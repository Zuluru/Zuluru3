<?php
namespace App\Controller;

use App\Model\Entity\Registration;
use App\View\Helper\ZuluruHtmlHelper;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\Network\Exception\GoneException;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Auth\HasherTrait;
use App\Model\Entity\TeamsPerson;
use Cake\Utility\Text;

/**
 * Teams Controller
 *
 * @property \App\Model\Table\TeamsTable $Teams
 */
class TeamsController extends AppController {

	use HasherTrait;

	public $paginate = [
		'order' => [
			'Teams.name' => 'asc',
		]
	];

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _publicActions() {
		if (Configure::read('Perm.is_manager')) {
			// If a team id is specified, check if we're a manager of that team's affiliate
			$team = $this->request->query('team');
			if ($team) {
				if (!in_array($this->Teams->affiliate($team), $this->UserCache->read('ManagedAffiliateIDs'))) {
					Configure::write('Perm.is_manager', false);
				}
			}
		}

		$actions = ['index', 'add', 'letter', 'view', 'tooltip', 'schedule', 'ical',
			// Roster updates may come from emailed links; people might not be logged in
			'roster_accept', 'roster_decline',
		];
		if (Configure::read('feature.public')) {
			$actions[] = 'stats';
		}
		return $actions;
	}

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->params['action'], [
					'statistics',
					'unassigned',
				]))
				{
					// If an affiliate id is specified, check if we're a manager of that affiliate
					$affiliate_id = $this->request->query('affiliate');
					if (!$affiliate_id) {
						// If there's no affiliate id, this is a top-level operation that all managers can perform
						return true;
					} else if (in_array($affiliate_id, $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					} else {
						Configure::write('Perm.is_manager', false);
					}
				}

				if (in_array($this->request->params['action'], [
					'edit',
					'delete',
					'roster_request',
					'emails',
					'stat_sheet',
					'attendance',
					'spirit',
					'move',
				]))
				{
					// If a team id is specified, check if we're a manager of that team's affiliate
					$team_id = $this->request->query('team');
					if ($team_id) {
						if (in_array($this->Teams->affiliate($team_id), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}

			// People can perform these operations on divisions they coordinate
			if (in_array($this->request->params['action'], [
				'spirit',
				'stat_sheet',
			]))
			{
				// If a team id is specified, check if we're a coordinator of that team's division
				$team_id = $this->request->query('team');
				if ($team_id) {
					$divisions = $this->UserCache->read('Divisions');
					return collection($divisions)->extract('teams.{*}')->some(function ($team) use ($team_id) { return $team->id == $team_id; });
				}
			}

			// Anyone that's logged in can perform these operations
			if (in_array($this->request->params['action'], [
				'join',
				'note',
				'delete_note',
				'stats',
			]))
			{
				return true;
			}

			// We have a special function just for checking these operations
			if (in_array($this->request->params['action'], [
				'add_player',
				'add_from_team',
				'add_from_event',
				'roster_add',
				'roster_role',
				'roster_position',
				'numbers',
			]))
			{
				// If a team id is specified, check the permissions
				$team_id = $this->request->query('team');
				if ($team_id && $this->_canEditRoster($team_id, $this->request->params['action'] != 'add_from_event') === true) {
					return true;
				}
			}

			// People can perform these operations on teams they run
			if (in_array($this->request->params['action'], [
				'edit',
				'delete',
				'emails',
				'stat_sheet',
			]))
			{
				// If a team id is specified, check if we're a captain of that team
				$team_id = $this->request->query('team');
				if ($team_id && in_array($team_id, $this->UserCache->read('OwnedTeamIDs'))) {
					return true;
				}
			}

			// People can perform these operations on their own account
			if (in_array($this->request->params['action'], [
				'roster_role',
				'roster_position',
				'roster_request',
				'numbers',
			]))
			{
				// If a player id is specified, check if it's the logged-in user, or a relative
				// If no player id is specified, it's always the logged-in user
				$person_id = $this->request->query('person');
				$relatives = $this->UserCache->read('RelativeIDs');
				if (!$person_id || $person_id == $this->UserCache->currentId() || in_array($person_id, $relatives)) {
					return true;
				}
			}

			// People can perform these operations on teams they or their relatives are on
			if (in_array($this->request->params['action'], [
				'attendance',
			]))
			{
				$team_id = $this->request->query('team');
				if ($team_id) {
					if (in_array($team_id, $this->UserCache->read('AllTeamIDs')) || in_array($team_id, $this->UserCache->read('AllRelativeTeamIDs'))) {
						return true;
					}
				}
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	// TODO: Proper fix for black-holing of team management
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		$this->Security->config('unlockedActions', ['edit', 'add_from_team']);
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$affiliate = $this->request->query('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();

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
			->hydrate(false)
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
			->toArray();

		$leagues = $this->Teams->Divisions->Leagues->find()
			->where([
				'Leagues.is_open' => true,
				'Leagues.affiliate_id IN' => $affiliates,
			])
			->count();

		$this->set(compact('affiliates', 'affiliate', 'teams', 'letters', 'leagues'));
		$this->set('_serialize', true);
	}

	public function letter() {
		$letter = strtoupper($this->request->query('letter'));
		if (!$letter) {
			$this->Flash->info(__('Invalid letter.'));
			return $this->redirect(['action' => 'index']);
		}

		$affiliate = $this->request->query('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();

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
			->hydrate(false)
			->select(['letter' => 'DISTINCT SUBSTR(Teams.name, 1, 1)'])
			->matching('Divisions.Leagues.Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->where([
				'Divisions.is_open' => true,
				'Affiliates.id IN' => $affiliates,
			])
			->order(['letter'])
			->toArray();

		$this->set(compact('affiliates', 'affiliate', 'teams', 'letters', 'letter'));
	}

	public function join() {
		$affiliate = $this->request->query('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();

		$query = $this->Teams->find('openRoster', compact('affiliates'));
		$teams = $this->paginate($query);
		if (empty($teams)) {
			$this->Flash->info(__('There are no teams available to join.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('affiliates', 'affiliate', 'teams'));
	}

	public function unassigned() {
		$affiliates = $this->_applicableAffiliateIDs(true);

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
		// We need the names here, so that "top 10" lists are sorted by affiliate name
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('affiliates'));

		// Division conditions take precedence over year conditions
		$division = $this->request->query('division');
		$year = $this->request->query('year');
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
			->autoFields(true)
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
						// TODO: Use the configuration settings for non-player roles
						'TeamsPeople.role' => 'substitute',
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
		}

		$years = $this->Teams->Divisions->find()
			->select(['year' => 'DISTINCT YEAR(open)'])
			->where(['YEAR(open) !=' => 0])
			->order(['year'])
			->toArray();

		$this->set(compact('shorts', 'top_rating', 'lowest_rating',
			'defaulting', 'no_scores', 'top_spirit', 'lowest_spirit',
			'year', 'years', 'divisions'));
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
			if ($a->name > $b->name) {
				return 1;
			} else if ($a->name < $b->name) {
				return -1;
			}
		} else {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}

		return 0;
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->query('team');

		if ($this->request->is('csv')) {
			$contain = [
				'People' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['TeamsPeople.status' => ROSTER_APPROVED]);
					},
					Configure::read('Security.authModel'),
					'Groups',
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
			if (Configure::read('Perm.is_logged_in') || Configure::read('feature.public')) {
				$contain['People'] = ['Skills'];
				if (Configure::read('feature.annotations')) {
					$visibility = [VISIBILITY_PUBLIC];
					if (Configure::read('Perm.is_admin')) {
						$visibility[] = VISIBILITY_ADMIN;
						$visibility[] = VISIBILITY_COORDINATOR;
					} else {
						$divisions = $this->UserCache->read('Divisions');
						$teams = collection($divisions)->extract('teams.{*}.id')->toArray();
						if (in_array($id, $teams)) {
							$visibility[] = VISIBILITY_COORDINATOR;
						}
					}
					if (in_array($id, $this->UserCache->read('OwnedTeamIDs'))) {
						$visibility[] = VISIBILITY_CAPTAINS;
					}
					if (in_array($id, $this->UserCache->read('TeamIDs'))) {
						$visibility[] = VISIBILITY_TEAM;
					}
					$contain['Notes'] = [
						'queryBuilder' => function (Query $q) use ($visibility) {
							return $q->where([
								'OR' => [
									'Notes.created_person_id' => $this->UserCache->currentId(),
									'Notes.visibility IN' => $visibility,
								],
							]);
						},
						'CreatedPerson',
					];
				}

				if (Configure::read('feature.badges')) {
					$badge_obj = $this->moduleRegistry->load('Badge');
					$contain['People']['Badges'] = [
						'queryBuilder' => function (Query $q) use ($badge_obj) {
							return $q->where([
								'BadgesPeople.approved' => true,
								'Badges.visibility IN' => $badge_obj->visibility(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'), BADGE_VISIBILITY_HIGH),
							]);
						},
					];
				}
			}
		}

		try {
			$team = $this->Teams->get($id, [
				'contain' => $contain
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$is_captain = in_array($id, $this->UserCache->read('OwnedTeamIDs'));
		$is_coordinator = in_array($team->division_id, $this->UserCache->read('DivisionIDs'));
		$can_edit_roster = $this->_canEditRoster($team);

		$this->set(compact('is_captain', 'is_coordinator', 'can_edit_roster'));

		if ($this->request->is('csv')) {
			if (!(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_captain || $is_coordinator)) {
				$this->Flash->info(__('You do not have access to download this team roster.'));
				return $this->redirect(['action' => 'view', 'team' => $id]);
			}
			$this->response->download("{$team->name}.csv");
			\App\lib\context_usort($team->people, ['App\Model\Table\TeamsTable', 'compareRoster'], ['team' => $team]);
			$this->set(compact('team'));
			return;
		}

		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		if (isset($badge_obj)) {
			foreach (array_keys($team->people) as $key) {
				$badge_obj->prepForDisplay($team->people[$key]);
			}
		}

		if ($team->division_id) {
			$team_days = collection($team->division->days)->extract('id')->toArray();
			if (Configure::read('feature.registration')) {
				$member_rule = "compare(member_type('{$team->division->open}') != 'none')";
			}
		} else {
			$team_days = [];
		}

		if (Configure::read('Perm.is_logged_in') || Configure::read('feature.public')) {
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

			foreach ($team->people as $person) {
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
							'Groups',
						]
					]);

					if ($person->_joinData->status == ROSTER_APPROVED) {
						$person->can_add = true;
					} else {
						$person->can_add = $this->_canAdd($full_person, $team, $person->_joinData->role, $person->_joinData->status, true, true);
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
						if (in_array($person->_joinData->role, $playing_roster_roles)) {
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
				} catch (RecordNotFoundException $ex) {
					// Shouldn't ever happen, but the stuff above will fail badly if it ever does.
				} catch (InvalidPrimaryKeyException $ex) {
					// Shouldn't ever happen, but the stuff above will fail badly if it ever does.
				}
			}

			\App\lib\context_usort($team->people, ['App\Model\Table\TeamsTable', 'compareRoster'], ['team' => $team]);
		}

		if ($team->division_id && $team->division->is_playoff) {
			$affiliate = $team->_getAffiliatedTeam($team->division, ['Divisions' => ['Leagues']]);
			if ($affiliate) {
				// Should maybe rename "affiliate" here, as it's the affiliated team, not the Zuluru Affiliate concept
				$team->affiliate = $affiliate;
			}
		}

		$this->set('team', $team);

		$this->set('_serialize', true);
	}

	public function numbers() {
		if (!Configure::read('feature.shirt_numbers')) {
			$this->Flash->info(__('Shirt numbers are not enabled on this system.'));
			return $this->redirect(['action' => 'index']);
		}

		$person_id = $this->request->query('person');
		if ($person_id) {
			$people_query = ['queryBuilder' => function (Query $q) use ($person_id) {
				return $q->where(compact('person_id'));
			}];
		} else {
			$people_query = [];
		}

		$id = $this->request->query('team');
		try {
			$team = $this->Teams->get($id, [
				'contain' => [
					'Divisions' => ['Leagues'],
					'People' => $people_query,
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}
		$is_captain = in_array($id, $this->UserCache->read('OwnedTeamIDs'));

		if ($person_id) {
			if (empty($team->people)) {
				$this->Flash->info(__('That player is not on this team.'));
				return $this->redirect(['action' => 'view', 'team' => $id]);
			}
			$person = current($team->people);
		} else if ($this->_canEditRoster($team) !== true) {
			$this->Flash->info(__('You do not have permission to set shirt numbers for this team.'));
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}

		usort($team->people, ['App\Model\Table\TeamsTable', 'compareRoster']);

		if ($this->request->is(['patch', 'post', 'put'])) {
			if ($person_id) {
				// Manually add the person id into the data. Awkward to have it present in Ajax calls,
				// so we don't bother anywhere and instead just insert it here.
				$this->request->data['people'][0]['id'] = $person_id;
			}

			$team = $this->Teams->patchEntity($team, $this->request->data, [
				'associated' => ['People._joinData']
			]);

			// Check for new join data entities in what's to be saved. They could be the
			// result of a forged form, or simply submitting stale data for someone that's
			// been removed from the roster since the form was loaded.
			try {
				foreach ($team->people as $key => $player) {
					if ($player->isNew() || $player->_joinData->isNew()) {
						unset($team->people[$key]);
						throw new Exception(__('You cannot set shirt numbers for someone not on this team.'));
					}
				}

				if ($this->Teams->save($team)) {
					if ($person_id) {
						$this->Flash->success(__('The number has been saved.'));
					} else {
						$this->Flash->success(__('The numbers have been saved.'));
					}
					if (!$this->request->is('ajax')) {
						return $this->redirect(['action' => 'view', 'team' => $id]);
					}
				} else {
					$this->Flash->warning(__('The {0} could not be saved. Please correct the errors below and try again.', __n('number', 'numbers', ($person_id ? 1 : 2))));
				}
			} catch (Exception $ex) {
				$this->Flash->info($ex->getMessage());
			}
		}

		$this->set(compact('team', 'is_captain', 'person_id', 'person'));
		$this->set('_serialize', true);
	}

	public function stats() {
		$id = intval($this->request->query('team'));
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
		if (Configure::read('feature.annotations') && !$this->request->is('csv')) {
			$contain['Notes'] = [
				'queryBuilder' => function (Query $q) {
					return $q->where(['created_person_id' => $this->UserCache->currentId()]);
				},
			];
		}

		try {
			$team = $this->Teams->get($id, compact('contain'));
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		if (empty($team->division_id)) {
			// TODO: Any situation where it makes sense to have stat tracking for a team not in a division?
			$this->Flash->info(__('This team does not have stat tracking enabled.'));
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}
		if (!$team->division->league->hasStats()) {
			$this->Flash->info(__('This league does not have stat tracking enabled.'));
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}

		$this->Configuration->loadAffiliate($team->division->league->affiliate_id);

		$sport_obj = $this->moduleRegistry->load("Sport:{$team->division->league->sport}");

		// Hopefully, everything we need is already cached
		$stats = Cache::remember("team/{$id}/stats", function () use ($team, $sport_obj) {
			// Calculate some stats. We need to get stats from any team in this
			// division, so that it properly handles subs and people who move teams.
			$teams = $this->Teams->find()
				->where(['division_id' => $team->division_id])
				->combine('id', 'name')
				->toArray();
			if (empty($teams)) {
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

		usort($team->people, ['App\Model\Table\TeamsTable', 'compareRoster']);

		$this->set(compact('team', 'sport_obj'));
		$this->set('is_captain', in_array($id, $this->UserCache->read('OwnedTeamIDs')));
		$this->set('is_coordinator', in_array($team->division_id, $this->UserCache->read('DivisionIDs')));

		if ($this->request->is('csv')) {
			$this->response->download("Stats - {$team->name}.csv");
		}
	}

	public function stat_sheet() {
		$id = $this->request->query('team');
		$team = $this->Teams->find()
			->contain([
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
			])
			->where(['Teams.id' => $id])
			->first();

		if (!$team) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		if (!$team->division->league->hasStats()) {
			$this->Flash->info(__('This league does not have stat tracking enabled.'));
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}

		$this->set(compact('team'));
	}

	public function tooltip() {
		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->query('team');
		$contain = [
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
		];
		if (Configure::read('feature.annotations') && Configure::read('Perm.is_logged_in')) {
			$contain['Notes'] = [
				'queryBuilder' => function (Query $q) {
					return $q->where(['created_person_id' => $this->UserCache->currentId()]);
				},
			];
		}

		try {
			$team = $this->Teams->get($id, [
				'contain' => $contain,
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
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
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$team = $this->Teams->newEntity();

		if (!Configure::read('Perm.is_admin') && Configure::read('feature.registration')) {
			$this->Flash->info(__('This system creates teams through the registration process. Team creation through {0} is disabled. If you need a team created for some other reason (e.g. a touring team), please email {1} with the details, or call the office.', ZULURU, Configure::read('email.admin_email')));
			return $this->redirect('/');
		}

		if ($this->request->is('post')) {
			if (!Configure::read('Perm.is_admin') && (empty($this->request->data['affiliate_id']) || !in_array($this->request->data['affiliate_id'], $this->UserCache->read('ManagedAffiliateIDs')))) {
				$this->request->data['people'] = [[
					'id' => $this->UserCache->currentId(),
					'_joinData' => [
						'role' => 'captain',
						'status' => ROSTER_APPROVED,
					],
				]];
			}

			// Save the facility preference order
			if (!empty($this->request->data['facilities']['_ids'])) {
				foreach ($this->request->data['facilities']['_ids'] as $key => $facility_id) {
					$this->request->data['facilities'][$key] = [
						'id' => $facility_id,
						'_joinData' => [
							'rank' => $key + 1,
						],
					];
				}
				unset($this->request->data['facilities']['_ids']);
			}

			$team = $this->Teams->patchEntity($team, $this->request->data, [
				'associated' => ['People', 'Facilities']
			]);

			if ($this->Teams->save($team)) {
				if (Configure::read('Perm.is_admin')) {
					$this->Flash->success(__('The team has been saved.'));
				} else {
					$this->Flash->success(__('The team has been saved, but will not be visible until approved.'));
				}
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The team could not be saved. Please correct the errors below and try again.'));
			}

			$this->Configuration->loadAffiliate($this->request->data['affiliate_id']);
		}

		// TODO: A way to indicate which sport the team is for, and load only applicable facilities
		$affiliates = $this->_applicableAffiliates();
		$regions = TableRegistry::get('Regions')->find('list', [
			'conditions' => ['affiliate_id IN' => array_keys($affiliates)],
		])->toArray();

		$facilities = TableRegistry::get('Facilities')->find()
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

		$this->set(compact('team', 'affiliates', 'regions', 'facilities'));
		$this->set('_serialize', true);
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->query('team');
		try {
			$team = $this->Teams->get($id, [
				'contain' => [
					'Facilities',
					'Divisions' => [
						'Leagues',
						'GameSlots' => ['Fields' => ['Facilities']],
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($this->Teams->affiliate($id));

		if ($this->request->is(['patch', 'post', 'put'])) {
			// Save the facility preference order
			if (!empty($this->request->data['facilities']['_ids'])) {
				foreach ($this->request->data['facilities']['_ids'] as $key => $facility_id) {
					$this->request->data['facilities'][$key] = [
						'id' => $facility_id,
						'_joinData' => [
							'rank' => $key + 1,
						],
					];
				}
				unset($this->request->data['facilities']['_ids']);
			}

			$team = $this->Teams->patchEntity($team, $this->request->data, [
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
		$affiliates = $this->_applicableAffiliates();
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

		$regions = TableRegistry::get('Regions')->find('list', [
			'conditions' => $region_conditions,
		])->toArray();

		$facilities = TableRegistry::get('Facilities')->find()
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

		$this->set(compact('team', 'affiliates', 'regions', 'facilities'));
		$this->set('_serialize', true);
	}

	public function note() {
		$note_id = $this->request->query('note');

		if ($note_id) {
			try {
				$note = $this->Teams->Notes->get($note_id, [
					'contain' => ['Teams' => ['Divisions' => ['Leagues']]],
				]);

				// Check that this user is allowed to edit this note
				if ($note->created_person_id != Configure::read('Perm.my_id')) {
					$this->Flash->warning(__('You are not allowed to edit that note.'));
					return $this->redirect(['action' => 'view', 'team' => $note->team->id]);
				}
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid note.'));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid note.'));
				return $this->redirect('/');
			}
			$team = $note->team;
		} else {
			try {
				$team = $this->Teams->get($this->request->query('team'), [
					'contain' => ['Divisions' => ['Leagues']]
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid team.'));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid team.'));
				return $this->redirect('/');
			}
			$note = $this->Teams->Notes->newEntity();
			$note->team_id = $team->id;
		}

		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$note = $this->Teams->Notes->patchEntity($note, $this->request->data);

			if (empty($note->note)) {
				if ($note->isNew()) {
					$this->Flash->warning(__('You entered no text, so no note was added.'));
					return $this->redirect(['action' => 'view', 'team' => $team->id]);
				} else {
					if ($this->Teams->Notes->delete($note)) {
						$this->Flash->success(__('The note has been deleted.'));
						return $this->redirect(['action' => 'view', 'team' => $team->id]);
					} else if ($note->errors('delete')) {
						$this->Flash->warning(current($note->errors('delete')));
					} else {
						$this->Flash->warning(__('The note could not be deleted. Please, try again.'));
					}
				}
			} else if ($this->Teams->Notes->save($note)) {
				$this->Flash->success(__('The note has been saved.'));
				return $this->redirect(['action' => 'view', 'team' => $team->id]);
			} else {
				$this->Flash->warning(__('The note could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('team', 'note'));
		$this->set('_serialize', true);
	}

	public function delete_note() {
		$this->request->allowMethod(['post', 'delete']);

		$note_id = $this->request->query('note');

		try {
			$note = $this->Teams->Notes->get($note_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid note.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid note.'));
			return $this->redirect('/');
		}

		if ($note->created_person_id == Configure::read('Perm.my_id') ||
			(Configure::read('Perm.is_admin') && in_array($note->visibility, [VISIBILITY_ADMIN, VISIBILITY_COORDINATOR])) ||
			(in_array($note->team->division_id, $this->UserCache->read('DivisionIDs')) && $note->visibility == VISIBILITY_COORDINATOR)
		) {
			if ($this->Teams->Notes->delete($note)) {
				$this->Flash->success(__('The note has been deleted.'));
			} else if ($note->errors('delete')) {
				$this->Flash->warning(current($note->errors('delete')));
			} else {
				$this->Flash->warning(__('The note could not be deleted. Please, try again.'));
			}
		} else {
			$this->Flash->warning(__('You are not allowed to delete that note.'));
		}
		return $this->redirect(['action' => 'view', 'team' => $note->team_id]);
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->query('team');
		$dependencies = $this->Teams->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this team, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Teams->delete($team)) {
			if ($team->division_id) {
				$this->Teams->Divisions->clearCache($team->division, ['standings']);
			}
			$this->Flash->success(__('The team has been deleted.'));
		} else if ($team->errors('delete')) {
			$this->Flash->warning(current($team->errors('delete')));
		} else {
			$this->Flash->warning(__('The team could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	// TODO: Method for moving multiple teams at once; jQuery "left and right" boxes?
	public function move() {
		$id = $this->request->query('team');
		try {
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions' => ['Leagues', 'People']]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}
		$this->set(compact('team'));

		if ($this->request->is(['patch', 'post', 'put'])) {
			try {
				$division = $this->Teams->Divisions->get($this->request->data['to'], [
					'contain' => ['Leagues']
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid division.'));
				return $this->redirect(['action' => 'view', 'team' => $id]);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid division.'));
				return $this->redirect(['action' => 'view', 'team' => $id]);
			}
			// Don't do division comparisons when the team being moved is not in a division
			if ($team->division_id) {
				if ($team->division->league_id != $division->league_id) {
					$this->Flash->info(__('Cannot move a team to a different league.'));
					return $this->redirect(['action' => 'view', 'team' => $id]);
				}
				if ($division->ratio_rule != $team->division->ratio_rule) {
					$this->Flash->info(__('Destination division must have the same ratio rule.'));
					return $this->redirect(['action' => 'view', 'team' => $id]);
				}
			}
			$team->division_id = $this->request->data['to'];
			if ($this->Teams->save($team)) {
				$this->Flash->success(__('Team has been moved to {0}.', $division->full_league_name));
			} else {
				$this->Flash->warning(__('Failed to move the team!'));
			}
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}

		$conditions = [
			'OR' => [
				'Divisions.is_open' => true,
				'Divisions.open >' => FrozenDate::now(),
			],
			'Leagues.affiliate_id IN' => $this->_applicableAffiliateIDs(true),
		];
		if ($team->division_id) {
			$conditions += [
				'Divisions.id !=' => $team->division_id,
				'Divisions.league_id' => $team->division->league_id,
				'Divisions.ratio_rule' => $team->division->ratio_rule,
			];
		}
		$divisions = $this->Teams->Divisions->find()
			->contain(['Leagues'])
			->where($conditions)
			->toArray();

		// Make sure there's somewhere to move it to
		if (empty($divisions)) {
			$this->Flash->info(__('No similar division found to move this team to!'));
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}

		$this->set(compact('team', 'divisions'));
	}

	public function schedule() {
		$id = $this->request->query('team');
		try {
			$team = $this->Teams->get($id, [
				'contain' => [
					'People',
					'Divisions' => ['Leagues']
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		$team->games = TableRegistry::get('Games')
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

		if (empty($team->games)) {
			$this->Flash->info(__('This team has no games scheduled yet.'));
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}

		// Sort games by date, time and field
		usort($team->games, ['App\Model\Table\GamesTable', 'compareDateAndField']);

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
		$this->set('is_coordinator', in_array($team->division_id, $this->UserCache->read('DivisionIDs')));
		$this->set('is_captain', in_array($id, $this->UserCache->read('AllOwnedTeamIDs')));
		$this->set('spirit_obj', $this->moduleRegistry->load("Spirit:{$team->division->league->sotg_questions}"));
		$this->set('display_attendance', $team->track_attendance && (in_array($team->id, $this->UserCache->read('AllTeamIDs')) || in_array($team->id, $this->UserCache->read('AllRelativeTeamIDs'))));
		$this->set('annotate', Configure::read('feature.annotations') && in_array($team->id, $this->UserCache->read('TeamIDs')));
	}

    /**
     * iCal method
	 *
	 * This function takes the parameter the old-fashioned way, to try to be more third-party friendly
     *
     * @param string|null $id Team id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
	public function ical($id) {
		$this->viewBuilder()->layout('ical');
		$id = intval($id);
		try {
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions' => ['Leagues']]
			]);
		} catch (RecordNotFoundException $ex) {
			throw new GoneException();
		} catch (InvalidPrimaryKeyException $ex) {
			throw new GoneException();
		}

		if (empty($team->division_id) || $team->division->close < FrozenDate::now()->subWeeks(2)) {
			throw new GoneException();
		}

		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		$games = TableRegistry::get('Games')
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
		usort($games, ['App\Model\Table\GamesTable', 'compareDateAndField']);
		usort($events, ['App\Model\Table\GamesTable', 'compareDateAndField']);
		// Outlook only accepts the first event in a file, so we put the last game first
		$games = array_reverse($games);

		$this->set('calendar_type', 'Team Schedule');
		$this->set('calendar_name', "{$team->name} schedule");
		$this->response->download("$id.ics");
		$this->set('team_id', $id);
		$this->set('games', $games);
		$this->set('events', $events);
		$this->RequestHandler->ext = 'ics';
	}

	public function spirit() {
		$id = $this->request->query('team');
		try {
			$team = $this->Teams->get($id, [
				'contain' => ['Divisions' => ['Leagues']]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

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
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}

		// Sort games by date, time and field
		usort($team['games'], ['App\Model\Table\GamesTable', 'compareDateAndField']);

		$this->set(compact('team'));
		$this->set('spirit_obj', $this->moduleRegistry->load("Spirit:{$team->division->league->sotg_questions}"));
	}

	public function attendance() {
		$id = $this->request->query('team');
		try {
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
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect('/');
		}

		if (!$team->track_attendance) {
			$this->Flash->info(__('That team does not have attendance tracking enabled.'));
			return $this->redirect('/');
		}

		// Find the list of holidays to avoid
		$holidays_table = TableRegistry::get('Holidays');
		$holidays = $holidays_table->find('list', [
			'keyField' => 'id',
			'valueField' => 'date',
		])->toArray();

		// Calculate the expected list of dates that games will be on. For divisions that play
		// on multiple days, this will include only the first day of each week.
		$dates = [];
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);

			$days = array_unique(collection($team->division->days)->extract('id')->toArray());
			if (!empty($days)) {
				$play_day = min($days);
				for ($date = $team->division->open; $date <= $team->division->close; $date = $date->addDay()) {
					$day = $date->format('N');
					// TODO: If it is a holiday, and the division plays on multiple days,
					// try the other days to see if one is valid
					if ($day == $play_day && !in_array($date, $holidays)) {
						$dates[] = $date;
					}
				}

				// Daylight savings time can result in dates being duplicated
				// TODOLATER: Still true?
				$dates = array_unique($dates);
			}
		}

		$attendance = $this->Teams->Divisions->Games->readAttendance($team, $days, null, $dates);
		$event_attendance = $this->Teams->TeamEvents->readAttendance($team);

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

		$this->set(compact('team', 'attendance', 'event_attendance', 'dates', 'days', 'games'));
		$this->set('is_captain', in_array($id, $this->UserCache->read('OwnedTeamIDs')));
	}

	public function emails() {
		$id = $this->request->query('team');
		try {
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
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		$this->set(compact('team'));
	}

	public function add_player() {
		$id = $this->request->query('team');
		try {
			$team = $this->Teams->get($id, [
				'contain' => [
					'Divisions' => ['Leagues'],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		$this->set(compact('team'));

		$this->_handlePersonSearch(['team'], ['group_id IN' => [GROUP_PLAYER,GROUP_COACH]]);

		// Only show teams from divisions that have some schedule type
		$teams = array_reverse($this->UserCache->read('AllTeams'));
		foreach ($teams as $key => $past_team) {
			if ($past_team->division_id == $team->division_id || empty($past_team->division_id) || $past_team->division->schedule_type == 'none') {
				unset($teams[$key]);
			}
		}
		$this->set(compact('teams'));

		// Admins and coordinators get to add people based on registration events
		if ($this->_canEditRoster($team, false) === true) {
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

			$events = TableRegistry::get('Events')->find()
				->where($conditions)
				->order(['Events.event_type_id', 'Events.open', 'Events.close', 'Events.id'])
				->toArray();
			$this->set(compact('events'));
		}
	}

	public function add_from_team() {
		$this->request->allowMethod(['post']);

		$id = $this->request->query('team');

		// Read the current team roster
		try {
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
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		// Read the old team roster
		try {
			$old_team = $this->Teams->get($this->request->data['team'], [
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
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}

		// If this is a form submission, set the role for each player
		if (array_key_exists('player', $this->request->data)) {
			$result = [];
			foreach ($this->request->data['player'] as $player => $data) {
				if (!empty($data['role']) && $data['role'] != 'none') {
					$person = collection($old_team->people)->firstMatch(['id' => $player]);
					if ($person) {
						$person->unsetProperty('_joinData');
						// TODO: If the team has numbers, take care of that here too
						$result[$this->_setRosterRole($person, $team, ROSTER_INVITED, $data['role'], $data['position'])][] = $person->full_name;
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
						Text::toList($result[ROSTER_APPROVED])
					);
					$class = 'success';
				}
				if (!empty($result[ROSTER_INVITED])) {
					$msg[] = __n('Invitation has been sent to {0}.', 'Invitations have been sent to {0}.',
						count($result[ROSTER_INVITED]),
						Text::toList($result[ROSTER_INVITED])
					);
					$class = 'success';
				}
				if (!empty($result[false])) {
					$msg[] = __n('Failed to send invitation to {1}.', 'Failed to send invitations to {1}.',
						count($result[false]),
						Text::toList($result[false])
					);
					$class = 'warning';
				}
			}
			$this->Flash->{$class}(implode(' ', $msg));
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}

		foreach ($old_team->people as $person) {
			$person->can_add = $this->_canAdd($person, $team, 'player', null, false, true);
			// By passing false here for the current role, "none" won't be eliminated as an option
			$person->roster_role_options = $this->_rosterRoleOptions(false, $team, $person->id);
		}

		$this->set(compact('team', 'old_team'));
	}

	public function add_from_event() {
		$this->request->allowMethod(['post']);

		$id = $this->request->query('team');

		try {
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
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect(['action' => 'index']);
		}
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
				->extract('teams.{*}.people.{*}.id')
				->toList();
		} else {
			$current = collection($team->people)->extract('id')->toArray();
		}

		// Read the event
		try {
			$this->loadModel('Events');
			$event = $this->Events->get($this->request->data['event'], [
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
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		}

		$event->registrations = collection($event->registrations)->sortBy(function (Registration $registration) {
			return $registration->person->last_name . '-' . $registration->person->first_name;
		}, SORT_ASC, SORT_STRING | SORT_FLAG_CASE);

		// If this is a form submission, set the role for each player
		if (array_key_exists('player', $this->request->data)) {
			$result = [];
			foreach ($this->request->data['player'] as $player => $data) {
				if (!empty($data['role']) && $data['role'] != 'none') {
					$registration = collection($event->registrations)->firstMatch(['person_id' => $player]);
					if ($registration) {
						$registration->person->unsetProperty('_joinData');
						// TODO: If the team has numbers, take care of that here too
						$result[$this->_setRosterRole($registration->person, $team, ROSTER_APPROVED, $data['role'], $data['position'])][] = $registration->person->full_name;
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
						Text::toList($result[ROSTER_APPROVED])
					);
					$class = 'success';
				}
				if (!empty($result[ROSTER_INVITED])) {
					$msg[] = __n('Invitation has been sent to {0}.', 'Invitations have been sent to {0}.',
						count($result[ROSTER_INVITED]),
						Text::toList($result[ROSTER_INVITED])
					);
					$class = 'success';
				}
				if (!empty($result[false])) {
					$msg[] = __n('Failed to send invitation to {1}.', 'Failed to send invitations to {1}.',
						count($result[false]),
						Text::toList($result[false])
					);
					$class = 'warning';
				}
			}
			$this->Flash->{$class}(implode(' ', $msg));
			return $this->redirect(['action' => 'view', 'team' => $id]);
		}

		foreach ($event->registrations as $registration) {
			$registration->can_add = $this->_canAdd($registration->person, $team, 'player', null, false, true);
			// By passing false here for the current role, "none" won't be eliminated as an option
			$registration->roster_role_options = $this->_rosterRoleOptions(false, $team, $registration->person->id);
		}

		$this->set(compact('team', 'event'));
	}

	public function roster_role() {
		$person_id = $this->request->query('person');
		if (!$person_id) {
			$person_id = $this->UserCache->currentId();
		}

		try {
			list($team, $person) = $this->_initTeamForRosterChange($person_id);
		} catch (Exception $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		if (empty($person)) {
			$this->Flash->info(__('This person is not on this team.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		$role = $person->_joinData->role;
		$roster_role_options = $this->_rosterRoleOptions($role, $team, $person_id);
		$this->set(compact('person', 'team', 'role', 'roster_role_options'));

		if ($person->_joinData->status != ROSTER_APPROVED) {
			$this->Flash->info(__('A player\'s role on a team cannot be changed until they have been approved on the roster.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		// Check if this user is the only approved captain on the team
		$required_roles = Configure::read('required_roster_roles');
		if (in_array($role, $required_roles) &&
			!in_array($this->request->data['role'], $required_roles) /*&&
			!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager')*/)
		{
			$captains = collection($team->people)->filter(function ($person) use ($required_roles) {
				return in_array($person->_joinData->role, $required_roles) && $person->_joinData->status == ROSTER_APPROVED;
			})->toArray();
			if (count($captains) == 1) {
				$this->Flash->info(__('All teams must have at least one player as coach or captain.'));
				return $this->redirect(['action' => 'view', 'team' => $team->id]);
			}
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			if (!array_key_exists($this->request->data['role'], $roster_role_options)) {
				$this->Flash->info(__('You do not have permission to set that role.'));
			} else {
				if ($this->_setRosterRole($person, $team, ROSTER_APPROVED, $this->request->data['role'])) {
					$this->UserCache->_deleteTeamData($person_id);
					if ($this->request->is('ajax')) {
						return;
					}
				}
			}
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}
	}

	public function roster_position() {
		$person_id = $this->request->query('person');
		if (!$person_id) {
			$person_id = $this->UserCache->currentId();
		}

		try {
			list($team, $person) = $this->_initTeamForRosterChange($person_id);
		} catch (Exception $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		if (empty($person)) {
			$this->Flash->info(__('This person is not on this team.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		$position = $person->_joinData->position;
		if ($team->division_id) {
			$sport = $team->division->league->sport;
		} else if (count(Configure::read('options.sport')) == 1) {
			$sport = current(Configure::read('options.sport'));
		} else {
			$this->Flash->info(__('A position cannot be assigned until this team is placed in a division.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}
		$roster_position_options = Configure::read("sports.$sport.positions");
		$this->set(compact('person', 'team', 'position', 'roster_position_options'));

		if ($this->request->is(['patch', 'post', 'put'])) {
			if (!array_key_exists($this->request->data['position'], $roster_position_options)) {
				$this->Flash->info(__('That is not a valid position.'));
			} else {
				$person->_joinData->position = $this->request->data['position'];
				if ($this->Teams->People->link($team, [$person], compact('person', 'team'))) {
					$this->UserCache->_deleteTeamData($person_id);
					if ($this->request->is('ajax')) {
						return;
					}
					$this->Flash->success(__('Changed the player\'s position.'));
				} else {
					$this->Flash->warning(__('Failed to change the player\'s position.'));
				}
			}
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}
	}

	public function roster_add() {
		$person_id = $this->request->query('person');

		try {
			list($team, $person) = $this->_initTeamForRosterChange($person_id);
		} catch (Exception $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		if (!empty($person)) {
			$this->Flash->info(__('This person is already on this team.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		// Read the bare player record
		try {
			$person = $this->Teams->People->get($person_id, [
				'contain' => [Configure::read('Security.authModel')]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid player.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid player.'));
			return $this->redirect('/');
		}

		// If a role was submitted, try to set it. Whether it succeeds or fails,
		// we'll go back to the team view page, and the flash message will tell the
		// user why. It should only fail in the case of malicious form tinkering, so
		// we don't try hard to let them correct the error.
		if ($this->request->is(['patch', 'post', 'put'])) {
			if (!empty($this->request->data['role'])) {
				$this->_setRosterRole($person, $team, ROSTER_INVITED, $this->request->data['role'], $this->request->data['position']);
				return $this->redirect(['action' => 'view', 'team' => $team->id]);
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
				return $this->redirect(['action' => 'view', 'team' => $team->id]);
			}
		}

		$roster_role_options = $this->_rosterRoleOptions('none', $team, $person_id);
		// TODO: In addition to checking the roster method, check if they were on an affiliate's roster.
		$adding = ($can_add === true && $team->division->roster_method == 'add');

		$this->set(compact('person', 'team', 'roster_role_options', 'can_add', 'adding'));
	}

	public function roster_request() {
		try {
			list($team, $person) = $this->_initTeamForRosterChange(Configure::read('Perm.my_id'));
		} catch (Exception $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		if (!empty($person)) {
			$this->Flash->info(__('You are already on this team.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		// Read the bare player record
		try {
			$person = $this->Teams->People->get(Configure::read('Perm.my_id'), [
				'contain' => [Configure::read('Security.authModel')]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid player.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid player.'));
			return $this->redirect('/');
		}

		// Check if this person can even be added
		$can_add = $this->_canAdd($person, $team);
		if ($can_add !== true) {
			$this->Flash->html('{0}', ['params' => ['class' => 'warning', 'replacements' => [$can_add]]]);
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		$roster_role_options = $this->_rosterRoleOptions('none', $team, Configure::read('Perm.my_id'), false);

		if ($this->request->is(['patch', 'post', 'put'])) {
			if (!array_key_exists($this->request->data['role'], $roster_role_options)) {
				$this->Flash->info(__('You are not allowed to request that role.'));
			} else if ($this->_setRosterRole($person, $team, ROSTER_REQUESTED, $this->request->data['role'],
				array_key_exists('position', $this->request->data) ? $this->request->data['position'] : 'unspecified'
			)) {
				$this->UserCache->_deleteTeamData();
			}
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		$this->set(compact('person', 'team', 'roster_role_options'));
	}

	public function roster_accept() {
		$person_id = $this->request->query('person');
		if (!$person_id) {
			$person_id = Configure::read('Perm.my_id');
		}
		$is_me = ($person_id == Configure::read('Perm.my_id') || in_array($person_id, $this->UserCache->read('RelativeIDs')));

		try {
			list($team, $person) = $this->_initTeamForRosterChange($person_id, false);
		} catch (Exception $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		if (empty($person)) {
			$this->Flash->info(__('This person has neither been invited nor requested to join this team.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		if ($person->_joinData->status == ROSTER_APPROVED) {
			$this->Flash->info(__('This person has already been added to the roster.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->request->query('code');
		if ($code) {
			// Authenticate the hash code
			if (!$this->_checkHash([$person->_joinData->id, $person->_joinData->team_id, $person->_joinData->person_id, $person->_joinData->role, $person->_joinData->created], $code)) {
				$this->Flash->warning(__('The authorization code is invalid.'));
				return $this->redirect(['action' => 'view', 'team' => $team->id]);
			}
		} else {
			// Check for coordinator or admin override
			if ($this->_canEditRoster($team, false) !== true &&
				// Players can accept when they are invited
				!($person->_joinData->status == ROSTER_INVITED && $is_me) &&
				// Captains can accept requests to join their teams
				!($person->_joinData->status == ROSTER_REQUESTED && in_array($team->id, $this->UserCache->read('OwnedTeamIDs')))
			)
			{
				$this->Flash->warning(__('You are not allowed to accept this roster {0}.',
					($person->_joinData->status == ROSTER_INVITED) ? __('invitation') : __('request')));
				return $this->redirect(['action' => 'view', 'team' => $team->id]);
			}
		}

		// Check if this person can even be added
		$can_add = $this->_canAdd($person, $team, $person->_joinData->role, $person->_joinData->status, true, $this->request->is('ajax'));
		if ($can_add !== true) {
			if (Configure::read('Perm.is_logged_in') && !empty($this->can_add_rule_obj->redirect)) {
//				$this->queueRedirect($this->request->here());
				if ($this->request->is('ajax')) {
					return $this->redirect(array_merge($this->can_add_rule_obj->redirect, ['return' => $this->_return()]), 100);
				} else {
					return $this->redirect(array_merge($this->can_add_rule_obj->redirect, ['return' => $this->_return()]));
				}
			}
			if ($this->request->is('ajax')) {
				$this->Flash->warning(ZuluruHtmlHelper::formatTextMessage(['format' => '{0}', 'replacements' => [$can_add]]));
			} else {
				$this->Flash->html('{0}', ['params' => ['class' => 'warning', 'replacements' => [$can_add]]]);
			}
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		$oldStatus = $person->_joinData->status;
		$person->_joinData->status = ROSTER_APPROVED;
		if ($this->Teams->People->link($team, [$person], compact('person', 'team'))) {
			// Send email to the affected people
			if (Configure::read('feature.generate_roster_email')) {
				$this->_sendAccept($person, $team, $person->_joinData->role, $oldStatus);
			}

			$this->UserCache->_deleteTeamData($person_id);

			if ($this->request->is('ajax')) {
				$this->set(compact('person', 'team'));
				return;
			}

			$this->Flash->success(__('You have accepted this roster {0}.',
				($person->_joinData->status == ROSTER_INVITED) ? __('invitation') : __('request')));
		} else {
			$this->Flash->warning(__('The database failed to save the acceptance of this roster {0}.',
				($person->_joinData->status == ROSTER_INVITED) ? __('invitation') : __('request')));
		}
		return $this->redirect(['action' => 'view', 'team' => $team->id]);
	}

	public function roster_decline() {
		$person_id = $this->request->query('person');
		if (!$person_id) {
			$person_id = Configure::read('Perm.my_id');
		}
		$is_me = ($person_id == Configure::read('Perm.my_id') || in_array($person_id, $this->UserCache->read('RelativeIDs')));

		try {
			list($team, $person) = $this->_initTeamForRosterChange($person_id, false);
		} catch (Exception $ex) {
			$this->Flash->info($ex->getMessage());
			return $this->redirect('/');
		}

		if (empty($person)) {
			$this->Flash->info(__('This person has neither been invited nor requested to join this team.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		if ($person->_joinData->status == ROSTER_APPROVED) {
			$this->Flash->info(__('This person has already been added to the roster.'));
			return $this->redirect(['action' => 'view', 'team' => $team->id]);
		}

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->request->query('code');
		if ($code) {
			// Authenticate the hash code
			if (!$this->_checkHash([$person->_joinData->id, $person->_joinData->team_id, $person->_joinData->person_id, $person->_joinData->role, $person->_joinData->created], $code)) {
				$this->Flash->warning(__('The authorization code is invalid.'));
				return $this->redirect(['action' => 'view', 'team' => $team->id]);
			}
		} else {
			// Check for coordinator or admin override
			if ($this->_canEditRoster($team, false) !== true &&
				// Players or captains can either decline an invite or request from the other,
				// or remove one that they made themselves.
				!$is_me && !(in_array($team->id, $this->UserCache->read('OwnedTeamIDs')))
			)
			{
				$this->Flash->warning(__('You are not allowed to decline this roster {0}.',
					($person->_joinData->status == ROSTER_INVITED) ? __('invitation') : __('request')));
				return $this->redirect(['action' => 'view', 'team' => $team->id]);
			}
		}

		$this->Teams->People->unlink($team, [$person], compact('person', 'team'));

		// Send email to the affected people
		if (Configure::read('feature.generate_roster_email')) {
			$this->_sendDecline($person, $team, $person->_joinData->role, $person->_joinData->status);
		}

		$this->UserCache->_deleteTeamData($person_id);

		if ($this->request->is('ajax')) {
			return;
		}

		$this->Flash->success(__('You have declined this roster {0}.',
			($person->_joinData->status == ROSTER_INVITED) ? __('invitation') : __('request')));
		if ($person_id == Configure::read('Perm.my_id')) {
			return $this->redirect('/');
		}
		return $this->redirect(['action' => 'view', 'team' => $team->id]);
	}

	protected function _canEditRoster($team_id, $allow_captain = true) {
		$teams_table = TableRegistry::get('Teams');
		return $teams_table->canEditRoster($team_id, Configure::read('Perm.is_admin'), Configure::read('Perm.is_manager'), $allow_captain);
	}

	protected function _initTeamForRosterChange($person_id, $check_permission = true) {
		if (!$person_id) {
			throw new Exception(__('Invalid player.'));
		}

		$team_id = $this->request->query('team');
		try {
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
		} catch (RecordNotFoundException $ex) {
			throw new Exception(__('Invalid team.'));
		} catch (InvalidPrimaryKeyException $ex) {
			throw new Exception(__('Invalid team.'));
		}
		if (empty($team->division_id)) {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		}

		// Pull out the player record from the team
		$person = collection($team->people)->firstMatch(['id' => $person_id]);

		$can_edit_roster = $this->_canEditRoster($team);
		$is_me = ($person_id == Configure::read('Perm.my_id') || in_array($person_id, $this->UserCache->read('RelativeIDs')));
		$permission = (!$check_permission || $can_edit_roster === true || ($team->division_id && !$team->division->roster_deadline_passed && $is_me));
		if (!$permission) {
			if ($can_edit_roster === false) {
				throw new Exception(__('The roster deadline for this division has already passed.'));
			} else {
				throw new Exception($can_edit_roster);
			}
		}

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
		$groups = $this->UserCache->read('GroupIDs', $person_id);
		if (!in_array(GROUP_PLAYER, $groups)) {
			foreach (Configure::read('extended_playing_roster_roles') as $playing_role) {
				unset($roster_role_options[$playing_role]);
			}
		}

		// Admins, coordinators and captains can make anyone anything that's left
		if ($this->Teams->canEditRoster($team, Configure::read('Perm.is_admin') && $allow_override, Configure::read('Perm.is_manager') && $allow_override) === true) {
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
					return $this->redirect(['action' => 'view', 'team' => $team->id]);
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

			if (!$this->request->is('ajax')) {
				$this->Flash->success(__('Removed the player from the team.'));
			}
			if (Configure::read('feature.generate_roster_email')) {
				$this->_sendRemove($person, $team, $person->_joinData->getOriginal('role'));
			}
			$this->UserCache->_deleteTeamData($person->id);
			return true;
		}

		$can_add = $this->_canAdd($person, $team, $role, $status);
		$accept_warning = null;
		if ($can_add === true) {
			// Under certain circumstances, an invite is changed to an add
			// TODO: In addition to checking the roster method, check if they were on an affiliate's roster.
			if ($status === ROSTER_INVITED && $team->division->roster_method == 'add') {
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
				if ($this->request->is('ajax')) {
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
		if ($this->Teams->People->link($team, [$person], compact('person', 'team')) && empty($person->errors())) {
			$this->UserCache->_deleteTeamData($person->id);
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

	protected function _canAdd($person, $team, $role = null, $status = null, $strict = true, $text_reason = false, $absolute_url = false) {
		if ($person->status == 'new') {
			return __('New players must be approved by an administrator before they can be added to a team; this normally happens within one business day.');
		}
		if (!$person->complete) {
			return __('This person has not yet completed their profile. Please contact them directly to have them complete their profile.');
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
			if (!$person->has('groups')) {
				$person->groups = $this->UserCache->read('Groups', $person->id);
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
				unset($person->groups);
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
			'subject' => __('You have been added to {0}', $team->name),
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
			'subject' => __('Invitation to join {0}', $team->name),
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
		list($captains, $captain_names) = $this->_initRosterCaptains($team);

		if (!$this->_sendMail([
			'to' => $captains,
			'replyTo' => $person,
			'subject' => __('{0} requested to join {1}', $person->full_name, $team->name),
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
			list($captains, $captain_names) = $this->_initRosterCaptains($team);

			if (!$this->_sendMail([
				'to' => $captains,
				'replyTo' => $person,
				'subject' => __('Invitation for {0} to join {1} was accepted', $person->full_name, $team->name),
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
				'subject' => __('Request to join {0} was accepted', $team->name),
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
			$is_player = ($this->request->query('code') !== null || $person->id == $this->UserCache->currentId() || in_array($person->id, $this->UserCache->read('RelativeIDs')));
			$is_captain = in_array($team->id, $this->UserCache->read('OwnedTeamIDs'));

			if (!$is_captain) {
				// A player or admin has declined an invitation
				list($captains, $captain_names) = $this->_initRosterCaptains($team);

				if (!$this->_sendMail([
					'to' => $captains,
					'replyTo' => $person,
					'subject' => __('{0} declined your invitation to join {1}', $person->full_name, $team->name),
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
					'subject' => __('Invitation to join {0} was removed', $team->name),
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
				'subject' => __('Request to join {0} was declined', $team->name),
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
			list($captains, $captain_names) = $this->_initRosterCaptains($team);

			if (!$this->_sendMail([
				'to' => $captains,
				'replyTo' => $person,
				'subject' => __('{0} role change on {1} roster', $person->full_name, $team->name),
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
				'subject' => __('Change of roster role on {0}', $team->name),
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
			list($captains, $captain_names) = $this->_initRosterCaptains($team);

			if (!$this->_sendMail([
				'to' => $captains,
				'replyTo' => $person,
				'subject' => __('{0} removed from {1} roster', $person->full_name, $team->name),
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
				'subject' => __('Removal from {0} roster', $team->name),
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
						return $q->where([
							'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
							'TeamsPeople.status' => ROSTER_APPROVED,
							'TeamsPeople.person_id !=' => $this->UserCache->currentId(),
						]);
					},
					Configure::read('Security.authModel'),
				],
			]
		]);

		return [$team_captains->people, implode(', ', collection($team_captains->people)->extract('first_name')->toArray())];
	}

}
