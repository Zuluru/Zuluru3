<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use App\Model\Entity\Division;
use App\Model\Entity\Registration;
use App\Model\Entity\Team;
use App\Policy\RedirectResult;
use App\View\Helper\ZuluruHtmlHelper;
use Authorization\Exception\ForbiddenException;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\Http\Exception\GoneException;
use Cake\Http\Exception\NotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\PasswordHasher\HasherTrait;
use App\Model\Entity\TeamsPerson;
use Cake\Utility\Text;
use App\Model\Table\TeamsTable;
use App\Model\Table\GamesTable;

/**
 * Teams Controller
 *
 * @property \App\Model\Table\TeamsTable $Teams
 */
class TeamsController extends AppController {

	use HasherTrait;

	public $paginate = [
		'order' => ['Teams.name' => 'ASC']
	];

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions(): array {
		$actions = ['index', 'letter', 'view', 'tooltip', 'schedule', 'ical',
			// Roster updates may come from emailed links; people might not be logged in
			'roster_accept', 'roster_decline',
		];
		if (Configure::read('feature.public')) {
			$actions[] = 'stats';
		}

		return $actions;
	}

	/**
	 * _noAuthenticationJsonActions method
	 *
	 * @return array of JSON actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationJsonActions() {
		return ['view', 'schedule'];
	}

	// TODO: Proper fix for black-holing of team management
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);
		if (isset($this->FormProtection)) {
			$this->FormProtection->setConfig('unlockedActions', ['edit', 'add_from_team']);
		}
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function index() {
		$affiliate = $this->getRequest()->getQuery('affiliate');
		$affiliates = $this->Authentication->applicableAffiliateIDs();

		$query = $this->Teams->find()
			->matching('Divisions.Leagues.Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->where([
				'Divisions.is_open' => true,
			]);
		try {
			$teams = $this->paginate($query);
		} catch (NotFoundException $ex) {
			return $this->redirect(['action' => 'index']);
		}

		$letters = $this->Teams->find()
			->enableHydration(false)
			// TODO: Use a query object here
			->select(['letter' => 'DISTINCT SUBSTR(Teams.name, 1, 1)'])
			->matching('Divisions.Leagues.Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->where([
				'Divisions.is_open' => true,
				'Affiliates.id IN' => $affiliates,
			])
			->order(['letter'])
			->all()
			->extract('letter')
			->toArray();

		$leagues = $this->Teams->Divisions->Leagues->find()
			->where([
				'Leagues.is_open' => true,
				'Leagues.affiliate_id IN' => $affiliates,
			])
			->count();

		$this->set(compact('affiliates', 'affiliate', 'teams', 'letters', 'leagues'));
	}

	public function letter() {
		$letter = strtoupper($this->getRequest()->getQuery('letter'));
		if (!$letter) {
			$this->Flash->info(__('Invalid letter.'));
			return $this->redirect(['action' => 'index']);
		}

		$affiliate = $this->getRequest()->getQuery('affiliate');
		$affiliates = $this->Authentication->applicableAffiliateIDs();

		$teams = $this->Teams->find()
			->matching('Divisions.Leagues.Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->where([
				'Teams.name LIKE' => "$letter%",
				'Divisions.is_open' => true,
			])
			->order(['Affiliates.name', 'Teams.name', 'Divisions.open'])
			->toArray();

		$letters = $this->Teams->find()
			->enableHydration(false)
			->select(['letter' => 'DISTINCT SUBSTR(Teams.name, 1, 1)'])
			->matching('Divisions.Leagues.Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->where([
				'Divisions.is_open' => true,
				'Affiliates.id IN' => $affiliates,
			])
			->order(['letter'])
			->all()
			->extract('letter')
			->toArray();

		$this->set(compact('affiliates', 'affiliate', 'teams', 'letters', 'letter'));
	}

	public function join() {
		$this->Authorization->authorize($this);

		$affiliate = $this->getRequest()->getQuery('affiliate');
		$affiliates = $this->Authentication->applicableAffiliateIDs();

		$query = $this->Teams->find('openRoster', compact('affiliates'));
		$teams = $this->paginate($query);
		if (empty($teams)) {
			$this->Flash->info(__('There are no teams available to join.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('affiliates', 'affiliate', 'teams'));
	}

	public function unassigned() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$query = $this->Teams->find()
			->where([
				'Teams.affiliate_id IN' => $affiliates,
				'Teams.division_id IS' => null,
			]);
		$teams = $this->paginate($query);
		if (empty($teams)) {
			$this->Flash->info(__('There are no unassigned teams.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('teams'));
	}

	public function statistics() {
		$this->Authorization->authorize($this);
		// We need the names here, so that "top 10" lists are sorted by affiliate name
		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('affiliates'));

		// Division conditions take precedence over year conditions
		$division = $this->getRequest()->getQuery('division');
		$year = $this->getRequest()->getQuery('year');
		if ($division !== null) {
			$conditions = ['Divisions.id' => $division];
		} else if ($year === null) {
			$conditions = ['Divisions.is_open' => true];
		} else {
			$conditions = ['YEAR(Divisions.open)' => $year];
		}
		$conditions['Leagues.affiliate_id IN'] = array_keys($affiliates);

		$query = $this->Teams->Divisions->find();
		$divisions = $query
			->select(['team_count' => $query->func()->count('Teams.id')])
			->leftJoinWith('Teams')
			->contain([
				'Leagues' => ['Affiliates'],
				'Days',
			])
			->where($conditions)
			->group(['Divisions.id'])
			->enableAutoFields(true)
			->all()
			->indexBy('id')
			->toArray();

		if (!empty($divisions)) {
			uasort($divisions, ['App\Model\Table\LeaguesTable', 'compareLeagueAndDivision']);
			$limit = min(10, ceil(collection($divisions)->extract('team_count')->sumOf() / 4));

			// Get the list of teams that are short on players
			$query = $this->Teams->find();
			$shorts = $query
				->select([
					'Teams.id',
					'Teams.name',
					'Teams.division_id',
					'count' => $query->func()->count('TeamsPeople.person_id')
				])
				->leftJoinWith('People')
				->where([
					'Teams.division_id IN' => array_keys($divisions),
					'TeamsPeople.role IN' => Configure::read('playing_roster_roles'),
					'TeamsPeople.status' => ROSTER_APPROVED,
				])
				->group('Teams.id')
				// TODO: Can we get the sport- and ratio-rule-specific roster minimums in here? Big ugly SQL case statement?
				->having(['count <' => 14])
				->toArray();

			foreach ($shorts as $short) {
				$short->sub_count = $this->Teams->TeamsPeople->find()
					->where([
						'TeamsPeople.team_id' => $short->id,
						'TeamsPeople.role NOT IN' => Configure::read('regular_roster_roles'),
						'TeamsPeople.status' => ROSTER_APPROVED,
					])
					->count();

				// Add division info for sorting
				$short->division = $divisions[$short->division_id];
			}
			usort($shorts, [$this, 'compareAffiliateAndCount']);

			$top_rating = $lowest_rating = $top_spirit = $lowest_spirit = [];
			foreach (array_keys($affiliates) as $affiliate) {
				$affiliate_divisions = collection($divisions)->filter(function ($division) use ($affiliate) {
					return $division->league->affiliate_id == $affiliate;
				})->extract('id')->toArray();

				if (!empty($affiliate_divisions)) {
					// Get the list of top-rated teams
					$top_rating = array_merge($top_rating, $this->Teams->find()
						->where(['division_id IN' => $affiliate_divisions])
						->order(['rating' => 'DESC'])
						->limit($limit)
						->toArray());

					// Get the list of lowest-rated teams
					$query = $this->Teams->find()
						->where(['division_id IN' => $affiliate_divisions]);
					if (!empty($top_rating)) {
						$query->andWhere(['Teams.id NOT IN' => collection($top_rating)->extract('id')->toList()]);
					}
					$lowest_rating = array_merge($lowest_rating, $query
						->order(['rating' => 'ASC'])
						->limit($limit)
						->toArray());

					if (Configure::read('feature.spirit')) {
						// Find the list of unplayed games
						$unplayed = array_keys($this->Teams->Divisions->Games->find('list', [
							'conditions' => [
								'division_id IN' => $affiliate_divisions,
								'status IN' => Configure::read('unplayed_status'),
							],
						])->toArray());

						// Get the list of top spirited teams
						$query = $this->Teams->find()
							->select([
								'Teams.id', 'Teams.name', 'Teams.division_id',
								// TODO: Use an expression object for this query.
								// TODO: Figure out a way to do this such that it works if numeric spirit *was* enabled but then turned off.
								'avgspirit' => 'ROUND( AVG( COALESCE(
								SpiritEntries.entered_sotg,
								SpiritEntries.score_entry_penalty + SpiritEntries.q1 + SpiritEntries.q2 + SpiritEntries.q3 + SpiritEntries.q4 + SpiritEntries.q5 + SpiritEntries.q6 + SpiritEntries.q7 + SpiritEntries.q8 + SpiritEntries.q9 + SpiritEntries.q10 )
							), 2)',
							])
							->leftJoinWith('SpiritEntries')
							->where(['division_id IN' => $affiliate_divisions]);
						if (!empty($unplayed)) {
							$query->andWhere(['NOT' => ['game_id IN' => $unplayed]]);
						}
						$top_spirit = array_merge($top_spirit, $query
							->group('Teams.id')
							->having(['avgspirit IS NOT' => null])
							->order(['avgspirit' => 'DESC', 'Teams.name'])
							->limit($limit)
							->toArray());

						// Get the list of lowest spirited teams
						$query = $this->Teams->find()
							->select([
								'Teams.id', 'Teams.name', 'Teams.division_id',
								// TODO: Use an expression object for this query.
								// TODO: Figure out a way to do this such that it works if numeric spirit *was* enabled but then turned off.
								'avgspirit' => 'ROUND( AVG( COALESCE(
							SpiritEntries.entered_sotg,
							SpiritEntries.score_entry_penalty + SpiritEntries.q1 + SpiritEntries.q2 + SpiritEntries.q3 + SpiritEntries.q4 + SpiritEntries.q5 + SpiritEntries.q6 + SpiritEntries.q7 + SpiritEntries.q8 + SpiritEntries.q9 + SpiritEntries.q10 )
						), 2)',
							])
							->leftJoinWith('SpiritEntries')
							->where(['division_id IN' => $affiliate_divisions]);
						if (!empty($unplayed)) {
							$query->andWhere(['NOT' => ['game_id IN' => $unplayed]]);
						}
						if (!empty($top_spirit)) {
							$query->andWhere(['Teams.id NOT IN' => collection($top_spirit)->extract('id')->toList()]);
						}
						$lowest_spirit = array_merge($lowest_spirit, $query
							->group('Teams.id')
							->having(['avgspirit IS NOT' => null])
							->order(['avgspirit' => 'ASC', 'Teams.name'])
							->limit($limit)
							->toArray());
					}
				}
			}

			$query = $this->Teams->Divisions->Games->find();
			$team_id = $query->newExpr()->addCase(
				[$query->newExpr()->eq('Games.status', 'home_default')],
				[new IdentifierExpression('HomeTeam.id'), new IdentifierExpression('AwayTeam.id')]
			);
			$team_name = $query->newExpr()->addCase(
				[$query->newExpr()->eq('Games.status', 'home_default')],
				[new IdentifierExpression('HomeTeam.name'), new IdentifierExpression('AwayTeam.name')]
			);
			$defaulting = $query
				->select([
					'Games.division_id',
					'id' => $team_id,
					'name' => $team_name,
					'count' => 'COUNT(Games.id)',
				])
				->leftJoinWith('HomeTeam')
				->leftJoinWith('AwayTeam')
				->where([
					'Games.division_id IN' => array_keys($divisions),
					'Games.status IN' => ['home_default', 'away_default'],
				])
				->group('id')
				->toArray();

			// Add division info for sorting
			foreach ($defaulting as $game) {
				$game->division = $divisions[$game->division_id];
			}
			usort($defaulting, [$this, 'compareAffiliateAndCount']);

			// Get the list of non-score-submitting teams
			$query = $this->Teams->Divisions->Games->find();
			$team_id = $query->newExpr()->addCase(
				[$query->newExpr()->eq('Games.approved_by_id', APPROVAL_AUTOMATIC_AWAY)],
				[new IdentifierExpression('HomeTeam.id'), new IdentifierExpression('AwayTeam.id')]
			);
			$team_name = $query->newExpr()->addCase(
				[$query->newExpr()->eq('Games.approved_by_id', APPROVAL_AUTOMATIC_AWAY)],
				[new IdentifierExpression('HomeTeam.name'), new IdentifierExpression('AwayTeam.name')]
			);
			$no_scores = $query
				->select([
					'Games.division_id',
					'id' => $team_id,
					'name' => $team_name,
					'count' => 'COUNT(Games.id)',
				])
				->leftJoinWith('HomeTeam')
				->leftJoinWith('AwayTeam')
				->where([
					'Games.division_id IN' => array_keys($divisions),
					'Games.approved_by_id IN' => [APPROVAL_AUTOMATIC_HOME, APPROVAL_AUTOMATIC_AWAY],
				])
				->group('id')
				->toArray();

			// Add division info for sorting
			foreach ($no_scores as $game) {
				$game->division = $divisions[$game->division_id];
			}
			usort($no_scores, [$this, 'compareAffiliateAndCount']);

			$this->set(compact('shorts', 'top_rating', 'lowest_rating',
				'defaulting', 'no_scores', 'top_spirit', 'lowest_spirit'));
		}

		$years = $this->Teams->Divisions->find()
			->enableHydration(false)
			->select(['year' => 'DISTINCT YEAR(open)'])
			->where(['YEAR(open) !=' => 0])
			->order(['year'])
			->all()
			->extract('year')
			->toArray();

		$this->set(compact('year', 'years', 'divisions'));
	}

	public static function compareAffiliateAndCount($a, $b) {
		if ($a->division->league->affiliate->name > $b->division->league->affiliate->name) {
			return 1;
		} else if ($a->division->league->affiliate->name < $b->division->league->affiliate->name) {
			return -1;
		}

		if ($a->count > $b->count) {
			return 1;
		} else if ($a->count < $b->count) {
			return -1;
		}

		if ($a->has('name')) {
			return $a->name <=> $b->name;
		}

		return 0;
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('team');

		if ($this->getRequest()->is('csv')) {
			$contain = [
				'People' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['TeamsPeople.status' => ROSTER_APPROVED]);
					},
					Configure::read('Security.authModel'),
					'UserGroups',
					'Related' => [Configure::read('Security.authModel')],
				],
				'Divisions' => ['Leagues'],
			];
		} else {
			$contain = [
				'Divisions' => ['Days', 'Leagues'],
				'Franchises',
				'Regions',
				'Facilities',
				'Fields' => ['Facilities'],
			];
		}

		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => $contain
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		// Check if we can edit the roster. Whether it succeeds or fails is of no importance.
		// We just need to display the message that might come with a failure.
		try {
			$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division]), 'roster_add');
		} catch (ForbiddenException $ex) {
			$result = $ex->getResult();
			if ($result instanceof RedirectResult && $result->getReason()) {
				$this->set(['warning_message' => $result->getReason()]);
			}
		} catch (\Exception $ex) {
		}

		if ($this->getRequest()->is('csv')) {
			$this->Authorization->authorize($team, 'download');
			$this->setResponse($this->getResponse()->withDownload("{$team->name}.csv"));
			$include_gender = $this->Authorization->can(new ContextResource($team, ['division' => $team->division]), 'display_gender');
			\App\lib\context_usort($team->people, [TeamsTable::class, 'compareRoster'], ['include_gender' => $include_gender]);
			$this->set(compact('team'));
			return;
		}

		$identity = $this->Authentication->getIdentity();

		if ($identity && $identity->isLoggedIn() && Configure::read('feature.annotations')) {
			$visibility = [VISIBILITY_PUBLIC];

			if ($identity->isManagerOf($team)) {
				$visibility[] = VISIBILITY_ADMIN;
				$visibility[] = VISIBILITY_COORDINATOR;
			} else if ($identity->isCoordinatorOf($team)) {
				$visibility[] = VISIBILITY_COORDINATOR;
			}
			if ($identity->isCaptainOf($team)) {
				$visibility[] = VISIBILITY_CAPTAINS;
			}
			if ($identity->isPlayerOn($team)) {
				$visibility[] = VISIBILITY_TEAM;
			}

			$this->Teams->loadInto($team, ['Notes' => [
				'queryBuilder' => function (Query $q) use ($visibility) {
					return $q->where([
						'OR' => [
							'Notes.created_person_id' => $this->UserCache->currentId(),
							'Notes.visibility IN' => $visibility,
						],
					]);
				},
				'CreatedPerson',
			]]);
		}

		if ($this->Authorization->can($team, 'view_roster')) {
			$people_contain = ['Skills'];
		} else {
			$people_contain = [];
		}

		if (Configure::read('feature.badges')) {
			/** @var \App\Module\Badge $badge_obj */
			$badge_obj = $this->moduleRegistry->load('Badge');
			$badge_obj->visibility($identity, BADGE_VISIBILITY_HIGH);
			if (!empty($badge_obj->visibility)) {
				$people_contain['Badges'] = [
					'queryBuilder' => function (Query $q) use ($badge_obj) {
						return $q->where([
							'BadgesPeople.approved' => true,
							'Badges.visibility IN' => $badge_obj->visibility,
						]);
					},
				];
			}
		}

		$this->Teams->loadInto($team, ['TeamsPeople' => ['People' => $people_contain]]);

		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		if (isset($badge_obj)) {
			foreach ($team->teams_people as $roster_entry) {
				$badge_obj->prepForDisplay($roster_entry->person);
			}
		}

		if ($team->division_id) {
			$team_days = collection($team->division->days)->extract('id')->toArray();
			if (Configure::read('feature.registration') && $team->division->flag_membership) {
				$member_rule = "compare(member_type('{$team->division->open}') != 'none')";
			}
		} else {
			$team_days = [];
		}

		if ($this->Authorization->can($team, 'view_roster')) {
			if (isset($member_rule)) {
				$rule_obj = $this->moduleRegistry->load('RuleEngine');
				if (!$rule_obj->init($member_rule)) {
					$this->Flash->error(__('Failed to parse the rule: {0}', $member_rule));
					return $this->redirect(['action' => 'index']);
				}

				if ($team->division->league->affiliate_id) {
					$affiliate_id = $team->division->league->affiliate_id;
				} else {
					$affiliate_id = $team->affiliate_id;
				}
			}

			foreach ($team->teams_people as $roster_entry) {
				$person = $roster_entry->person;
				// Get everything from the user record that the rule might need
				try {
					$full_person = $this->Teams->People->get($person->id, [
						'contain' => [
							'Registrations' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['Registrations.payment' => 'paid']);
								},
								'Events' => [
									'EventTypes',
								],
							],
							'Teams' => [
								'queryBuilder' => function (Query $q) use ($id) {
									return $q->where(['Teams.id !=' => $id]);
								},
								'Divisions' => ['Days', 'Leagues'],
							],
							'Waivers',
							'Uploads',
							'UserGroups',
						]
					]);

					if ($roster_entry->status == ROSTER_APPROVED) {
						$person->can_add = true;
					} else {
						$person->can_add = $this->_canAdd($full_person, $team, $roster_entry->role, $roster_entry->status, true, true);
					}

					// Check if the player is a member, so we can highlight any that aren't
					if (isset($rule_obj)) {
						$person->is_a_member = $rule_obj->evaluate($affiliate_id, $full_person, $team);
					} else {
						// Ensure there's no warnings
						$person->is_a_member = true;
					}

					// Check for any roster conflicts
					$person->roster_conflict = $person->schedule_conflict = false;
					$playing_roster_roles = Configure::read('playing_roster_roles');
					foreach ($full_person->teams as $other_team) {
						if (in_array($roster_entry->role, $playing_roster_roles)) {
							// If this player is on a roster of another team in the same league...
							if ($other_team->division_id && $team->division_id &&
								$team->division->league_id == $other_team->division->league_id &&
								// and they're a regular player...
								in_array($other_team->_joinData->role, $playing_roster_roles))
							{
								$connected = false;
								if ($team->division->has('season_divisions') &&
									in_array($other_team->division_id, $team->division->season_divisions)
								)
								{
									$connected = true;
								}
								if ($team->division->has('playoff_divisions') &&
									in_array($other_team->division_id, $team->division->playoff_divisions)
								)
								{
									$connected = true;
								}

								// and that division doesn't have a regular season/playoff connection with this one...
								if (!$connected) {
									// ... then there's a roster conflict!
									$person->roster_conflict = true;
								}
							}
						}

						// If this player is on a roster of a team in another league...
						if ($other_team->division_id &&
							!empty($team_days) && $team->division->league_id != $other_team->division->league_id)
						{
							// that has a schedule which at least partially overlaps with this division...
							if (($other_team->division->open <= $team->division->open && $other_team->division->close >= $team->division->open) ||
								($team->division->open <= $other_team->division->open && $team->division->close >= $other_team->division->open))
							{
								// and they play on the same day of the week...
								// ... then there's a possible schedule conflict!
								$person->schedule_conflict = collection($other_team->division->days)->some(function ($day) use ($team_days) {
									return in_array($day->id, $team_days);
								});
							}
						}
					}
				} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
					// Shouldn't ever happen, but the stuff above will fail badly if it ever does.
				}
			}

			$include_gender = $this->Authorization->can(new ContextResource($team, ['division' => $team->division]), 'display_gender');
			\App\lib\context_usort($team->teams_people, [TeamsTable::class, 'compareRoster'], ['include_gender' => $include_gender]);
		}

		if ($team->division_id && $team->division->is_playoff) {
			$affiliate = $team->_getAffiliatedTeam($team->division, ['Divisions' => ['Leagues']]);
			if ($affiliate) {
				// Should maybe rename "affiliate" here, as it's the affiliated team, not the Zuluru Affiliate concept
				$team->affiliate = $affiliate;
			}
		}

		$this->set('team', $team);
		$this->viewBuilder()->setOption('serialize', ['team']);
	}

	public function numbers() {
		$person_id = $this->getRequest()->getQuery('person');
		if ($person_id) {
			$people_query = ['queryBuilder' => function (Query $q) use ($person_id) {
				return $q->where(compact('person_id'));
			}];
		} else {
			$people_query = [];
		}

		$id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					'Divisions' => ['Leagues'],
					'People' => $people_query,
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($person_id) {
			if (empty($team->people)) {
				$this->Flash->info(__('That player is not on this team.'));
				return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
			}
			$person = current($team->people);
			$roster = $person->_joinData;
		} else {
			$person = $roster = null;
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division, 'roster' => $roster]));

		usort($team->people, [TeamsTable::class, 'compareRoster']);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$data = $this->getRequest()->getData();

			if ($person_id) {
				// Manually add the person id into the data. Awkward to have it present in Ajax calls,
				// so we don't bother anywhere and instead just insert it here.
				$data['people'][0]['id'] = $person_id;
			}

			/** @var Team $team */
			$team = $this->Teams->patchEntity($team, $data, [
				'associated' => ['People._joinData']
			]);

			// Check for new join data entities in what's to be saved. They could be the
			// result of a forged form, or simply submitting stale data for someone that's
			// been removed from the roster since the form was loaded.
			try {
				foreach ($team->people as $key => $player) {
					if ($player->isNew() || $player->_joinData->isNew()) {
						unset($team->people[$key]);
						throw new CakeException(__('You cannot set shirt numbers for someone not on this team.'));
					}
				}

				if ($this->Teams->save($team)) {
					if ($person_id) {
						$this->Flash->success(__('The number has been saved.'));
					} else {
						$this->Flash->success(__('The numbers have been saved.'));
					}
					if (!$this->getRequest()->is('ajax')) {
						return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
					}
				} else {
					$this->Flash->warning(__('The {0} could not be saved. Please correct the errors below and try again.', __n('number', 'numbers', ($person_id ? 1 : 2))));
				}
			} catch (CakeException $ex) {
				$this->Flash->info($ex->getMessage());
			}
		}

		$this->set(compact('team', 'person'));
	}

	public function stats() {
		$id = intval($this->getRequest()->getQuery('team'));
		$contain = [
			'Divisions' => [
				'Leagues' => [
					'StatTypes' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['StatTypes.type IN' => Configure::read('stat_types.team')]);
						},
					],
				],
				'Days',
			],
			'People' => [
				'queryBuilder' => function (Query $q) {
					return $q->where([
						'TeamsPeople.role IN' => Configure::read('extended_playing_roster_roles'),
						'TeamsPeople.status' => ROSTER_APPROVED,
					]);
				},
			],
		];
		if (Configure::read('feature.annotations') && !$this->getRequest()->is('csv')) {
			$contain['Notes'] = [
				'queryBuilder' => function (Query $q) {
					return $q->where(['created_person_id' => $this->UserCache->currentId()]);
				},
			];
		}

		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, compact('contain'));
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize(new ContextResource($team, ['league' => $team->division_id ? $team->division->league : null]));
		$this->Configuration->loadAffiliate($team->division->league->affiliate_id);

		$sport_obj = $this->moduleRegistry->load("Sport:{$team->division->league->sport}");

		// Hopefully, everything we need is already cached
		$stats = Cache::remember("team_{$id}_stats", function () use ($team, $sport_obj) {
			// Calculate some stats. We need to get stats from any team in this
			// division, so that it properly handles subs and people who move teams.
			$teams = $this->Teams->find()
				->where(['division_id' => $team->division_id])
				->all()
				->combine('id', 'name')
				->toArray();
			if (empty($teams) || empty($team->people)) {
				return ['stats' => [], 'calculated_stats' => []];
			}

			$team->stats = $this->Teams->Stats->find()
				->where([
					'person_id IN' => collection($team->people)->extract('id')->toArray(),
					'team_id IN' => array_keys($teams),
				])
				->toArray();

			return [
				'stats' => $team->stats,
				'calculated_stats' => $sport_obj->calculateStats($team->stats, $team->division->league->stat_types),
			];
		}, 'long_term');

		$team->stats = $stats['stats'];
		$team->calculated_stats = $stats['calculated_stats'];

		usort($team->people, [TeamsTable::class, 'compareRoster']);

		$this->set(compact('team', 'sport_obj'));

		if ($this->getRequest()->is('csv')) {
			$this->setResponse($this->getResponse()->withDownload("Stats - {$team->name}.csv"));
		}
	}

	public function stat_sheet() {
		$id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					'Divisions' => [
						'Leagues' => [
							'StatTypes' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['StatTypes.type' => 'entered']);
								},
							],
						],
					],
					'People',
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize(new ContextResource($team, ['league' => $team->division->league, 'stat_types' => $team->division->league->stat_types]));

		$this->set(compact('team'));
	}

	public function tooltip() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('team');

		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					// Get the list of captains
					'People' => [
						'queryBuilder' => function (Query $q) {
							return $q
								->select(['id', 'first_name', 'last_name'])
								->where([
									'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
									'TeamsPeople.status' => ROSTER_APPROVED,
								]);
						},
					],
					'Divisions' => ['Leagues'],
				],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Authorization->can($team, 'note')) {
			$this->Teams->loadInto($team, ['Notes' => [
				'queryBuilder' => function (Query $q) {
					return $q->where(['created_person_id' => $this->UserCache->currentId()]);
				},
			]]);
		}

		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}
		$this->set(compact('team'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$this->Authorization->authorize($this);
		/** @var Team $team */
		$team = $this->Teams->newEmptyEntity();

		if ($this->getRequest()->is('post')) {
			$data = $this->getRequest()->getData();

			if (!$this->Authentication->getIdentity()->isManager()) {
				$data['people'] = [[
					'id' => $this->UserCache->currentId(),
					'_joinData' => [
						'role' => 'captain',
						'status' => ROSTER_APPROVED,
					],
				]];
			}

			// Save the facility preference order
			if (!empty($data['facilities']['_ids'])) {
				foreach ($data['facilities']['_ids'] as $key => $facility_id) {
					$data['facilities'][$key] = [
						'id' => $facility_id,
						'_joinData' => [
							'ranking' => $key + 1,
						],
					];
				}
				unset($data['facilities']['_ids']);
			}

			$team = $this->Teams->patchEntity($team, $data, [
				'associated' => ['People', 'Facilities']
			]);

			if ($this->Teams->save($team)) {
				if ($this->Authentication->getIdentity()->isManagerOf($team)) {
					$this->Flash->success(__('The team has been saved.'));
				} else {
					$this->Flash->success(__('The team has been saved, but will not be visible until approved.'));
				}
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The team could not be saved. Please correct the errors below and try again.'));
			}

			$this->Configuration->loadAffiliate($data['affiliate_id']);
		}

		// TODO: A way to indicate which sport the team is for, and load only applicable facilities
		$affiliates = $this->Authentication->applicableAffiliates();
		$regions = TableRegistry::getTableLocator()->get('Regions')->find('list', [
			'conditions' => ['affiliate_id IN' => array_keys($affiliates)],
		])->toArray();

		$facilities = [];
		if (!empty($regions)) {
			$facilities = TableRegistry::getTableLocator()->get('Facilities')->find()
				->contain([
					'Fields' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Fields.is_open' => true])->order(['Fields.num']);
						},
					],
				])
				->where([
					'Facilities.region_id IN' => array_keys($regions),
					'Facilities.is_open' => true,
				])
				->order(['Facilities.name'])
				->toArray();

			// Eliminate any facilities that have no matching fields
			foreach ($facilities as $key => $facility) {
				if (empty($facility->fields)) {
					unset($facilities[$key]);
				}
			}
		}

		$this->set(compact('team', 'affiliates', 'regions', 'facilities'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					'Facilities',
					'Divisions' => [
						'Leagues',
						'GameSlots' => ['Fields' => ['Facilities']],
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($team);
		$this->Configuration->loadAffiliate($this->Teams->affiliate($id));

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$data = $this->getRequest()->getData();

			// Save the facility preference order
			if (!empty($data['facilities']['_ids'])) {
				foreach ($data['facilities']['_ids'] as $key => $facility_id) {
					$data['facilities'][$key] = [
						'id' => $facility_id,
						'_joinData' => [
							'ranking' => $key + 1,
						],
					];
				}
				unset($data['facilities']['_ids']);
			}

			$team = $this->Teams->patchEntity($team, $data, [
				'associated' => ['Facilities']
			]);

			if ($this->Teams->save($team)) {
				$this->Flash->success(__('The team has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The team could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->Configuration->loadAffiliate($this->Teams->affiliate($id));

		$field_conditions = ['Fields.is_open' => true];
		$affiliates = $this->Authentication->applicableAffiliates();
		$region_conditions = ['affiliate_id IN' => array_keys($affiliates)];

		if ($team->division_id) {
			$available_fields = array_unique(collection($team->division->game_slots)->extract('field_id')->toArray());
			if (!empty($available_fields)) {
				$field_conditions['Fields.id IN'] = $available_fields;
			} else {
				$field_conditions['Fields.indoor'] = Configure::read("season_is_indoor.{$team->division->league->season}");
			}

			$available_regions = array_unique(collection($team->division->game_slots)->extract('field.facility.region_id')->toArray());
			if (!empty($available_regions)) {
				$region_conditions['Regions.id IN'] = $available_regions;
			}
		}

		$regions = TableRegistry::getTableLocator()->get('Regions')->find('list', [
			'conditions' => $region_conditions,
		])->toArray();

		$facilities = [];
		if (!empty($regions)) {
			$facilities = TableRegistry::getTableLocator()->get('Facilities')->find()
				->contain([
					'Fields' => [
						'queryBuilder' => function (Query $q) use ($field_conditions) {
							return $q->where($field_conditions)->order(['Fields.num']);
						},
					],
				])
				->where([
					'Facilities.region_id IN' => array_keys($regions),
					'Facilities.is_open' => true,
				])
				->order(['Facilities.name'])
				->toArray();

			// Eliminate any facilities that have no matching fields
			foreach ($facilities as $key => $facility) {
				if (empty($facility->fields)) {
					unset($facilities[$key]);
				}
			}
		}

		$this->set(compact('team', 'affiliates', 'regions', 'facilities'));
	}

	public function note() {
		$note_id = $this->getRequest()->getQuery('note');

		if ($note_id) {
			try {
				$note = $this->Teams->Notes->get($note_id, [
					'contain' => ['Teams' => ['Divisions' => ['Leagues']]],
				]);

				$this->Authorization->authorize($note, 'edit_team');
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid note.'));
				return $this->redirect('/');
			}
			$team = $note->team;
		} else {
			try {
				/** @var Team $team */
				$team = $this->Teams->get($this->getRequest()->getQuery('team'), [
					'contain' => ['Divisions' => ['Leagues']]
				]);
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid team.'));
				return $this->redirect('/');
			}
			$note = $this->Teams->Notes->newEmptyEntity();
			$note->team_id = $team->id;
		}

		$this->Authorization->authorize($team);
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$note = $this->Teams->Notes->patchEntity($note, $this->getRequest()->getData());

			if (empty($note->note)) {
				if ($note->isNew()) {
					$this->Flash->warning(__('You entered no text, so no note was added.'));
					return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
				} else {
					if ($this->Teams->Notes->delete($note)) {
						$this->Flash->success(__('The note has been deleted.'));
						return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
					} else if ($note->getError('delete')) {
						$this->Flash->warning(current($note->getError('delete')));
					} else {
						$this->Flash->warning(__('The note could not be deleted. Please, try again.'));
					}
				}
			} else if ($this->Teams->Notes->save($note)) {
				$this->Flash->success(__('The note has been saved.'));
				return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
			} else {
				$this->Flash->warning(__('The note could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('team', 'note'));
	}

	public function delete_note() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$note_id = $this->getRequest()->getQuery('note');

		try {
			$note = $this->Teams->Notes->get($note_id,
				['contain' => ['Teams' => ['Divisions']]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid note.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($note, 'delete_team');

		if ($this->Teams->Notes->delete($note)) {
			$this->Flash->success(__('The note has been deleted.'));
		} else if ($note->getError('delete')) {
			$this->Flash->warning(current($note->getError('delete')));
		} else {
			$this->Flash->warning(__('The note could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'view', '?' => ['team' => $note->team_id]]);
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions']
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($team);

		$dependencies = $this->Teams->dependencies($id, ['Facilities']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this team, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Teams->delete($team)) {
			if ($team->division_id) {
				$this->Teams->Divisions->clearCache($team->division, ['standings']);
			}
			$this->Flash->success(__('The team has been deleted.'));
		} else if ($team->getError('delete')) {
			$this->Flash->warning(current($team->getError('delete')));
		} else {
			$this->Flash->warning(__('The team could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	// TODO: Method for moving multiple teams at once; jQuery "left and right" boxes?
	public function move() {
		$id = $this->getRequest()->getQuery('team');
		$loose = $this->getRequest()->getQuery('loose');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions' => ['Leagues', 'People']]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($team);
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}
		$this->set(compact('team'));

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			try {
				/** @var Division $division */
				$division = $this->Teams->Divisions->get($this->getRequest()->getData('to'), [
					'contain' => ['Leagues']
				]);
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid division.'));
				return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
			}
			// Don't do division comparisons when the team being moved is not in a division
			if ($team->division_id) {
				if ($team->division->league->sport !== $division->league->sport) {
					$this->Flash->info(__('Cannot move a team to a different sport.'));
					return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
				}
				if (!$loose) {
					if ($team->division->league_id !== $division->league_id) {
						$this->Flash->info(__('Cannot move a team to a different league.'));
						return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
					}
					if ($team->division->ratio_rule !== $division->ratio_rule) {
						$this->Flash->info(__('Destination division must have the same ratio rule.'));
						return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
					}
				}
			}
			$team->division_id = $this->getRequest()->getData('to');
			if ($this->Teams->save($team)) {
				$this->Flash->success(__('Team has been moved to {0}.', $division->full_league_name));
			} else {
				$this->Flash->warning(__('Failed to move the team!'));
			}
			return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
		}

		$conditions = [
			'OR' => [
				'Divisions.is_open' => true,
				'Divisions.open >' => FrozenDate::now(),
			],
			'Leagues.affiliate_id IN' => $this->Authentication->applicableAffiliateIDs(true),
		];
		if ($team->division_id) {
			$conditions += [
				'Divisions.id !=' => $team->division_id,
				'Leagues.sport' => $team->division->league->sport,
				'Divisions.ratio_rule' => $team->division->ratio_rule,
			];
			if (!$loose) {
				$conditions['Divisions.league_id'] = $team->division->league_id;
			}
		}
		$divisions = $this->Teams->Divisions->find()
			->contain(['Leagues'])
			->where($conditions)
			->toArray();

		$this->set(compact('team', 'divisions', 'loose'));
	}

	public function schedule() {
		$id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					'People',
					'Divisions' => ['Leagues']
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$team->games = TableRegistry::getTableLocator()->get('Games')
			->find('schedule', ['teams' => [$id]])
			->find('withAttendance', ['teams' => [$id], 'status' => [ATTENDANCE_ATTENDING]])
			->contain([
				'ScoreEntries',
				'SpiritEntries',
			])
			->where(['OR' => [
				'Games.home_dependency_type !=' => 'copy',
				'Games.home_dependency_type IS' => null,
			]])
			->order(['GameSlots.game_date', 'GameSlots.game_start'])
			->toArray();

		// Find any non-game team events
		if (in_array($team->id, $this->UserCache->read('AllTeamIDs'))) {
			$team->games = array_merge($team->games, $this->Teams->TeamEvents->readAttendance($team, null, true));
		}

		$team->games = collection($team->games)->filter(function ($game) {
			return $this->Authorization->can($game, 'view');
		})->toList();

		if (empty($team->games)) {
			$this->Flash->info(__('This team has no games scheduled yet.'));
			return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
		}

		// Sort games by date, time and field
		usort($team->games, [GamesTable::class, 'compareDateAndField']);

		// Imported databases may have teams that are not properly associated with divisions,
		// but nevertheless have games which are.
		// TODOLATER: Add a migration to deal with this, if possible
		if (empty($team->division_id)) {
			$team->division = $this->Teams->Divisions->get($team->games[0]->division_id, ['contain' => ['Leagues']]);
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		}

		$this->set(compact('team'));
		$this->set('spirit_obj', $team->division->league->hasSpirit() ? $this->moduleRegistry->load("Spirit:{$team->division->league->sotg_questions}") : null);
		$this->viewBuilder()->setOption('serialize', ['team']);
	}

	/**
	 * iCal method
	 *
	 * This function takes the parameter the old-fashioned way, to try to be more third-party friendly
	 *
	 * @param string|null $id Team id.
	 * @return void
	 * @throws \Cake\Http\Exception\GoneException When record not found.
	 */
	public function ical($id) {
		$id = intval($id);
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions' => ['Leagues']]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			throw new GoneException();
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division]));

		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		$games = TableRegistry::getTableLocator()->get('Games')
			->find('schedule', ['teams' => [$id]])
			->where([
				'Games.published' => true,
				'OR' => [
					'Games.home_dependency_type !=' => 'copy',
					'Games.home_dependency_type IS' => null,
				],
			])
			->order(['GameSlots.game_date', 'GameSlots.game_start'])
			->toArray();

		$events = $this->Teams->TeamEvents->find()
			->where([
				'TeamEvents.team_id' => $id,
			])
			->toArray();

		// Sort items by date, time and field
		usort($games, [GamesTable::class, 'compareDateAndField']);
		usort($events, [GamesTable::class, 'compareDateAndField']);
		// Outlook only accepts the first event in a file, so we put the last game first
		$games = array_reverse($games);

		$this->set('calendar_type', 'Team Schedule');
		$this->set('calendar_name', "{$team->name} schedule");
		$this->setResponse($this->getResponse()->withDownload("$id.ics"));
		$this->set('team_id', $id);
		$this->set('games', $games);
		$this->set('events', $events);
		$this->viewBuilder()->setLayoutPath('ics')->setClassName('Ical');
	}

	public function spirit() {
		$id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions' => ['Leagues']]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($team);
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}
		$team['games'] = $this->Teams->Divisions->Games->find()
			->contain([
				'GameSlots',
				'HomeTeam',
				'AwayTeam',
				'SpiritEntries',
			])
			->where([
				['OR' => [
					'Games.home_team_id' => $id,
					'Games.away_team_id' => $id,
				]],
				['OR' => [
					'Games.home_dependency_type !=' => 'copy',
					'Games.home_dependency_type IS' => null,
				]],
			])
			->toArray();
		if (empty($team['games'])) {
			$this->Flash->info(__('This team has no games scheduled yet.'));
			return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
		}

		// Sort games by date, time and field
		usort($team['games'], [GamesTable::class, 'compareDateAndField']);

		$this->set(compact('team'));
		$this->set('spirit_obj', $this->moduleRegistry->load("Spirit:{$team->division->league->sotg_questions}"));
	}

	public function attendance() {
		$id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					'Divisions' => ['Days', 'Leagues'],
					'People' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['TeamsPeople.status' => ROSTER_APPROVED]);
						}
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($team);

		// Find the list of holidays to avoid
		$holidays_table = TableRegistry::getTableLocator()->get('Holidays');
		$holidays = $holidays_table->find('list', [
			'keyField' => 'id',
			'valueField' => 'date',
		])->toArray();

		$games = $this->Teams->Divisions->Games->find()
			->contain([
				'GameSlots' => ['Fields' => ['Facilities']],
				'HomeTeam',
				'AwayTeam',
			])
			->where([
				['OR' => [
					'Games.home_team_id' => $id,
					'Games.away_team_id' => $id,
				]],
				'Games.published' => true,
				['OR' => [
					'Games.home_dependency_type !=' => 'copy',
					'Games.home_dependency_type IS' => null,
				]],
			])
			->order(['GameSlots.game_date', 'GameSlots.game_start'])
			->toArray();
		$game_dates = collection($games)->extract('game_slot.game_date')->toArray();

		// Calculate the expected list of dates that games will be on. For divisions that play
		// on multiple days, this will include only the first day of each week.
		$dates = [];
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
			$days = [];

			$attendance = $this->Teams->get($team->id, [
				'contain' => [
					'People' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['TeamsPeople.status' => ROSTER_APPROVED]);
						},
						Configure::read('Security.authModel'),
					],
				]
			]);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);

			$days = array_unique(collection($team->division->days)->extract('id')->toArray());
			if (!empty($days) && $team->division->schedule_type !== 'none') {
				// Back the start date up to whatever this organization considers the first day of the week
				$start = $team->division->open;
				$first_weekday = Configure::read('organization.first_day');
				while ($start->format('N') != $first_weekday) {
					$start = $start->subDays(1);
				}

				// Now move it forward to whatever is the first day that this division plays on
				$first_playday = min($days);
				while ($start->format('N') != $first_playday) {
					$start = $start->addDays(1);
				}

				for ($week_start = $start; $week_start <= $team->division->close; $week_start = $week_start->addWeeks(1)) {
					$week_end = $week_start->addDays(6);
					$week_game_scheduled = $week_non_holiday = false;
					for ($date = $week_start; $date <= $week_end; $date = $date->addDays(1)) {
						// If the league doesn't play on this day, skip it
						if (!in_array($date->format('N'), $days)) {
							continue;
						}

						// If there is a game scheduled, show it
						if (in_array($date, $game_dates)) {
							$dates[] = $date;
							$week_game_scheduled = true;
							continue;
						}

						// If it's not a holiday, this week is a week that might have a game in it
						if (!in_array($date, $holidays)) {
							$week_non_holiday = true;
						}
					}

					// If we don't already have a game scheduled this week, but it's not all holidays, include the starting date
					if (!$week_game_scheduled && $week_non_holiday) {
						$dates[] = $week_start;
					}
				}
			}

			$attendance = $this->Teams->Divisions->Games->readAttendance($team, $days, null, $dates);
		}

		$event_attendance = $this->Teams->TeamEvents->readAttendance($team);

		$this->set(compact('team', 'attendance', 'event_attendance', 'dates', 'days', 'games'));
	}

	public function emails() {
		$id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					'People' => [
						'queryBuilder' => function (Query $q) {
							return $q
								->where([
									'People.id !=' => $this->UserCache->currentId(),
									'TeamsPeople.status' => ROSTER_APPROVED,
								])
								->order([
									'People.' . Configure::read('gender.column') => Configure::read('gender.order'), 'People.last_name', 'People.first_name',
								]);
						},
						Configure::read('Security.authModel'),
					],
					'Divisions' => ['Leagues'],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($team);
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		$this->set(compact('team'));
	}

	public function add_player() {
		$id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					'Divisions' => ['Leagues'],
				],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division]));
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		$this->set(compact('team'));

		$this->_handlePersonSearch(['team'], ['group_id IN' => [GROUP_PLAYER, GROUP_COACH]]);

		// Only show teams from divisions that have some schedule type
		$teams = array_reverse($this->UserCache->read('AllTeams'));
		foreach ($teams as $key => $past_team) {
			if ($past_team->division_id == $team->division_id || empty($past_team->division_id) || $past_team->division->schedule_type == 'none') {
				unset($teams[$key]);
			}
		}
		$this->set(compact('teams'));

		// Admins and coordinators get to add people based on registration events
		if ($this->Authorization->can(new ContextResource($team, ['division' => $team->division]), 'add_from_event')) {
			$conditions = [
				'Events.open < NOW()',
				'Events.close >' => FrozenDate::now()->subDays(30),
			];
			if (!empty($team->division_id)) {
				$divisions = $this->Teams->Divisions->Leagues->divisions($team->division->league_id);
				if (!empty($divisions)) {
					// The first element of this array is an array, because those conditions need
					// to be ANDed together. This is NOT suitable for array_merge.
					$conditions = ['OR' => [$conditions, 'Events.division_id IN' => $divisions]];
				}
			}

			$events = TableRegistry::getTableLocator()->get('Events')->find()
				->where($conditions)
				->order(['Events.event_type_id', 'Events.open', 'Events.close', 'Events.id'])
				->toArray();
			$this->set(compact('events'));
		}
	}

	public function add_from_team() {
		$this->getRequest()->allowMethod(['post']);

		$id = $this->getRequest()->getQuery('team');

		// Read the current team roster
		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					'People',
					// We need league information for sending out invites, may as well read it now
					'Divisions' => [
						'Days',
						'Leagues',
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division]));
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		// Read the old team roster
		try {
			$old_team = $this->Teams->get($this->getRequest()->getData('team'), [
				'contain' => [
					'Divisions' => ['Leagues'],
					'People' => [
						'queryBuilder' => function (Query $q) use ($team) {
							if (!empty($team->people)) {
								// Only include people that aren't yet on the new roster
								$q->where(['NOT' => ['People.id IN' => collection($team->people)->extract('id')->toArray()]]);
							}
							return $q->order(['People.' . Configure::read('gender.column') => Configure::read('gender.order'), 'People.last_name', 'People.first_name']);
						},
						Configure::read('Security.authModel'),
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		// If this is a form submission, set the role for each player
		if (array_key_exists('player', $this->getRequest()->getData())) {
			$result = [];
			foreach ($this->getRequest()->getData('player') as $player => $data) {
				if (!empty($data['role']) && $data['role'] != 'none') {
					$person = collection($old_team->people)->firstMatch(['id' => $player]);
					if ($person) {
						$person->unset('_joinData');
						// TODO: If the team has numbers, take care of that here too
						$result[$this->_setRosterRole($person, $team, ROSTER_INVITED, $data['role'], $data['position'])][] = $person;
					}
				}
			}
			$msg = [];
			if (empty($result)) {
				$msg[] = __('You did not select anyone to add to the team!');
				$class = 'info';
			} else {
				if (!empty($result[ROSTER_APPROVED])) {
					$msg[] = __n('{0} has been added to the roster.', '{0} have been added to the roster.',
						count($result[ROSTER_APPROVED]),
						Text::toList(collection($result[ROSTER_APPROVED])->extract('full_name')->toArray())
					);
					$class = 'success';

					$event = new CakeEvent('Model.Team.rosterUpdate', $this, [$team->id, $result[ROSTER_APPROVED]]);
					$this->getEventManager()->dispatch($event);
				}
				if (!empty($result[ROSTER_INVITED])) {
					$msg[] = __n('Invitation has been sent to {0}.', 'Invitations have been sent to {0}.',
						count($result[ROSTER_INVITED]),
						Text::toList(collection($result[ROSTER_INVITED])->extract('full_name')->toArray())
					);
					$class = 'success';
				}
				if (!empty($result[false])) {
					$msg[] = __n('Failed to send invitation to {0}.', 'Failed to send invitations to {0}.',
						count($result[false]),
						Text::toList(collection($result[false])->extract('full_name')->toArray())
					);
					$class = 'warning';
				}
			}
			$this->Flash->{$class}(implode(' ', $msg));
			return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
		}

		foreach ($old_team->people as $person) {
			$person->can_add = $this->_canAdd($person, $team, 'player', null, false, true);
			// By passing false here for the current role, "none" won't be eliminated as an option
			$person->roster_role_options = $this->_rosterRoleOptions(false, $team, $person->id);
		}

		$this->set(compact('team', 'old_team'));
	}

	public function add_from_event() {
		$this->getRequest()->allowMethod(['post']);

		$id = $this->getRequest()->getQuery('team');

		try {
			/** @var Team $team */
			$team = $this->Teams->get($id, [
				'contain' => [
					'People',
					// We need league information for sending out invites, may as well read it now
					'Divisions' => [
						'Days',
						'Leagues',
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division]));
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		// Find all divisions in the same league
		// TODO: Add a checkbox to bypass this check
		if (!empty($team->division_id)) {
			$current = $this->Teams->Divisions->find()
				->contain(['Teams' => ['People']])
				->where([
					'league_id' => $team->division->league_id,
				])
				->all()
				->extract('teams.{*}.people.{*}.id')
				->toList();
		} else {
			$current = collection($team->people)->extract('id')->toArray();
		}

		// Read the event
		try {
			$this->Events = $this->fetchTable('Events');
			$event = $this->Events->get($this->getRequest()->getData('event'), [
				'contain' => [
					'Registrations' => [
						'queryBuilder' => function (Query $q) use ($current) {
							$q->where([
								'Registrations.payment IN' => Configure::read('registration_paid'),
							]);

							if (!empty($current)) {
								// Only include people that aren't yet on the new roster
								// or the roster of another team in the same league
								$q->andWhere(['NOT' => ['Registrations.person_id IN' => $current]]);
							}

							return $q;
						},
						'People' => [
							Configure::read('Security.authModel'),
						],
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		}

		$event->registrations = collection($event->registrations)->sortBy(function (Registration $registration) {
			return $registration->person->last_name . '-' . $registration->person->first_name;
		}, SORT_ASC, SORT_STRING | SORT_FLAG_CASE);

		// If this is a form submission, set the role for each player
		if (array_key_exists('player', $this->getRequest()->getData())) {
			$result = [];
			foreach ($this->getRequest()->getData('player') as $player => $data) {
				if (!empty($data['role']) && $data['role'] != 'none') {
					$registration = collection($event->registrations)->firstMatch(['person_id' => $player]);
					if ($registration) {
						$registration->person->unset('_joinData');
						// TODO: If the team has numbers, take care of that here too
						$result[$this->_setRosterRole($registration->person, $team, ROSTER_APPROVED, $data['role'], $data['position'])][] = $registration->person;
					}
				}
			}
			$msg = [];
			if (empty($result)) {
				$msg[] = __('You did not select anyone to add to the team!');
				$class = 'info';
			} else {
				if (!empty($result[ROSTER_APPROVED])) {
					$msg[] = __n('{0} has been added to the roster.', '{0} have been added to the roster.',
						count($result[ROSTER_APPROVED]),
						Text::toList(collection($result[ROSTER_APPROVED])->extract('full_name')->toArray())
					);
					$class = 'success';

					$event = new CakeEvent('Model.Team.rosterUpdate', $this, [$team->id, $result[ROSTER_APPROVED]]);
					$this->getEventManager()->dispatch($event);
				}
				if (!empty($result[ROSTER_INVITED])) {
					$msg[] = __n('Invitation has been sent to {0}.', 'Invitations have been sent to {0}.',
						count($result[ROSTER_INVITED]),
						Text::toList(collection($result[ROSTER_INVITED])->extract('full_name')->toArray())
					);
					$class = 'success';
				}
				if (!empty($result[false])) {
					$msg[] = __n('Failed to send invitation to {0}.', 'Failed to send invitations to {0}.',
						count($result[false]),
						Text::toList(collection($result[false])->extract('full_name')->toArray())
					);
					$class = 'warning';
				}
			}
			$this->Flash->{$class}(implode(' ', $msg));
			return $this->redirect(['action' => 'view', '?' => ['team' => $id]]);
		}

		foreach ($event->registrations as $registration) {
			$registration->can_add = $this->_canAdd($registration->person, $team, 'player', null, false, true);
			// By passing false here for the current role, "none" won't be eliminated as an option
			$registration->roster_role_options = $this->_rosterRoleOptions(false, $team, $registration->person->id);
		}

		$this->set(compact('team', 'event'));
	}

	public function roster_role() {
		$person_id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();

		try {
			[$team, $person] = $this->_initTeamForRosterChange($person_id);
		} catch (CakeException $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		if (empty($person)) {
			$this->Flash->info(__('This person is not on this team.'));
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division, 'roster' => $person->_joinData]));

		$role = $person->_joinData->role;
		$roster_role_options = $this->_rosterRoleOptions($role, $team, $person_id);
		$this->set(compact('person', 'team', 'role', 'roster_role_options'));

		if ($person->_joinData->status != ROSTER_APPROVED) {
			$this->Flash->info(__('A player\'s role on a team cannot be changed until they have been approved on the roster.'));
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}

		// Check if this user is the only approved captain on the team
		$is_captain = in_array($team->id, $this->UserCache->read('OwnedTeamIDs'));
		if ($is_captain) {
			$required_roles = Configure::read('required_roster_roles');
			if (in_array($role, $required_roles) &&
				!in_array($this->getRequest()->getData('role'), $required_roles)
			) {
				$captains = collection($team->people)->filter(function ($person) use ($required_roles) {
					return in_array($person->_joinData->role, $required_roles) && $person->_joinData->status == ROSTER_APPROVED;
				})->toArray();
				if (count($captains) == 1) {
					$this->Flash->info(__('All teams must have at least one player as coach or captain.'));
					return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
				}
			}
		}

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if (!array_key_exists($this->getRequest()->getData('role'), $roster_role_options)) {
				$this->Flash->info(__('You do not have permission to set that role.'));
			} else {
				if ($this->_setRosterRole($person, $team, ROSTER_APPROVED, $this->getRequest()->getData('role'))) {
					$this->UserCache->_deleteTeamData($person_id);
					if ($this->getRequest()->is('ajax')) {
						return;
					}
				}
			}
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}
	}

	public function roster_position() {
		$person_id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();

		try {
			[$team, $person] = $this->_initTeamForRosterChange($person_id);
		} catch (CakeException $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		if (empty($person)) {
			$this->Flash->info(__('This person is not on this team.'));
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division, 'roster' => $person->_joinData]));

		$position = $person->_joinData->position;
		if ($team->division_id) {
			$sport = $team->division->league->sport;
		} else if (count(Configure::read('options.sport')) == 1) {
			$sport = current(Configure::read('options.sport'));
		} else {
			$this->Flash->info(__('A position cannot be assigned until this team is placed in a division.'));
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}
		$roster_position_options = Configure::read("sports.$sport.positions");
		$this->set(compact('person', 'team', 'position', 'roster_position_options'));

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if (!array_key_exists($this->getRequest()->getData('position'), $roster_position_options)) {
				$this->Flash->info(__('That is not a valid position.'));
			} else {
				$person->_joinData->position = $this->getRequest()->getData('position');
				if ($this->Teams->People->link($team, [$person], compact('person', 'team'))) {
					$this->UserCache->_deleteTeamData($person_id);
					if ($this->getRequest()->is('ajax')) {
						return;
					}
					$this->Flash->success(__('Changed the player\'s position.'));
				} else {
					$this->Flash->warning(__('Failed to change the player\'s position.'));
				}
			}
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}
	}

	public function roster_add() {
		$person_id = $this->getRequest()->getQuery('person');

		try {
			[$team, $person] = $this->_initTeamForRosterChange($person_id);
		} catch (CakeException $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division]));

		if (!empty($person)) {
			$this->Flash->info(__('This person is already on this team.'));
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}

		// Read the bare player record
		try {
			$person = $this->Teams->People->get($person_id, [
				'contain' => [Configure::read('Security.authModel')]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid player.'));
			return $this->redirect('/');
		}

		// If a role was submitted, try to set it. Whether it succeeds or fails,
		// we'll go back to the team view page, and the flash message will tell the
		// user why. It should only fail in the case of malicious form tinkering, so
		// we don't try hard to let them correct the error.
		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if (!empty($this->getRequest()->getData('role'))) {
				$this->_setRosterRole($person, $team, ROSTER_INVITED, $this->getRequest()->getData('role'), $this->getRequest()->getData('position'));
				return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
			}
			$this->Flash->info(__('You must select a role for this person.'));
		}

		// Check if this person can even be added
		$can_add = $this->_canAdd($person, $team, null, null, false);
		if ($can_add !== true) {
			// If not, we may still allow the invitation, but give the captain a warning
			$can_invite = $this->_canInvite($person, $team);
			if ($can_invite !== true) {
				$this->Flash->warning($can_invite);
				return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
			}
		}

		$roster_role_options = $this->_rosterRoleOptions('none', $team, $person_id);
		// TODO: In addition to checking the roster method, check if they were on an affiliate's roster.
		$adding = ($can_add === true && $team->division && $team->division->roster_method == 'add');

		$this->set(compact('person', 'team', 'roster_role_options', 'can_add', 'adding'));
	}

	public function roster_request() {
		try {
			[$team, $person] = $this->_initTeamForRosterChange($this->UserCache->currentId());
		} catch (CakeException $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($team, ['division' => $team->division]));

		if (!empty($person)) {
			$this->Flash->info(__('You are already on this team.'));
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}

		// Read the bare player record
		try {
			$person = $this->Teams->People->get($this->UserCache->currentId(), [
				'contain' => [Configure::read('Security.authModel')]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid player.'));
			return $this->redirect('/');
		}

		// Check if this person can even be added
		$can_add = $this->_canAdd($person, $team, null, null, true, false, false, true);
		if ($can_add !== true) {
			$this->Flash->html('{0}', ['params' => ['class' => 'warning', 'replacements' => [$can_add]]]);
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}

		$roster_role_options = $this->_rosterRoleOptions('none', $team, $this->UserCache->currentId(), false);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if (!array_key_exists($this->getRequest()->getData('role'), $roster_role_options)) {
				$this->Flash->info(__('You are not allowed to request that role.'));
			} else if ($this->_setRosterRole($person, $team, ROSTER_REQUESTED, $this->getRequest()->getData('role'),
				array_key_exists('position', $this->getRequest()->getData()) ? $this->getRequest()->getData('position') : 'unspecified'
			)) {
				$this->UserCache->_deleteTeamData();
			}
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}

		$this->set(compact('person', 'team', 'roster_role_options'));
	}

	public function roster_accept() {
		$person_id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();

		try {
			[$team, $person] = $this->_initTeamForRosterChange($person_id);
		} catch (CakeException $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($team, ['person' => $person, 'roster' => $person ? $person->_joinData : null, 'division' => $team->division, 'code' => $this->getRequest()->getQuery('code')]));

		// Check if this person can even be added
		$can_add = $this->_canAdd($person, $team, $person->_joinData->role, $person->_joinData->status, true, $this->getRequest()->is('ajax'), false, true);
		if ($can_add !== true) {
			$identity = $this->Authentication->getIdentity();
			if ($identity && $identity->isLoggedIn() && !empty($this->can_add_rule_obj) && !empty($this->can_add_rule_obj->redirect)) {
				if ($this->getRequest()->is('ajax')) {
					return $this->redirect(array_merge($this->can_add_rule_obj->redirect, ['return' => $this->_return()]), 100);
				} else {
					return $this->redirect(array_merge($this->can_add_rule_obj->redirect, ['return' => $this->_return()]));
				}
			}
			if ($this->getRequest()->is('ajax')) {
				$this->Flash->warning(ZuluruHtmlHelper::formatTextMessage(['format' => '{0}', 'replacements' => [$can_add]]));
			} else {
				$this->Flash->html('{0}', ['params' => ['class' => 'warning', 'replacements' => [$can_add]]]);
			}
			return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
		}

		$oldStatus = $person->_joinData->status;
		$person->_joinData->status = ROSTER_APPROVED;
		if ($this->Teams->People->link($team, [$person], compact('person', 'team'))) {
			// Send email to the affected people
			if (Configure::read('feature.generate_roster_email')) {
				$this->_sendAccept($person, $team, $person->_joinData->role, $oldStatus);
			}

			$event = new CakeEvent('Model.Team.rosterUpdate', $this, [$team->id, [$person]]);
			$this->getEventManager()->dispatch($event);

			$this->UserCache->_deleteTeamData($person_id);

			if ($this->getRequest()->is('ajax')) {
				$this->set(compact('person', 'team'));
				return;
			}

			$this->Flash->success(__('You have accepted this roster {0}.',
				($oldStatus == ROSTER_INVITED) ? __('invitation') : __('request')));
		} else {
			$this->Flash->warning(__('The database failed to save the acceptance of this roster {0}.',
				($oldStatus == ROSTER_INVITED) ? __('invitation') : __('request')));
		}
		return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
	}

	public function roster_decline() {
		$person_id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();

		try {
			[$team, $person] = $this->_initTeamForRosterChange($person_id);
		} catch (CakeException $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($team, ['person' => $person, 'roster' => $person ? $person->_joinData : null, 'division' => $team->division, 'code' => $this->getRequest()->getQuery('code')]));

		$this->Teams->People->unlink($team, [$person], compact('person', 'team'));

		// Send email to the affected people
		if (Configure::read('feature.generate_roster_email')) {
			$this->_sendDecline($person, $team, $person->_joinData->role, $person->_joinData->status);
		}

		$this->UserCache->_deleteTeamData($person_id);

		if ($this->getRequest()->is('ajax')) {
			return;
		}

		$this->Flash->success(__('You have declined this roster {0}.',
			($person->_joinData->status == ROSTER_INVITED) ? __('invitation') : __('request')));
		$identity = $this->Authentication->getIdentity();
		if ($identity && $identity->isMe($person)) {
			return $this->redirect('/');
		}
		return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
	}

	protected function _initTeamForRosterChange($person_id) {
		$team_id = $this->getRequest()->getQuery('team');
		try {
			/** @var Team $team */
			$team = $this->Teams->get($team_id, [
				'contain' => [
					'People' => [Configure::read('Security.authModel')],
					// We need league information for sending out invites, may as well read it now
					'Divisions' => [
						'Days',
						'Leagues',
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			throw new CakeException(__('Invalid team.'));
		}
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		// Pull out the player record from the team
		$person = collection($team->people)->firstMatch(['id' => $person_id]);

		return [$team, $person];
	}

	protected function _rosterRoleOptions($role, $team, $person_id, $allow_override = true) {
		// Some special handling for playoff teams
		if ($team->division_id && $team->division->is_playoff) {
			$roster_role_options = $this->_playoffRosterRoleOptions($role, $team, $person_id);
		} else {
			$roster_role_options = Configure::read('options.roster_role');
		}

		// People that aren't on the team can't be "changed to" not on the team
		if ($role == 'none' || $role === null) {
			unset($roster_role_options['none']);
		}

		// Check for some group membership
		$groups = $this->UserCache->read('UserGroupIDs', $person_id);
		if (!in_array(GROUP_PLAYER, $groups)) {
			foreach (Configure::read('extended_playing_roster_roles') as $playing_role) {
				unset($roster_role_options[$playing_role]);
			}
		}

		// Admins, coordinators and captains can make anyone anything that's left, but they don't get any
		// special treatment when joining a roster ($allow_override == false)
		if ($allow_override && $this->Authorization->can(new ContextResource($team, ['division' => $team->division]), 'roster_add')) {
			return $roster_role_options;
		}

		// Special handling of a couple of current roles
		switch ($role) {
			case 'substitute':
				// Subs can't make themselves regular players
				unset($roster_role_options['player']);
				break;

			case 'none':
				if (!$team->open_roster) {
					$this->Flash->info(__('Sorry, this team is not open for new players to join.'));
					return $this->redirect(['action' => 'view', '?' => ['team' => $team->id]]);
				}
				// The "none" role means they're not on the team, so either they are being added
				// by a captain or admin, or they are requesting to join and need to be confirmed
				// before they will have any permissions. Either way, captainly roles should be
				// an option.
				return $roster_role_options;
		}

		// Non-captains are not allowed to promote themselves to captainly roles
		foreach (Configure::read('privileged_roster_roles') as $captain_role) {
			unset($roster_role_options[$captain_role]);
		}

		// Whatever is left is okay
		return $roster_role_options;
	}

	protected function _playoffRosterRoleOptions ($role, $team, $person_id) {
		$roster_role_options = Configure::read('options.roster_role');

		$affiliate = $team->_getAffiliatedTeam($team->division, [
			'People' => [
				'queryBuilder' => function (Query $q) use ($person_id) {
					return $q->where(['People.id' => $person_id]);
				}
			]
		]);
		if ($affiliate) {
			// If the person wasn't on the affiliated team roster, then
			// they cannot take a "normal" role on the playoff roster.
			if (empty($affiliate->people)) {
				foreach (Configure::read('playing_roster_roles') as $role) {
					unset($roster_role_options[$role]);
				}
			}
		}
		return $roster_role_options;
	}

	protected function _setRosterRole($person, $team, $status, $role, $position = null) {
		// We can always remove people from rosters
		if ($role == 'none') {
			// TODOLATER: Check for unlink return value, if they change it so it returns success
			// https://github.com/cakephp/cakephp/issues/8196
			$this->Teams->People->unlink($team, [$person], compact('person', 'team'));

			// Delete any future attendance records
			$this->Teams->Attendances->deleteAll([
				'team_id' => $team->id,
				'person_id' => $person->id,
				'game_date >' => FrozenDate::now(),
			]);

			if (!$this->getRequest()->is('ajax')) {
				$this->Flash->success(__('Removed the player from the team.'));
			}
			if (Configure::read('feature.generate_roster_email')) {
				$this->_sendRemove($person, $team, $person->_joinData->getOriginal('role'));
			}

			$event = new CakeEvent('Model.Team.rosterRemove', $this, [$team->id, $person]);
			$this->getEventManager()->dispatch($event);

			$this->UserCache->_deleteTeamData($person->id);
			return true;
		}

		$can_add = $this->_canAdd($person, $team, $role, $status);
		$accept_warning = null;
		if ($can_add === true) {
			// Under certain circumstances, an invite is changed to an add
			// TODO: In addition to checking the roster method, check if they were on an affiliate's roster.
			if ($status === ROSTER_INVITED && $team->division && $team->division->roster_method == 'add') {
				$status = ROSTER_APPROVED;
			}
		} else {
			if ($status === ROSTER_INVITED) {
				// Redo the test, without being strict
				$can_add = $this->_canAdd($person, $team, $role, $status, false, false, true);

				if ($can_add !== true) {
					// Save the reason that they can't be added for the email
					$accept_warning = $can_add;
					$can_add = $this->_canInvite($person, $team, $role);
				}
			}
			if ($can_add !== true) {
				if ($this->getRequest()->is('ajax')) {
					$this->Flash->warning(ZuluruHtmlHelper::formatTextMessage(['format' => '{0}', 'replacements' => [$can_add]]));
				} else {
					$this->Flash->html('{0}', ['params' => ['class' => 'warning', 'replacements' => [$can_add]]]);
				}
				return false;
			}
		}

		if (empty($person->_joinData)) {
			$person->_joinData = new TeamsPerson([
				'role' => $role,
				'position' => $position ?: 'unspecified',
				'status' => $status,
			]);
		} else {
			$person->_joinData->role = $role;
			if ($position) {
				$person->_joinData->position = $position;
			}
		}

		// We will need to know this later, and it's no longer new after the link is done
		$isNew = $person->_joinData->isNew();
		$old_role = $person->_joinData->getOriginal('role');

		// If we are successful in the update, there may be emails to send
		if ($this->Teams->People->link($team, [$person], compact('person', 'team')) && empty($person->getErrors())) {
			$this->UserCache->_deleteTeamData($person->id);

			if ($status == ROSTER_APPROVED) {
				$event = new CakeEvent('Model.Team.rosterUpdate', $this, [$team->id, [$person]]);
				$this->getEventManager()->dispatch($event);
			}

			if (!Configure::read('feature.generate_roster_email')) {
				return $status;
			}

			// TODO: Move this stuff to an event triggered from TeamsPeopleTable::afterSave
			if ($isNew) {
				switch ($status) {
					case ROSTER_APPROVED:
						$this->_sendAdd($person, $team, $role);
						break;

					case ROSTER_INVITED;
						$this->_sendInvite($person, $team, $role, $accept_warning);
						break;

					case ROSTER_REQUESTED:
						$this->_sendRequest($person, $team, $role);
						break;
				}
			} else {
				$this->_sendChange($person, $team, $role, $old_role);
			}
			return $status;
		} else {
			$this->Flash->warning(__('Failed to set player to that role.'));
			return false;
		}
	}

	protected function _canAdd($person, $team, $role = null, $status = null, $strict = true, $text_reason = false, $absolute_url = false, $player_perspective = false) {
		if (!$person->complete) {
			if ($player_perspective) {
				return __('You must complete your profile before you can join a team. Log in and go to My Profile -> Edit to complete this step.');
			} else {
				return __('This person has not yet completed their profile. Please contact them directly to have them complete their profile.');
			}
		}
		if ($person->status == 'new') {
			return __('New players must be approved by an administrator before they can be added to a team; this normally happens within one business day.');
		}

		// Maybe use the rules engine to decide if this person can be added to this roster
		if (!empty($team->division->roster_rule)) {
			if (!isset($this->can_add_rule_obj)) {
				$this->can_add_rule_obj = $this->moduleRegistry->load('CanAddRule', ['className' => 'RuleEngine']);
				if (!$this->can_add_rule_obj->init($team->division->roster_rule)) {
					return __('Failed to parse the rule: {0}', $this->can_add_rule_obj->parse_error);
				}
			}

			if (!$person->has('registrations')) {
				$person->registrations = $this->UserCache->read('RegistrationsReserved', $person->id);
				$has_registrations = false;
			} else {
				$has_registrations = true;
			}
			if (!$person->has('teams')) {
				$person->teams = $this->UserCache->read('Teams', $person->id);
				$has_teams = false;
			} else {
				$has_teams = true;
			}
			if (!$person->has('waivers')) {
				$person->waivers = $this->UserCache->read('Waivers', $person->id);
				$has_waivers = false;
			} else {
				$has_waivers = true;
			}
			if (!$person->has('user_groups')) {
				$person->uset_groups = $this->UserCache->read('UserGroups', $person->id);
				$has_groups = false;
			} else {
				$has_groups = true;
			}
			if (!$person->has('uploads')) {
				$person->uploads = $this->UserCache->read('Documents', $person->id);
				$has_uploads = false;
			} else {
				$has_uploads = true;
			}

			$canAdd = $this->can_add_rule_obj->evaluate($team->division->league->affiliate_id, $person, $team, $strict, $text_reason, true, $absolute_url, [
				REASON_TYPE_PLAYER_ACTIVE => ($person->id == $this->UserCache->currentId() ? __('To be added to this team, you must first {0}.') : __('To be added to this team, this person must first {0}.')),
				REASON_TYPE_PLAYER_PASSIVE => ($person->id == $this->UserCache->currentId() ? __('You {0}.') : __('This person {0}.')),
				REASON_TYPE_TEAM => __('This team {0}.'),
			]);

			// Remove all the things we added. Otherwise, they can mess up later saves.
			if (!$has_registrations) {
				unset($person->registrations);
			}
			if (!$has_teams) {
				unset($person->teams);
			}
			if (!$has_waivers) {
				unset($person->waivers);
			}
			if (!$has_groups) {
				unset($person->user_groups);
			}
			if (!$has_uploads) {
				unset($person->uploads);
			}

			if (!$canAdd) {
				return $this->can_add_rule_obj->reason;
			}
		}

		if ($role !== null && $status != ROSTER_INVITED && $status != ROSTER_REQUESTED) {
			$roster_role_options = $this->_rosterRoleOptions(null, $team, $person->id);
			if (!array_key_exists($role, $roster_role_options)) {
				return __('You are not allowed to invite someone to that role.');
			}
		}

		return true;
	}

	// TODO: Placeholder function for limiting who can even be invited onto rosters,
	// for example denying non-members the ability to be invited onto rosters
	protected function _canInvite($person, $team, $role = null) {
		if ($role !== null) {
			$roster_role_options = $this->_rosterRoleOptions(null, $team, $person->id);
			if (!array_key_exists($role, $roster_role_options)) {
				return __('You are not allowed to invite someone to that role.');
			}
		}
		return true;
	}

	protected function _sendAdd($person, $team, $role) {
		if (!$this->_sendMail([
			'to' => $person,
			'replyTo' => $this->UserCache->read('Person'),
			'subject' => function() use ($team) { return __('You have been added to {0}', $team->name); },
			'template' => 'roster_add',
			'sendAs' => 'both',
			'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), [
				'reply' => $this->UserCache->read('Person.email'),
			]),
		]))
		{
			$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
			return false;
		}

		return true;
	}

	protected function _sendInvite($person, $team, $role, $accept_warning) {
		if (!$this->_sendMail([
			'to' => $person,
			'replyTo' => $this->UserCache->read('Person'),
			'subject' => function() use ($team) { return __('Invitation to join {0}', $team->name); },
			'template' => 'roster_invite',
			'sendAs' => 'both',
			'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), [
				'code' => $this->_makeHash([$person->_joinData->id, $person->_joinData->team_id, $person->_joinData->person_id, $person->_joinData->role, $person->_joinData->created]),
				'captain' => $this->UserCache->read('Person.full_name'),
				'accept_warning' => $accept_warning,
			]),
		]))
		{
			$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
			return false;
		}

		return true;
	}

	protected function _sendRequest($person, $team, $role) {
		[$captains, $captain_names] = $this->_initRosterCaptains($team);

		if (!$this->_sendMail([
			'to' => $captains,
			'replyTo' => $person,
			'subject' => function() use ($person, $team) { return __('{0} requested to join {1}', $person->full_name, $team->name); },
			'template' => 'roster_request',
			'sendAs' => 'both',
			'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), [
				'code' => $this->_makeHash([$person->_joinData->id, $person->_joinData->team_id, $person->_joinData->person_id, $person->_joinData->role, $person->_joinData->created]),
				'captains' => $captain_names,
			]),
		]))
		{
			$this->Flash->warning(__('Error sending email to team coaches/captains.'));
			return false;
		}

		return true;
	}

	protected function _sendAccept($person, $team, $role, $status) {
		if ($status == ROSTER_INVITED) {
			// A player has accepted an invitation
			[$captains, $captain_names] = $this->_initRosterCaptains($team);

			if (!$this->_sendMail([
				'to' => $captains,
				'replyTo' => $person,
				'subject' => function() use ($person, $team) { return __('Invitation for {0} to join {1} was accepted', $person->full_name, $team->name); },
				'template' => 'roster_accept_invite',
				'sendAs' => 'both',
				'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), [
					'captains' => $captain_names,
				]),
			]))
			{
				$this->Flash->warning(__('Error sending email to team coaches/captains.'));
				return false;
			}
		} else {
			// A captain has accepted a request
			$captain = $this->UserCache->read('Person.full_name');
			if (empty($captain)) {
				$captain = __('A coach or captain');
			}

			if (!$this->_sendMail([
				'to' => $person,
				'replyTo' => $this->UserCache->read('Person'),
				'subject' => function() use ($team) { return __('Request to join {0} was accepted', $team->name); },
				'template' => 'roster_accept_request',
				'sendAs' => 'both',
				'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), compact('captain')),
			]))
			{
				$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
				return false;
			}
		}
		return true;
	}

	protected function _sendDecline($person, $team, $role, $status) {
		if ($status == ROSTER_INVITED) {
			$is_player = ($this->getRequest()->getQuery('code') !== null || $person->id == $this->UserCache->currentId() || in_array($person->id, $this->UserCache->read('RelativeIDs')));
			$is_captain = in_array($team->id, $this->UserCache->read('OwnedTeamIDs'));

			if (!$is_captain) {
				// A player or admin has declined an invitation
				[$captains, $captain_names] = $this->_initRosterCaptains($team);

				if (!$this->_sendMail([
					'to' => $captains,
					'replyTo' => $person,
					'subject' => function() use ($person, $team) { return __('{0} declined your invitation to join {1}', $person->full_name, $team->name); },
					'template' => 'roster_decline_invite',
					'sendAs' => 'both',
					'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), [
						'captains' => $captain_names,
					]),
				]))
				{
					$this->Flash->warning(__('Error sending email to team coaches/captains.'));
					return false;
				}
			}
			if (!$is_player) {
				// A captain or admin has removed an invitation
				if (!$this->_sendMail([
					'to' => $person,
					'replyTo' => $this->UserCache->read('Person'),
					'subject' => function() use ($team) { return __('Invitation to join {0} was removed', $team->name); },
					'template' => 'roster_remove_invite',
					'sendAs' => 'both',
					'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), [
						'captain' => $this->UserCache->read('Person.full_name'),
					]),
				]))
				{
					$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
					return false;
				}
			}
		} else {
			// A captain has declined a request
			$captain = $this->UserCache->read('Person.full_name');
			if (empty($captain)) {
				$captain = 'A coach or captain';
			}

			if (!$this->_sendMail([
				'to' => $person,
				'replyTo' => $this->UserCache->read('Person'),
				'subject' => function() use ($team) { return __('Request to join {0} was declined', $team->name); },
				'template' => 'roster_decline_request',
				'sendAs' => 'both',
				'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), compact('captain')),
			]))
			{
				$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
				return false;
			}
		}
		return true;
	}

	protected function _sendChange($person, $team, $role, $old_role) {
		if ($role == $old_role) {
			return true;
		}

		if ($person->id == $this->UserCache->currentId() ||
			(in_array($person->id, $this->UserCache->read('RelativeIDs')) && !in_array($team->id, $this->UserCache->read('OwnedTeamIDs'))))
		{
			// A player has changed themselves, or a relative who is not a captain of the team did it for them
			[$captains, $captain_names] = $this->_initRosterCaptains($team);

			if (!$this->_sendMail([
				'to' => $captains,
				'replyTo' => $person,
				'subject' => function() use ($person, $team) { return __('{0} role change on {1} roster', $person->full_name, $team->name); },
				'template' => 'roster_change_by_player',
				'sendAs' => 'both',
				'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), [
					'captains' => $captain_names,
					'reply' => $this->UserCache->read('Person.email'),
					'old_role' => $old_role,
				]),
			]))
			{
				$this->Flash->warning(__('Error sending email to team coaches/captains.'));
				return false;
			}
		} else {
			if (!$this->_sendMail([
				'to' => $person,
				'replyTo' => $this->UserCache->read('Person'),
				'subject' => function() use ($team) { return __('Change of roster role on {0}', $team->name); },
				'template' => 'roster_change_by_captain',
				'sendAs' => 'both',
				'viewVars' => array_merge($this->_rosterEmailVars($person, $team, $role), [
					'captain' => $this->UserCache->read('Person.full_name'),
					'reply' => $this->UserCache->read('Person.email'),
					'old_role' => $old_role,
				]),
			]))
			{
				$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
				return false;
			}
		}

		return true;
	}

	protected function _sendRemove($person, $team, $old_role) {
		if ($person->id == $this->UserCache->currentId()) {
			// A player has removed themselves
			[$captains, $captain_names] = $this->_initRosterCaptains($team);

			if (!$this->_sendMail([
				'to' => $captains,
				'replyTo' => $person,
				'subject' => function() use ($person, $team) { return __('{0} removed from {1} roster', $person->full_name, $team->name); },
				'template' => 'roster_remove_by_player',
				'sendAs' => 'both',
				'viewVars' => array_merge($this->_rosterEmailVars($person, $team), [
					'captains' => $captain_names,
					'reply' => $this->UserCache->read('Person.email'),
					'old_role' => $old_role,
				]),
			]))
			{
				$this->Flash->warning(__('Error sending email to team coaches/captains.'));
				return false;
			}
		} else {
			if (!$this->_sendMail([
				'to' => $person,
				'replyTo' => $this->UserCache->read('Person'),
				'subject' => function() use ($team) { return __('Removal from {0} roster', $team->name); },
				'template' => 'roster_remove_by_captain',
				'sendAs' => 'both',
				'viewVars' => array_merge($this->_rosterEmailVars($person, $team), [
					'captain' => $this->UserCache->read('Person.full_name'),
					'reply' => $this->UserCache->read('Person.email'),
					'old_role' => $old_role,
				])
			]))
			{
				$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
				return false;
			}
		}

		return true;
	}

	protected function _rosterEmailVars($person, $team, $role = null) {
		$vars = [
			'person' => $person,
			'team' => $team,
			'division' => $team->division,
			'role' => $role,
		];

		if (!empty($team->division->league)) {
			$vars['league'] = $team->division->league;
			$vars['sport'] = $team->division->league->sport;
		} else {
			$vars['sport'] = current(array_keys(Configure::read('options.sport')));
		}

		return $vars;
	}

	protected function _initRosterCaptains($team) {
		// Find the list of captains and assistants for the team, not including current person
		$team_captains = $this->Teams->get($team->id, [
			'contain' => [
				'People' => [
					'queryBuilder' => function (Query $q) {
						$q = $q->where([
							'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
							'TeamsPeople.status' => ROSTER_APPROVED,
						]);
						$my_id = $this->UserCache->currentId();
						if ($my_id) {
							$q = $q->where(['TeamsPeople.person_id !=' => $my_id]);
						}
						return $q;
					},
					Configure::read('Security.authModel'),
				],
			]
		]);

		return [$team_captains->people, implode(', ', collection($team_captains->people)->extract('first_name')->toArray())];
	}

}
