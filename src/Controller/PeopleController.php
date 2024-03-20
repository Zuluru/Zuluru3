<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use Authorization\Exception\ForbiddenException;
use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Http\Cookie\Cookie;
use Cake\Http\Exception\GoneException;
use Cake\I18n\FrozenDate;
use Cake\I18n\I18n;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use App\PasswordHasher\HasherTrait;
use App\Exception\RuleException;
use App\Model\Entity\BadgesPerson;
use App\Model\Entity\Person;
use App\Model\Entity\PeoplePerson;
use App\Model\Table\GamesTable;

/**
 * People Controller
 *
 * @property \App\Model\Table\PeopleTable $People
 */
class PeopleController extends AppController {

	use HasherTrait;

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		// Relative approvals and removals may come from emailed links; people might not be logged in
		return ['view', 'tooltip', 'approve_relative', 'remove_relative', 'vcf', 'ical'];
	}

	/**
	 * _noAuthenticationJsonActions method
	 *
	 * @return array of JSON actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationJsonActions() {
		return ['view'];
	}

	/**
	 * _freeActions method
	 *
	 * @return array list of actions that people can perform even if the system wants them to do something else
	 */
	protected function _freeActions() {
		return ['edit', 'act_as'];
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));
		$group_id = $this->getRequest()->getQuery('group');

		// TODO: Multiple default sort fields break pagination links.
		// https://github.com/cakephp/cakephp/issues/7324 has related info.
		//$this->paginate['order'] = ['People.last_name', 'People.first_name', 'People.id'];
		$this->paginate['order'] = ['People.last_name'];

		$query = $this->People->find()
			->distinct(['People.id'])
			->contain(Configure::read('Security.authModel'))
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->order(['Affiliates.name']);

		if ($group_id) {
			$query->matching('Groups', function (Query $q) use ($group_id) {
				return $q->where(['Groups.id' => $group_id]);
			});
			try {
				$group = $this->People->Groups->field('name', ['Groups.id' => $group_id]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid group.'));
				return $this->redirect('/');
			}
			$this->set(compact('group'));
		}

		if ($this->getRequest()->is('csv')) {
			if ($group_id) {
				$this->setResponse($this->getResponse()->withDownload(Inflector::pluralize($group) . '.csv'));
			} else {
				$this->setResponse($this->getResponse()->withDownload('People.csv'));
			}
			$this->set('people', $query->contain(['Related']));
			$this->render('rule_search');
		} else {
			if (Configure::read('feature.badges')) {
				$badge_obj = $this->moduleRegistry->load('Badge');
				$query->contain(['Badges' => [
					'queryBuilder' => function (Query $q) use ($badge_obj) {
						return $q->where([
							'BadgesPeople.approved' => true,
							'Badges.visibility IN' => $badge_obj->visibility($this->Authentication->getIdentity(), BADGE_VISIBILITY_HIGH),
						]);
					},
				]]);
			}

			$this->set('people', $this->paginate($query));
		}
	}

	public function statistics() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		// Get the list of accounts by status
		$query = $this->People->find();
		$this->set('status_count', $query
			->select(['status', 'person_count' => $query->func()->count('People.id')])
			->select($this->People->Affiliates)
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->group(['AffiliatesPeople.affiliate_id', 'People.status'])
			->order(['Affiliates.name', 'People.status'])
		);

		// Get the list of players by gender
		$query = $this->People->find();
		$this->set('gender_count', $query
			->select(['gender', 'person_count' => $query->func()->count('People.id')])
			->select($this->People->Affiliates)
			->select($this->People->Skills)
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->matching('Groups', function (Query $q) {
				return $q->where(['Groups.id' => GROUP_PLAYER]);
			})
			->leftJoinWith('Skills')
			->where(['Skills.enabled' => true, 'People.status' => 'active'])
			->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'People.gender'])
			->order(['Affiliates.name', 'Skills.sport', 'People.gender' => 'DESC'])
		);

		// Get the list of players by roster designation
		if (Configure::read('gender.column') == 'roster_designation') {
			$query = $this->People->find();
			$this->set('roster_designation_count', $query
				->select([Configure::read('gender.column'), 'person_count' => $query->func()->count('People.id')])
				->select($this->People->Affiliates)
				->select($this->People->Skills)
				->matching('Affiliates', function (Query $q) use ($affiliates) {
					return $q->where(['Affiliates.id IN' => $affiliates]);
				})
				->matching('Groups', function (Query $q) {
					return $q->where(['Groups.id' => GROUP_PLAYER]);
				})
				->leftJoinWith('Skills')
				->where(['Skills.enabled' => true, 'People.status' => 'active'])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'People.' . Configure::read('gender.column')])
				->order(['Affiliates.name', 'Skills.sport', 'People.' . Configure::read('gender.column') => Configure::read('gender.order')])
			);
		}

		// Get the list of accounts by group
		$query = $this->People->find();
		$this->set('group_count', $query
			->select([Configure::read('gender.column'), 'person_count' => $query->func()->count('People.id')])
			->select($this->People->Affiliates)
			->select($this->People->Groups)
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->matching('Groups')
			->where(['People.status' => 'active'])
			->group(['AffiliatesPeople.affiliate_id', 'Groups.id'])
			->order(['Affiliates.name', 'Groups.id'])
		);

		// Get the list of players by age
		if (Configure::read('profile.birthdate')) {
			$query = $this->People->find();
			$this->set('age_count', $query
				->select([
					// TODO: Use a query function for the age bucket
					'age_bucket' => 'FLOOR((YEAR(NOW()) - YEAR(birthdate)) / 5) * 5',
					'person_count' => $query->func()->count('People.id'),
				])
				->select($this->People->Affiliates)
				->select($this->People->Skills)
				->matching('Affiliates', function (Query $q) use ($affiliates) {
					return $q->where(['Affiliates.id IN' => $affiliates]);
				})
				->matching('Groups', function (Query $q) {
					return $q->where(['Groups.id' => GROUP_PLAYER]);
				})
				->leftJoinWith('Skills')
				->where([
					'Skills.enabled' => true,
					'People.status' => 'active',
					'birthdate IS NOT' => null,
					'birthdate !=' => '0000-00-00',
				])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'age_bucket'])
				->order(['Affiliates.name', 'Skills.sport', 'age_bucket'])
			);
		}

		// Get the list of players by year started for each sport
		if (Configure::read('profile.year_started')) {
			$query = $this->People->find();
			$this->set('started_count', $query
				->select(['person_count' => $query->func()->count('People.id')])
				->select($this->People->Affiliates)
				->select($this->People->Skills)
				->matching('Affiliates', function (Query $q) use ($affiliates) {
					return $q->where(['Affiliates.id IN' => $affiliates]);
				})
				->matching('Groups', function (Query $q) {
					return $q->where(['Groups.id' => GROUP_PLAYER]);
				})
				->leftJoinWith('Skills')
				->where(['Skills.enabled' => true, 'People.status' => 'active'])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'Skills.year_started'])
				->order(['Affiliates.name', 'Skills.sport', 'Skills.year_started'])
			);
		}

		// Get the list of players by skill level for each sport
		if (Configure::read('profile.skill_level')) {
			$query = $this->People->find();
			$this->set('skill_count', $query
				->select(['person_count' => $query->func()->count('People.id')])
				->select($this->People->Affiliates)
				->select($this->People->Skills)
				->matching('Affiliates', function (Query $q) use ($affiliates) {
					return $q->where(['Affiliates.id IN' => $affiliates]);
				})
				->matching('Groups', function (Query $q) {
					return $q->where(['Groups.id' => GROUP_PLAYER]);
				})
				->leftJoinWith('Skills')
				->where(['Skills.enabled' => true, 'People.status' => 'active'])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'Skills.skill_level'])
				->order(['Affiliates.name', 'Skills.sport', 'Skills.skill_level'])
			);
		}

		// Get the list of players by city
		if (Configure::read('profile.addr_city')) {
			$query = $this->People->find();
			$this->set('city_count', $query
				->select(['addr_city', 'person_count' => $query->func()->count('People.id')])
				->select($this->People->Affiliates)
				->select($this->People->Skills)
				->matching('Affiliates', function (Query $q) use ($affiliates) {
					return $q->where(['Affiliates.id IN' => $affiliates]);
				})
				->matching('Groups', function (Query $q) {
					return $q->where(['Groups.id' => GROUP_PLAYER]);
				})
				->leftJoinWith('Skills')
				->where(['Skills.enabled' => true, 'People.status' => 'active'])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'addr_city'])
				->having(['person_count >' => 2])
				->order(['Affiliates.name', 'Skills.sport', 'person_count' => 'DESC'])
			);
		}
	}

	public function demographics() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		// Get the list of members by gender for each sport
		$buckets = $demographics = [];
		// TODO: Allow for pulling demographics from past years
		$reportDate = new FrozenDate('Aug 31');
		if (FrozenDate::now()->month < 3) {
			$reportDate = $reportDate->subYear();
		}
		$end = $reportDate->addDay(); // Registrations have times, we'll find anything less than this to include the whole report date day.
		$start = $end->subYear();

		foreach (Configure::read('options.sport') as $sport) {
			$buckets[$sport] = Configure::read("sports.$sport.demographic_ranges");
			if (!$buckets[$sport]) {
				$buckets[$sport] = Configure::read('demographic_ranges');
			}

			$query = $this->People->find();
			$cases = [$query->newExpr()->isNull('birthdate')];
			$names = ['Unknown'];
			$types = ['string'];
			$min = 0;
			foreach ($buckets[$sport] as $key => $next) {
				$max = $next - 1;
				$cases[] = $query->newExpr()->gt('birthdate', $reportDate->subYears($next));
				$names[] = "{$min}-{$max}";
				$types[] = 'string';
				$min = $next;
			}
			$cases[] = $query->newExpr()->lte('birthdate', $reportDate->subYears($next));
			$names[] = "{$next}+";
			$types[] = 'string';

			// Determine who are members. Are there membership registrations?
			$membershipEventList = TableRegistry::getTableLocator()->get('Events')->find()
				->contain(['EventTypes'])
				->where(['EventTypes.type' => 'membership'])
				->order(['Events.open', 'Events.close', 'Events.id']);

			// We are interested in memberships that covered this year
			$eventNames = $membershipEventIds = $leagueNames = [];
			foreach ($membershipEventList as $event) {
				if ($event->membership_begins < $end && $event->membership_ends >= $start) {
					$eventNames[$event->id] = $event->name;
					$membershipEventIds[] = $event->id;
				}
			}

			if (!empty($membershipEventIds)) {
				$people = TableRegistry::getTableLocator()->get('Registrations')->find()
					->select('person_id')
					->where([
						'Registrations.created <' => $end,
						'Registrations.payment' => 'Paid',
						'Registrations.event_id IN'  => $membershipEventIds,
					]);
			} else {
				// If there are no memberships, we look at teams that played in this time
				$conditions = [
					'Divisions.schedule_type NOT IN' => ['tournament', 'none'],
					'Divisions.open <' => $end,
					'Divisions.close >=' => $start,
				];

				$leagueNames = TableRegistry::getTableLocator()->get('Divisions')->find()
					->contain(['Leagues'])
					->where($conditions)
					->extract('league.full_name')
					->toArray();

				$divisions = TableRegistry::getTableLocator()->get('Divisions')->find()
					->select('id')
					->where($conditions);
				$people = TableRegistry::getTableLocator()->get('TeamsPeople')->find()
					->distinct('person_id')
					->select('person_id')
					->contain(['Teams'])
					->where([
						'Teams.division_id IN' => $divisions,
						'TeamsPeople.role IN' => Configure::read('playing_roster_roles'),
					]);
			}

			$demographics[$sport] = $query
				->select([
					'gender',
					'bucket' => $query->newExpr()->addCase($cases, $names, $types),
					'person_count' => $query->func()->count('People.id'),
				])
				->select($this->People->Affiliates)
				->where(['People.id IN' => $people])
				->matching('Affiliates', function (Query $q) use ($affiliates) {
					return $q->where(['Affiliates.id IN' => $affiliates]);
				})
				->matching('Skills', function (Query $q) use ($sport) {
					return $q->where(['Skills.enabled' => true, 'Skills.sport' => $sport]);
				})
				->matching('Groups', function (Query $q) {
					return $q->where(['Groups.id' => GROUP_PLAYER]);
				})
				->group(['AffiliatesPeople.affiliate_id', 'People.gender', 'bucket'])
				->order(['Affiliates.name', 'People.gender', 'bucket'])
				->groupBy('_matchingData.Affiliates.id')
				->map(function ($group) {
					return collection($group)
						->groupBy('bucket')
						->map(function ($group) {
							return collection($group)->indexBy('gender')->toArray();
						})
						->toArray();
				})
				->toArray();
		}

		$this->set(compact('demographics', 'start', 'reportDate', 'eventNames', 'leagueNames'));
	}

	public function participation() {
		$this->Authorization->authorize($this);

		$first_event = TableRegistry::getTableLocator()->get('Events')->find()->order('Events.open')->first();
		$first_league = TableRegistry::getTableLocator()->get('Leagues')->find()->order('Leagues.open')->first();

		$min = min(
			$first_event ? $first_event->open->year : FrozenDate::now()->year,
			$first_league ? $first_league->open->year : FrozenDate::now()->year
		);
		$this->set(compact('min'));

		// Check form data
		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if ($this->getRequest()->getData('start') > $this->getRequest()->getData('end')) {
				$this->Flash->info(__('End date cannot precede start date.'));
				return;
			}

			$reports_table = TableRegistry::getTableLocator()->get('Reports');
			$report = $reports_table->newEntity([
				'report' => 'people_participation',
				'person_id' => $this->UserCache->currentId(),
				'params' => json_encode($this->getRequest()->getData()),
			]);
			if (!$reports_table->save($report)) {
				$this->Flash->warning(__('Failed to queue your report request.'));
			} else {
				$this->Flash->success(__('Your report request has been queued; the report should be emailed to you in a few minutes.'));
				$this->redirect('/');
			}
		}
	}

	public function retention() {
		$this->Authorization->authorize($this);

		$first_event = TableRegistry::getTableLocator()->get('Events')->find()->order('Events.open')->first();
		$first_league = TableRegistry::getTableLocator()->get('Leagues')->find()->order('Leagues.open')->first();

		$min = min(
			$first_event ? $first_event->open->year : FrozenDate::now()->year,
			$first_league ? $first_league->open->year : FrozenDate::now()->year
		);
		$this->set(compact('min'));

		// Check form data
		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if ($this->getRequest()->getData('start') > $this->getRequest()->getData('end')) {
				$this->Flash->info(__('End date cannot precede start date.'));
				return;
			}

			$reports_table = TableRegistry::getTableLocator()->get('Reports');
			$report = $reports_table->newEntity([
				'report' => 'people_retention',
				'person_id' => $this->UserCache->currentId(),
				'params' => json_encode($this->getRequest()->getData()),
			]);
			if (!$reports_table->save($report)) {
				$this->Flash->warning(__('Failed to queue your report request.'));
			} else {
				$this->Flash->success(__('Your report request has been queued; the report should be emailed to you in a few minutes.'));
				$this->redirect('/');
			}
		}
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$user_id = $this->getRequest()->getQuery('user');
		if ($user_id) {
			try {
				$id = $this->People->field('id', ['People.user_id' => $user_id]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect('/');
			}
		} else {
			$id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		}
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		$person->groups = $this->UserCache->read('Groups', $person->id);
		$person->skills = collection($this->UserCache->read('Skills', $person->id))->filter(function ($skill) { return $skill->enabled; })->toArray();
		$person->teams = $this->UserCache->read('Teams', $person->id);
		$photo = null;

		$identity = $this->Authentication->getIdentity();

		if ($identity && $identity->isLoggedIn()) {
			$person->relatives = $this->UserCache->read('Relatives', $person->id);
			$person->related_to = $this->UserCache->read('RelatedTo', $person->id);
			$person->divisions = $this->UserCache->read('Divisions', $person->id);
			$person->waivers = $this->UserCache->read('WaiversCurrent', $person->id);
			if (Configure::read('feature.registration')) {
				$person->registrations = array_slice($this->UserCache->read('Registrations', $person->id), 0, 4);
				$person->preregistrations = $this->UserCache->read('Preregistrations', $person->id);
				$person->credits = $this->UserCache->read('Credits', $person->id);
			}
			if (Configure::read('scoring.allstars')) {
				$person->allstars = $this->People->GamesAllstars->find()
					->contain([
						'ScoreEntries' => [
							'Games' => [
								'GameSlots',
								'HomeTeam',
								'AwayTeam',
								'Divisions',
							],
						],
					])
					->where([
						'GamesAllstars.person_id' => $id,
						'Divisions.is_open' => true,
					])
					->order(['GameSlots.game_date', 'GameSlots.game_start'])
					->toArray();
			}

			if (Configure::read('feature.photos')) {
				$photo = $this->People->Uploads->find()
					->where([
						'person_id' => $person->id,
						'type_id IS' => null,
						'approved' => true,
					])
					->first();
			}
			if (Configure::read('feature.documents')) {
				$person->uploads = $this->UserCache->read('Documents', $person->id);
			}
			if (Configure::read('feature.annotations')) {
				$visibility = [VISIBILITY_PUBLIC];
				if ($identity->isManagerOf($person)) {
					$visibility[] = VISIBILITY_ADMIN;
				}
				$person->notes = $this->People->Notes->find()
					->contain(['CreatedPerson'])
					->where([
						'person_id' => $person->id,
						'OR' => [
							'Notes.created_person_id' => $this->UserCache->currentId(),
							'Notes.visibility IN' => $visibility,
						],
					])
					->toArray();
			}
			if ($this->Authorization->can($person, 'view_tasks')) {
				$person->tasks = $this->UserCache->read('Tasks', $person->id);
			}
			if (Configure::read('feature.badges')) {
				$badge_obj = $this->moduleRegistry->load('Badge');
				$badge_obj->visibility($identity);

				$person->badges = $this->People->Badges->find()
					->where([
						'BadgesPeople.approved' => true,
						'Badges.visibility IN' => $badge_obj->getVisibility(),
					])
					->matching('People', function (Query $q) use ($id) {
						return $q
							->where(['People.id' => $id]);
					})
					->toArray();

				$badge_obj->prepForDisplay($person);
			}
		}

		$person->updateHidden($identity);
		$photo_url = $this->Authorization->can($person, 'photo') ? $person->photoUrl($photo) : null;
		$this->set(compact('person', 'photo', 'photo_url'));
		$this->set('_serialize', ['person', 'photo_url']);
	}

	public function tooltip() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('person');
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		$identity = $this->Authentication->getIdentity();
		$person->updateHidden($identity);
		$photo = null;

		if ($identity && $identity->isLoggedIn()) {
			if (Configure::read('feature.photos')) {
				$photo = $this->People->Uploads->find()
					->where([
						'person_id' => $person->id,
						'type_id IS' => null,
						'approved' => true,
					])
					->first();
			}
			if (Configure::read('feature.badges')) {
				$badge_obj = $this->moduleRegistry->load('Badge');
				$badge_obj->visibility($identity, BADGE_VISIBILITY_HIGH);

				$person->badges = $this->People->Badges->find()
					->where([
						'BadgesPeople.approved' => true,
						'Badges.visibility IN' => $badge_obj->getVisibility(),
					])
					->matching('People', function (Query $q) use ($id) {
						return $q
							->where(['People.id' => $id]);
					})
					->toArray();

				$badge_obj->prepForDisplay($person);
			}
		}

		if ($this->Authorization->can($person, 'note')) {
			$this->People->loadInto($person, ['Notes' => [
				'queryBuilder' => function (Query $q) {
					return $q->where(['created_person_id' => $this->UserCache->currentId()]);
				},
			]]);
		}

		$this->set(compact('person', 'photo'));
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();

		$this->_loadAddressOptions();
		// We always want to include players, even if they aren't a valid "create account" group.
		$this->set('groups', $this->People->Groups->find('options', ['Groups.require_player' => true])->toArray());
		$this->_loadAffiliateOptions();

		$users_table = $this->loadModel(Configure::read('Security.authPlugin') . Configure::read('Security.authModel'));
		try {
			$contain = $associated = ['Affiliates', 'Skills', 'Groups'];
			if ($users_table->manageUsers) {
				$contain[] = Configure::read('Security.authModel');
				$associated[] = Configure::read('Security.authModel');
			}

			if (Configure::read('feature.photos')) {
				$contain['Uploads'] = [
					'queryBuilder' => function (Query $q) use ($id) {
						return $q->where([
							'person_id' => $id,
							'type_id IS' => null,
						]);
					}
				];
			}

			$person = $this->People->get($id, compact('contain'));

			// Re-assign skill array indices
			$i = 0;
			$skills = [];
			$sports = Configure::read('options.sport');
			foreach ($sports as $sport => $name) {
				$skill = collection($person->skills)->firstMatch(['sport' => $sport]);
				if (!empty($skill)) {
					$skills[$i] = $skill;
				}
				++ $i;
			}
			$person->skills = $skills;
			$person->setDirty('skills', false);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$access = [PROFILE_USER_UPDATE, PROFILE_REGISTRATION];
			// People with incomplete profiles can update any of the fields that
			// normally only admins can edit, so that they can successfully fill
			// out all of the profile.
			if ($this->Authentication->getIdentity()->isManagerOf($person) || !$person->complete) {
				$access[] = PROFILE_ADMIN_UPDATE;
			}

			// TODO: Centralize checking of profile fields
			$columns = $this->People->getSchema()->columns();
			$accessible = [
				'id' => false,
				'user_id' => false,
				'complete' => false,
				'modified' => false,
				'status' => $this->Authentication->getIdentity()->isManagerOf($person),
				'has_dog' => Configure::read('feature.dog_questions') ? true : false,
				'twitter_token' => Configure::read('feature.twitter') ? true : false,
				'twitter_secret' => Configure::read('feature.twitter') ? true : false,
				'show_gravatar' => Configure::read('feature.gravatar') ? true : false,
				'gender_description' => true,
				'roster_designation' => true,
			];

			foreach ($columns as $key => $column) {
				if (!array_key_exists($column, $accessible) && strpos($column, 'alternate_') === false && strpos($column, 'publish_') === false) {
					// Deal with special cases
					if ($column == 'work_ext') {
						$include = Configure::read('profile.work_phone');
					} else {
						$include = Configure::read("profile.$column");
					}
					if (!in_array($include, $access)) {
						$accessible[$column] = false;
					}
				}
			}

			$person = $this->People->patchEntity($person, $this->getRequest()->getData(), [
				'associated' => $associated,
				'accessibleFields' => $accessible,
			]);

			if ($this->People->save($person, ['manage_affiliates' => true, 'manage_groups' => true])) {
				$identity = $this->Authentication->getIdentity();
				if ($identity->isMe($person) || $identity->isRelative($person)) {
					$this->Flash->success(__('Your profile has been saved.'));
				} else {
					$this->Flash->success(__('The person has been saved.'));
				}
				return $this->redirect('/');
			} else {
				$this->Flash->warning(__('The person could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set([
			'person' => $person,
			'user_model' => Configure::read('Security.authModel'),
			'id_field' => $users_table->getPrimaryKey(),
			'user_field' => $users_table->userField,
			'email_field' => $users_table->emailField,
			'manage_users' => $users_table->manageUsers,
			'_serialize' => true,
		]);
	}

	/**
	 * add_account method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add_account() {
		$id = $this->getRequest()->getQuery('person');
		if (!$id) {
			$id = $this->UserCache->read('Person.id');
		}

		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		$this->_loadAddressOptions();
		$this->_loadAffiliateOptions();
		$user_model = Inflector::underscore(Inflector::singularize(Configure::read('Security.authModel')));
		$users_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . Configure::read('Security.authModel'));

		$this->set([
			'user_model' => $user_model,
			'user_field' => $users_table->userField,
			'email_field' => $users_table->emailField,
		]);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$this->setRequest($this->getRequest()->withData("{$user_model}.{$users_table->pwdField}", $this->getRequest()->getData("{$user_model}.new_password")));
			$person = $this->People->patchEntity($person, $this->getRequest()->getData(), [
				'Associated' => [
					Configure::read('Security.authModel') => ['validate' => 'create'],
				],
			]);

			// TODO: Need to fix the new user_id being set in the person record
			if ($this->People->save($person)) {
				$this->Flash->success(__('Your account has been created.'));
				Cache::delete("person/{$id}", 'long_term');
				return $this->redirect('/');
			}

			$this->Flash->warning(__('The account could not be saved. Please correct the errors below and try again.'));

			// Force the various rules checks to run, for better feedback to the user
			$users_table->checkRules($person->user, RulesChecker::CREATE);
			$this->People->checkRules($person, RulesChecker::UPDATE);
		}

		$this->set(compact('person'));
	}

	/**
	 * Deactivate profile method
	 *
	 * @return void|\Cake\Network\Response Redirects
	 */
	public function deactivate() {
		$id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		try {
			$person = $this->People->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		if ($person->status != 'active') {
			$this->Flash->info(__('Only active profiles can be deactivated.'));
			return $this->redirect('/');
		}
		if (!empty($this->UserCache->read('TeamIDs'))) {
			$this->Flash->info(__('You cannot deactivate your account while you are on an active team.'));
			return $this->redirect('/');
		}
		if (!empty($this->UserCache->read('DivisionIDs'))) {
			$this->Flash->info(__('You cannot deactivate your account while you are coordinating an active division.'));
			return $this->redirect('/');
		}

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$person->status = 'inactive';
			if ($this->People->save($person)) {
				$this->Flash->warning(__('Your profile has been deactivated; sorry to see you go. If you ever change your mind, you can just return to the site and reactivate your profile; we\'ll be happy to have you back!'));
			} else {
				$this->Flash->warning(__('Failed to deactivate profile.'));
			}
			return $this->redirect('/');
		}

		$this->set(compact('person'));
	}

	/**
	 * Reactivate profile method
	 *
	 * @return void|\Cake\Network\Response Redirects
	 */
	public function reactivate() {
		$id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		try {
			$person = $this->People->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		if ($person->status != 'inactive') {
			$this->Flash->info(__('Only inactive profiles can be reactivated.'));
			return $this->redirect('/');
		}

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$person->status = 'active';
			if ($this->People->save($person)) {
				$this->Flash->warning(__('Your profile has been reactivated; welcome back!'));
			} else {
				$this->Flash->warning(__('Failed to reactivate profile.'));
			}
			return $this->redirect('/');
		}

		$this->set(compact('person'));
	}

	public function confirm() {
		$this->Authorization->authorize($this);
		$this->getRequest()->allowMethod('ajax');

		$person = $this->Authentication->getIdentity()->getOriginalData()->person;
		$this->People->touch($person);
		if ($this->People->save($person)) {
			$this->Flash->success(__("Profile details have been confirmed, thank you.\nYou will be reminded about this again periodically."));
		} else {
			$this->Flash->info(__("Failed to update profile details.\nYou will likely be prompted about this again very soon.\n\nIf problems persist, contact your system administrator."));
			$this->log($person->getErrors());
			return $this->redirect('/');
		}
	}

	public function note() {
		$note_id = $this->getRequest()->getQuery('note');

		if ($note_id) {
			try {
				$note = $this->People->Notes->get($note_id, [
					'contain' => ['People'],
				]);

				$this->Authorization->authorize($note, 'edit_person');
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid note.'));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid note.'));
				return $this->redirect('/');
			}
			$person = $note->person;
		} else {
			try {
				$person = $this->People->get($this->getRequest()->getQuery('person'));
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect('/');
			}
			$note = $this->People->Notes->newEntity();
			$note->person_id = $person->id;
		}

		$this->Authorization->authorize($person);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$note = $this->People->Notes->patchEntity($note, $this->getRequest()->getData());

			if (empty($note->note)) {
				if ($note->isNew()) {
					$this->Flash->warning(__('You entered no text, so no note was added.'));
					return $this->redirect(['action' => 'view', 'person' => $person->id]);
				} else {
					if ($this->People->Notes->delete($note)) {
						$this->Flash->success(__('The note has been deleted.'));
						return $this->redirect(['action' => 'view', 'person' => $person->id]);
					} else if ($note->getError('delete')) {
						$this->Flash->warning(current($note->getError('delete')));
					} else {
						$this->Flash->warning(__('The note could not be deleted. Please, try again.'));
					}
				}
			} else if ($this->People->Notes->save($note)) {
				$this->Flash->success(__('The note has been saved.'));
				return $this->redirect(['action' => 'view', 'person' => $person->id]);
			} else {
				$this->Flash->warning(__('The note could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('person', 'note'));
	}

	public function delete_note() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$note_id = $this->getRequest()->getQuery('note');

		try {
			$note = $this->People->Notes->get($note_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid note.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid note.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($note, 'delete_person');

		if ($this->People->Notes->delete($note)) {
			$this->Flash->success(__('The note has been deleted.'));
		} else if ($note->getError('delete')) {
			$this->Flash->warning(current($note->getError('delete')));
		} else {
			$this->Flash->warning(__('The note could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'view', 'person' => $note->person_id]);
	}

	public function preferences() {
		$id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		$settings = $this->People->Settings->find()
			->where(['person_id' => $id])
			->toArray();

		$plugin_elements = new \ArrayObject();
		$event = new Event('Plugin.preferences', $this, [$plugin_elements]);
		$this->getEventManager()->dispatch($event);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$settings = $this->People->Settings->patchEntities($settings, $this->getRequest()->getData());

			if ($this->People->Settings->getConnection()->transactional(function () use ($settings) {
				foreach ($settings as $setting) {
					if (!$this->People->Settings->save($setting)) {
						return false;
					}
				}
				return true;
			})) {
				if ($id == $this->UserCache->currentId()) {
					// Reload the configuration right away, so it affects any rendering we do now,
					// and rebuild the menu based on any changes.
					$this->Configuration->loadUser($id);
					$lang = collection($settings)->firstMatch(['name' => 'language']);
					if (!empty($lang)) {
						$lang = $lang->value;

						if (empty($lang)) {
							// Clear any existing cookie, and set to the default language
							$this->setResponse($this->getResponse()->withExpiredCookie(new Cookie('ZuluruLocale')));
							$lang = 'en';
						} else {
							$this->setResponse($this->getResponse()->withCookie(new Cookie('ZuluruLocale', $lang)));
						}
						I18n::setLocale($lang);
						$this->_setLanguage();
						$this->_initMenu();
					}
				}

				$this->Flash->success(__('The preferences have been saved.'));
			} else {
				$this->Flash->warning(__('The preferences could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('id', 'person', 'plugin_elements', 'settings'));
	}

	public function add_relative() {
		$this->Authorization->authorize($this);
		$this->_loadAffiliateOptions();
		$person = $this->People->newEntity();

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$data = $this->getRequest()->getData();
			$data['is_child'] = true;
			$person = $this->People->patchEntity($person, $data, [
				'validate' => 'create',
				'associated' => ['Affiliates', 'Groups', 'Skills'],
			]);

			if ($this->People->getConnection()->transactional(function () use ($person) {
				if (!$this->People->save($person)) {
					$this->Flash->warning(__('The profile could not be saved. Please correct the errors below and try again.'));
					return false;
				}

				$person->_joinData = new PeoplePerson(['approved' => true]);
				$me = $this->People->get($this->UserCache->currentId());
				if (!$this->People->Relatives->link($me, [$person])) {
					$this->Flash->warning(__('The profile could not be saved. Please correct the errors below and try again.'));
					return false;
				}

				$msg = __('The new profile has been saved.');
				if (!Configure::read('feature.auto_approve')) {
					$msg .= ' ' . __('It must be approved by an administrator before you will have full access to the site.');
				}
				$this->Flash->success($msg);

				return true;
			})) {
				if ($data['action'] == 'continue') {
					$person = $this->People->newEntity();
				} else {
					return $this->redirect('/');
				}
			}
		}

		$this->set(compact('person'));
	}

	public function link_relative() {
		$person_id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		try {
			$person = $this->People->get($person_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);
		$this->set(compact('person'));

		$relative_id = $this->getRequest()->getQuery('relative');
		if ($relative_id !== null) {
			if ($relative_id == $person_id) {
				$this->Flash->info(__('You can\'t link yourself as a relative!'));
			} else {
				try {
					$relative = $this->People->get($relative_id);
				} catch (RecordNotFoundException $ex) {
					$this->Flash->info(__('Invalid person.'));
					return $this->redirect('/');
				} catch (InvalidPrimaryKeyException $ex) {
					$this->Flash->info(__('Invalid person.'));
					return $this->redirect('/');
				}

				if (in_array($relative_id, $this->UserCache->read('RelativeIDs', $person_id))) {
					$this->Flash->info(__('{0} is already your relative.', $relative->full_name));
				} else {
					if ($this->People->Relatives->link($person, [$relative])) {
						if (!$this->_sendMail([
							'to' => $relative,
							'replyTo' => $person,
							'subject' => function() { return __('You have been linked as a relative'); },
							'template' => 'relative_link',
							'sendAs' => 'both',
							'viewVars' => [
								'person' => $person,
								'relative' => $relative,
								'code' => $this->_makeHash([
									$person->relatives[0]->_joinData->id,
									$person_id,
									$relative_id,
									$person->relatives[0]->_joinData->created,
								]),
							],
						]))
						{
							$this->Flash->warning(__('Error sending email to {0}.', $relative->full_name));
						}

						$this->Flash->success(__('Linked {0} as relative; you will not have access to their information until they have approved this.', $relative->full_name));
						return $this->redirect('/');
					} else {
						$this->Flash->warning(__('Failed to link {0} as relative.', $relative->full_name));
						return $this->redirect(['action' => 'link_relative', 'person' => $person_id]);
					}
				}
			}
		}

		$this->_handlePersonSearch(['person', 'relative']);
	}

	public function approve_relative() {
		// The profile being granted control, which is not the current user
		$person_id = $this->getRequest()->getQuery('person');
		// The profile to be controlled, which is the current user
		$relative_id = $this->getRequest()->getQuery('relative');
		if ($relative_id === null || $person_id === null) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['action' => 'view', 'person' => $person_id]);
		}

		// The relation being updated is in the current user's Related list
		$relation = collection($this->UserCache->read('RelatedTo', $relative_id))->firstMatch(['_joinData.approved' => false, '_joinData.person_id' => $person_id]);

		// The profile to be controlled, i.e. the relative, is the one that grants permission for this
		$person = $this->UserCache->read('Person', $person_id);
		$relative = $this->UserCache->read('Person', $relative_id);
		$this->Authorization->authorize(new ContextResource($relative, ['relation' => $relation, 'code' => $this->getRequest()->getQuery('code')]));

		$relation->_joinData->approved = true;
		$people_people_table = TableRegistry::getTableLocator()->get('PeoplePeople');
		if (!$people_people_table->save($relation->_joinData)) {
			$this->Flash->warning(__('Failed to approve the relative request.'));
			return $this->redirect(['action' => 'view', 'person' => $person_id]);
		}

		$this->Flash->success(__('Approved the relative request.'));

		if (!$this->_sendMail([
			'to' => $person,
			'replyTo' => $relative,
			'subject' => function() use ($relative) { return __('{0} approved your relative request', $relative->full_name); },
			'template' => 'relative_approve',
			'sendAs' => 'both',
			'viewVars' => compact('person', 'relative'),
		]))
		{
			$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
		}

		return $this->redirect(['action' => 'view', 'person' => $relative_id]);
	}

	public function remove_relative() {
		// The profile that was granted control
		$person_id = $this->getRequest()->getQuery('person');
		// The profile that is controlled
		$relative_id = $this->getRequest()->getQuery('relative');
		if ($relative_id === null || $person_id === null) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['action' => 'view', 'person' => $person_id]);
		}

		// The relation being updated is in the relative's Related list
		$relations = $this->UserCache->read('RelatedTo', $relative_id);
		$relation = collection($relations)->firstMatch(['_joinData.person_id' => $person_id]);

		$person = $this->UserCache->read('Person', $person_id);
		$relative = $this->UserCache->read('Person', $relative_id);

		// If the relative is only a profile, and this is the only remaining relation, don't allow it
		if (empty($relative->user_id) && count($relations) == 1) {
			$this->Flash->info(__('Youth profiles must always have a relative.'));
			return $this->redirect(['action' => 'view', 'person' => $person_id]);
		}

		// Either side of the relation may grant permission for this
		try {
			$this->Authorization->authorize(new ContextResource($relative, ['relation' => $relation, 'code' => $this->getRequest()->getQuery('code')]));
		} catch (ForbiddenException $ex) {
			$this->Authorization->authorize(new ContextResource($person, ['relation' => $relation, 'code' => $this->getRequest()->getQuery('code')]));
		}

		// TODOLATER: Check for unlink return value, if they change it such that it returns success
		// https://github.com/cakephp/cakephp/issues/8196
		$this->People->Relatives->unlink($person, [$relative]);
		$this->Flash->success(__('Removed the relation.'));

		if ($person_id == $this->UserCache->currentId()) {
			if (!$this->_sendMail([
				'to' => $relative,
				'replyTo' => $person,
				'subject' => function() use ($person) { return __('{0} removed your relation', $person->full_name); },
				'template' => 'relative_remove',
				'sendAs' => 'both',
				'viewVars' => compact('person', 'relative'),
			])) {
				$this->Flash->warning(__('Error sending email to {0}.', $relative->full_name));
			}

			return $this->redirect(['action' => 'view']);
		} else if ($relative_id == $this->UserCache->currentId()) {
			if (!$this->_sendMail([
				'to' => $person,
				'replyTo' => $relative,
				'subject' => function() use ($relative) { return __('{0} removed your relation', $relative->full_name); },
				'template' => 'relative_remove',
				'sendAs' => 'both',
				'viewVars' => ['person' => $relative, 'relative' => $person],
			])) {
				$this->Flash->warning(__('Error sending email to {0}.', $relative->full_name));
			}

			return $this->redirect(['action' => 'view']);
		} else {
			// Must have been an admin / manager doing the operation
			if (!$this->_sendMail([
				'to' => [$relative, $person],
				'subject' => function() { return __('An administrator removed your relation'); },
				'template' => 'relative_remove_admin',
				'sendAs' => 'both',
				'viewVars' => compact('person', 'relative'),
			])) {
				$this->Flash->warning(__('Error sending email.'));
			}

			return $this->redirect(['action' => 'view', 'person' => $person_id]);
		}
	}

	public function TODOLATER_authorize_twitter() {
		if (!App::import('Lib', 'tmh_oauth')) {
			$this->Flash->warning(__('Failed to load the {0} library! Contact your system administrator.', 'Twitter OAuth'));
			return $this->redirect(['action' => 'preferences']);
		}

		define('__DIR__', ROOT . DS . APP_DIR . DS . 'libs');
		$tmhOAuth = new tmhOAuth([
			'consumer_key' => Configure::read('twitter.consumer_key'),
			'consumer_secret' => Configure::read('twitter.consumer_secret'),
		]);

		if (!empty($this->getRequest()->getParam('url.oauth_token'))) {
			$response = $this->getRequest()->getSession()->read('Twitter.response');
			$this->getRequest()->getSession()->delete('Twitter.response');
			if ($this->getRequest()->getParam('url.oauth_token') !== $response['oauth_token']) {
				$this->Flash->warning(__('The oauth token you started with doesn\'t match the one you\'ve been redirected with. Do you have multiple tabs open?'));
				return $this->redirect(['action' => 'preferences']);
			}

			if (empty($this->getRequest()->getParam('url.oauth_verifier'))) {
				$this->Flash->warning(__('The oauth verifier is missing so we cannot continue. Did you deny the application access?'));
				return $this->redirect(['action' => 'preferences']);
			}

			// Update with the temporary token and secret
			$tmhOAuth->reconfigure(array_merge($tmhOAuth->config, [
				'token' => $response['oauth_token'],
				'secret' => $response['oauth_token_secret'],
			]));

			$code = $tmhOAuth->user_request([
				'method' => 'POST',
				'url' => $tmhOAuth->url('oauth/access_token', ''),
				'params' => [
					'oauth_verifier' => trim($this->getRequest()->getParam('url.oauth_verifier')),
				]
			]);

			if ($code == 200) {
				$oauth_creds = $tmhOAuth->extract_params($tmhOAuth->response['response']);
				if ($this->Person->updateAll(['twitter_token' => "'{$oauth_creds['oauth_token']}'", 'twitter_secret' => "'{$oauth_creds['oauth_token_secret']}'"], ['Person.id' => $this->UserCache->currentId()])) {
					$this->Flash->success(__('Your Twitter authorization has been completed. You can always revoke this at any time through the preferences page.'));
				} else {
					$this->Flash->warning(__('Twitter authorization was received, but the database failed to update.'));
				}
			} else {
				$this->Flash->warning(__('There was an error communicating with Twitter.') . ' ' . $tmhOAuth->response['response']);
			}
			return $this->redirect(['action' => 'preferences']);
		} else {
			$code = $tmhOAuth->apponly_request([
				'without_bearer' => true,
				'method' => 'POST',
				'url' => $tmhOAuth->url('oauth/request_token', ''),
				'params' => [
					'oauth_callback' => Router::url(Router::normalize($this->getRequest()->getRequestTarget()), true),
				],
			]);

			if ($code != 200) {
				$this->Flash->warning(__('There was an error communicating with Twitter.') . ' ' . $tmhOAuth->response['response']);
				return $this->redirect(['action' => 'preferences']);
			}

			// store the params into the session so they are there when we come back after the redirect
			$response = $tmhOAuth->extract_params($tmhOAuth->response['response']);

			// check the callback has been confirmed
			if ($response['oauth_callback_confirmed'] !== 'true') {
				$this->Flash->warning(__('The callback was not confirmed by Twitter so we cannot continue.') . ' ' . $tmhOAuth->response['response']);
				return $this->redirect(['action' => 'preferences']);
			} else {
				$this->getRequest()->getSession()->write('Twitter.response', $response);
				return $this->redirect($tmhOAuth->url('oauth/authorize', '') . "?oauth_token={$response['oauth_token']}");
			}
		}
	}

	public function TODOLATER_revoke_twitter() {
		if ($this->Person->updateAll(['twitter_token' => null, 'twitter_secret' => null], ['Person.id' => $this->UserCache->currentId()])) {
			$this->Flash->success(__('Your Twitter authorization has been revoked. You can always re-authorize at any time through the preferences page.'));
		} else {
			$this->Flash->warning(__('Failed to revoke your Twitter authorization.'));
		}
		return $this->redirect(['action' => 'preferences']);
	}

	public function photo() {
		$photo = $this->People->Uploads->find()
			->where([
				'person_id' => $this->getRequest()->getQuery('person'),
				'type_id IS' => null,
			])
			->first();
		if (!empty($photo)) {
			$this->Authorization->authorize($photo);
			return $this->getResponse()->withFile(Configure::read('App.paths.uploads') . DS . $photo->filename);
		}
	}

	public function photo_upload() {
		// We don't want the Upload behavior here: we're getting an image from the
		// cropping plugin in the browser, not from the standard form upload method.
		if ($this->People->Uploads->hasBehavior('Upload')) {
			$this->People->Uploads->removeBehavior('Upload');
		}

		$temp_dir = Configure::read('App.paths.files') . DS . 'temp';
		if (!is_dir($temp_dir) || !is_writable($temp_dir)) {
			if ($this->Authentication->getIdentity()->isManager()) {
				$this->Flash->warning(__('Your temp folder {0} does not exist or is not writable.', $temp_dir));
			} else {
				$this->Flash->warning(__('This system does not appear to be properly configured for photo uploads. Please contact your administrator to have them correct this.'));
			}
			return $this->redirect('/');
		}
		$file_dir = Configure::read('App.paths.uploads');
		if (!is_dir($file_dir) || !is_writable($file_dir)) {
			if ($this->Authentication->getIdentity()->isManager()) {
				$this->Flash->warning(__('Your uploads folder {0} does not exist or is not writable.', $file_dir));
			} else {
				$this->Flash->warning(__('This system does not appear to be properly configured for photo uploads. Please contact your administrator to have them correct this.'));
			}
			return $this->redirect('/');
		}

		$person = $this->UserCache->read('Person');
		$upload = $this->People->Uploads->find()
			->where([
				'person_id' => $person->id,
				'type_id IS' => null,
			])
			->first();
		if ($upload) {
			$old_filename = $upload->filename;
		} else {
			$upload = $this->People->Uploads->newEntity();
		}

		$this->set(compact('person', 'upload'));

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			// Extract base64 file for standard data
			$fileBin = file_get_contents($this->getRequest()->getData('cropped'));
			$mimeType = mime_content_type($this->getRequest()->getData('cropped'));

			// Check allowed mime type
			if (substr($mimeType, 0, 6) == 'image/') {
				[, $ext] = explode('/', $mimeType);
				$filename = $person->id . '.' . strtolower($ext);
				file_put_contents(Configure::read('App.paths.uploads') . DS . $filename, $fileBin);

				// Are approvals required?
				$approved = (Configure::read('feature.approve_photos') ? false : true);
				$upload = $this->People->Uploads->patchEntity($upload, array_merge($this->getRequest()->getData(), compact('filename', 'approved')));
				if ($this->People->Uploads->save($upload)) {
					if (isset($old_filename) && $old_filename != $filename) {
						unlink(Configure::read('App.paths.uploads') . DS . $old_filename);
					}
					if (!$approved) {
						$this->Flash->success(__('Your photo has been saved, but will not be visible until approved.'));
					} else {
						$this->Flash->success(__('Your photo has been saved.'));
					}
					return $this->redirect(['action' => 'view']);
				} else {
					$this->Flash->warning(__('Failed to save your document.'));
				}
			} else {
				$this->Flash->warning(__('The file you tried to upload is not of a recognized type. Please try again.'));
			}
		}
	}

	public function approve_photos() {
		$this->Authorization->authorize($this);

		if (!Configure::read('feature.approve_photos')) {
			$this->Flash->info(__('Approval of photos is not required on this site.'));
			return $this->redirect('/');
		}

		$photos = $this->People->Uploads->find()
			->contain(['People'])
			->where([
				'approved' => false,
				'type_id IS' => null,
			]);
		if ($photos->isEmpty()) {
			$this->Flash->info(__('There are no photos to approve.'));
			return $this->redirect('/');
		}
		$this->set(compact('photos'));
	}

	public function approve_photo() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('person');
		try {
			$photo = $this->People->Uploads->find()
				->contain(['People'])
				->where([
					'People.id' => $id,
					'Uploads.type_id IS' => null,
				])
				->firstOrFail();
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid photo.'));
			return $this->redirect(['action' => 'approve_photos']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid photo.'));
			return $this->redirect(['action' => 'approve_photos']);
		}

		$this->Authorization->authorize($photo->person);

		$photo->approved = true;

		if (!$this->People->Uploads->save($photo)) {
			$this->Flash->warning(__('Failed to approve the photo.'));
			return $this->redirect(['action' => 'approve_photos']);
		} else {
			if (!$this->_sendMail([
				'to' => $photo->person,
				'subject' => function() { return __('{0} Notification of Photo Approval', Configure::read('organization.name')); },
				'template' => 'photo_approved',
				'sendAs' => 'both',
				'viewVars' => ['person' => $photo->person],
			]))
			{
				$this->Flash->warning(__('Error sending email to {0}.', $photo->person->full_name));
			}
		}
	}

	public function delete_photo() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('person');
		try {
			$photo = $this->People->Uploads->find()
				->contain(['People'])
				->where([
					'People.id' => $id,
					'Uploads.type_id IS' => null,
				])
				->firstOrFail();
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid photo.'));
			return $this->redirect(['action' => 'approve_photos']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid photo.'));
			return $this->redirect(['action' => 'approve_photos']);
		}

		$this->Authorization->authorize($photo->person);

		if ($this->People->Uploads->delete($photo)) {
			if (!$this->_sendMail([
				'to' => $photo->person,
				'subject' => function() { return __('{0} Notification of Photo Deletion', Configure::read('organization.name')); },
				'template' => 'photo_deleted',
				'sendAs' => 'both',
				'viewVars' => ['person' => $photo->person],
			])) {
				$this->Flash->warning(__('Error sending email to {0}.', $photo->person->full_name));
			}
		}
	}

	public function document() {
		try {
			$document = $this->People->Uploads->get($this->getRequest()->getQuery('document'), [
				'contain' => ['People', 'UploadTypes']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($document);

		foreach (Configure::read('new_mime_types') as $type => $mime) {
			$this->getResponse()->setTypeMap($type, $mime);
		}
		$f = new File($document->filename);
		return $this->getResponse()->withFile(Configure::read('App.paths.uploads') . DS . $document->filename, [
			'name' => $document->filename,
			'download' => !in_array($f->ext(), Configure::read('no_download_extensions')),
		]);
	}

	public function document_upload() {
		$file_dir = Configure::read('App.paths.uploads');
		if (!is_dir($file_dir) || !is_writable($file_dir)) {
			if ($this->Authentication->getIdentity()->isManager()) {
				$this->Flash->warning(__('Your uploads folder {0} does not exist or is not writable.', $file_dir));
			} else {
				$this->Flash->warning(__('This system does not appear to be properly configured for document uploads. Please contact your administrator to have them correct this.'));
			}
			return $this->redirect('/');
		}

		$id = $this->getRequest()->getQuery('person');
		if ($id) {
			try {
				$person = $this->People->get($id);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect('/');
			}
		} else {
			$person = $this->UserCache->read('Person');
		}

		$this->Authorization->authorize($person);
		$affiliates = $this->Authentication->applicableAffiliateIDs();
		$types = $this->People->Uploads->UploadTypes->find()
			->contain(['Affiliates'])
			->where(['UploadTypes.affiliate_id IN' => $affiliates])
			->order(['Affiliates.name', 'UploadTypes.name'])
			->combine('id', 'name', 'affiliate.name')
			->toArray();
		if (count($affiliates) == 1) {
			$types = current($types);
		}

		$upload = $this->People->Uploads->newEntity();

		if ($this->getRequest()->is('post')) {
			// Add some configuration that the upload behaviour will use
			$filename = $person->id . '_' . md5(mt_rand());
			$this->People->Uploads->behaviors()->get('Upload')->setConfig([
				'filename' => [
					// Callbacks for adjusting the file name before saving. Both are required. :-(
					'nameCallback' => function (Table $table, Entity $entity, $data, $field, $settings) use ($filename) {
						$f = new File($data['name']);
						return $filename . '.' . strtolower($f->ext());
					},
					'transformer' => function (Table $table, Entity $entity, $data, $field, $settings) use ($filename) {
						$f = new File($data['name']);
						return [$data['tmp_name'] => $filename . '.' . strtolower($f->ext())];
					},
				],
			]);

			$upload = $this->People->Uploads->patchEntity($upload, $this->getRequest()->getData());

			if ($this->People->Uploads->save($upload)) {
				$this->Flash->success(__('Document saved, you will receive an email when it has been approved.'));
				return $this->redirect(['action' => 'view', 'person' => $person->id]);
			} else {
				$this->Flash->warning(__('Failed to save your document.'));
			}
		} else {
			$upload->type_id = $this->getRequest()->getQuery('type');
		}

		$this->set(compact('person', 'types', 'upload'));
	}

	public function approve_documents() {
		$this->Authorization->authorize($this);
		$documents = $this->People->Uploads->find()
				->contain(['People', 'UploadTypes'])
				->where([
					'approved' => false,
					'type_id IS NOT' => null,
				])
				->order(['People.last_name', 'People.first_name', 'UploadTypes.id']);
		if ($documents->count() == 0) {
			$this->Flash->info(__('There are no documents to approve.'));
			return $this->redirect('/');
		}
		$this->set(compact('documents'));
	}

	public function approve_document() {
		try {
			$document = $this->People->Uploads->get($this->getRequest()->getQuery('document'), [
				'contain' => ['People' => [Configure::read('Security.authModel')], 'UploadTypes']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($document);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$document = $this->People->Uploads->patchEntity($document, $this->getRequest()->getData());
			if ($this->People->Uploads->save($document)) {
				$this->Flash->success(__('Approved document.'));

				if (!$this->_sendMail([
					'to' => $document->person,
					'subject' => function() { return __('{0} Notification of Document Approval', Configure::read('organization.name')); },
					'template' => 'document_approved',
					'sendAs' => 'both',
					'viewVars' => array_merge([
						'person' => $document->person,
					], compact('document')),
				]))
				{
					$this->Flash->warning(__('Error sending email to {0}.', $document->person->full_name));
				}
				return $this->redirect(['action' => 'approve_documents']);
			} else {
				$this->Flash->warning(__('Failed to approve the document.'));
			}
		}

		$this->set(compact('document'));
	}

	public function edit_document() {
		try {
			$document = $this->People->Uploads->get($this->getRequest()->getQuery('document'), [
				'contain' => ['People' => [Configure::read('Security.authModel')], 'UploadTypes']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($document);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$document = $this->People->Uploads->patchEntity($document, $this->getRequest()->getData());
			if ($this->People->Uploads->save($document)) {
				$this->Flash->success(__('Updated document.'));

				if (!$this->_sendMail([
					'to' => $document->person,
					'subject' => function() { return __('{0} Notification of Document Update', Configure::read('organization.name')); },
					'template' => 'document_updated',
					'sendAs' => 'both',
					'viewVars' => array_merge([
						'person' => $document->person,
					], compact('document')),
				]))
				{
					$this->Flash->warning(__('Error sending email to {0}.', $document->person->full_name));
				}
				return $this->redirect(['action' => 'view', 'person' => $document->person->id]);
			} else {
				$this->Flash->warning(__('Failed to update the document.'));
			}
		}
		$this->set(compact('document'));
		$this->render('approve_document');
	}

	public function delete_document() {
		$this->getRequest()->allowMethod('ajax');

		try {
			$document = $this->People->Uploads->get($this->getRequest()->getQuery('document'), [
				'contain' => ['People' => [Configure::read('Security.authModel')], 'UploadTypes']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($document);

		if (!$this->People->Uploads->delete($document)) {
			$this->Flash->warning(__('The document could not be deleted. Please, try again.'));
			return $this->redirect(['action' => 'approve_documents']);
		}

		if ($document->person_id != $this->UserCache->currentId()) {
			if (!$this->_sendMail([
				'to' => $document->person,
				'subject' => function() { return __('{0} Notification of Document Deletion', Configure::read('organization.name')); },
				'template' => 'document_deleted',
				'sendAs' => 'both',
				'viewVars' => array_merge([
					'person' => $document->person,
				], $this->getRequest()->getData(), compact('document')),
			]))
			{
				$this->Flash->warning(__('Error sending email to {0}.', $document->person->full_name));
			}
		}
	}

	public function nominate() {
		$this->Authorization->authorize($this);

		if ($this->getRequest()->is('post')) {
			if (empty($this->getRequest()->getData('badge'))) {
				$this->Flash->warning(__('You must select a badge!'));
			} else {
				return $this->redirect(['action' => 'nominate_badge', 'badge' => $this->getRequest()->getData('badge')]);
			}
		}

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$conditions = [
			'Badges.active' => true,
			'Badges.affiliate_id IN' => $affiliates,
		];
		if ($this->Authentication->getIdentity()->isManager()) {
			$conditions['Badges.category IN'] = ['nominated', 'assigned'];
		} else {
			$conditions['Badges.category'] = 'nominated';
			$conditions['Badges.visibility !='] = BADGE_VISIBILITY_ADMIN;
		}

		$badges = $this->People->Badges->find()
			->contain(['Affiliates'])
			->where($conditions)
			->order(['Affiliates.name', 'Badges.category', 'Badges.name'])
			->toArray();

		if (count($affiliates) > 1) {
			$names = [];
			foreach ($badges as $badge) {
				$names[$badge->affiliate->name][$badge->id] = $badge->name;
			}
			$badges = $names;
		} else {
			$badges = collection($badges)->combine('id', 'name')->toArray();
		}

		if (empty($badges)) {
			$this->Flash->warning(__('Sorry, there are no user-nominated badges currently available.'));
			return $this->redirect(['controller' => 'Badges']);
		}

		$this->set(compact('badges'));
	}

	public function nominate_badge() {
		$badge_id = $this->getRequest()->getQuery('badge');
		if (!$badge_id) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}

		try {
			$badge = $this->People->Badges->get($badge_id, [
				'contain' => ['Affiliates'],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}

		$this->Authorization->authorize($badge);
		$this->Configuration->loadAffiliate($badge->affiliate_id);

		$this->set(compact('badge'));

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->_handlePersonSearch(['badge']);
	}

	public function nominate_badge_reason() {
		$badge_id = $this->getRequest()->getQuery('badge');
		if (!$badge_id) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}
		$person_id = $this->getRequest()->getQuery('person');
		if (!$person_id) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}

		try {
			$badge = $this->People->Badges->get($badge_id, [
				'contain' => [
					'People' => [
						'queryBuilder' => function (Query $q) use ($person_id) {
							return $q->where(['People.id' => $person_id]);
						},
					],
					'Affiliates',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}

		$this->Authorization->authorize($badge, 'nominate_badge');

		if (!empty($badge->people)) {
			if ($badge->active) {
				// TODO: Allow multiple copies of the badge?
				$this->Flash->info(__('This person already has this badge.'));
				return $this->redirect(['action' => 'nominate_badge', 'badge' => $badge_id]);
			} else {
				$this->Flash->info(__('This person has already been nominated for this badge.'));
				return $this->redirect(['action' => 'nominate_badge', 'badge' => $badge_id]);
			}
		}

		try {
			$person = $this->People->get($person_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}

		if (Configure::read('feature.affiliates') && !in_array($badge->affiliate_id, $this->UserCache->read('AffiliateIDs', $person_id))) {
			$this->Flash->info(__('That person is not a member of this affiliate.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}

		$this->Configuration->loadAffiliate($badge->affiliate_id);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('badge', 'person', 'affiliates'));

		if ($this->getRequest()->is('post')) {
			$data = [
				'reason' => $this->getRequest()->getData('reason'),
			];
			if ($badge->category == 'assigned') {
				$data['approved'] = true;
				$data['approved_by_id'] = $this->UserCache->currentId();
			} else {
				$data['nominated_by_id'] = $this->UserCache->currentId();
				$nominator = $this->UserCache->read('Person');
			}

			$badge->_joinData = new BadgesPerson($data);
			if ($this->People->Badges->link($person, [$badge])) {
				if ($badge->category == 'assigned') {
					$this->Flash->success(__('The badge has been assigned.'));

					if ($badge->visibility != BADGE_VISIBILITY_ADMIN) {
						// Inform the recipient
						if (!$this->_sendMail([
							'to' => $person,
							'subject' => function() { return __('{0} New Badge Awarded', Configure::read('organization.name')); },
							'template' => 'badge_awarded',
							'sendAs' => 'both',
							'viewVars' => array_merge(['link' => $badge->_joinData], compact('person', 'badge', 'nominator')),
						]))
						{
							$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
						}
					}
				} else {
					$this->Flash->success(__('Your nomination has been saved.'));
				}
				return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
			} else {
				if ($badge->category == 'assigned') {
					$this->Flash->warning(__('Your badge assignment could not be saved. Please, try again.'));
				} else {
					$this->Flash->warning(__('Your nomination could not be saved. Please, try again.'));
				}
			}
		}
	}

	public function approve_badges() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$badges = $this->People->Badges->find()
			->contain(['Affiliates'])
			->matching('People')
			->where([
				'BadgesPeople.approved' => false,
				'Badges.affiliate_id IN' => $affiliates,
			]);
		if ($badges->isEmpty()) {
			$this->Flash->info(__('There are no badges to approve.'));
			return $this->redirect('/');
		}
		$this->set(compact('badges'));
	}

	public function approve_badge() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('badge');
		try {
			$badges_table = TableRegistry::getTableLocator()->get('BadgesPeople');
			$link = $badges_table->get($id, [
				'contain' => [
					'Badges',
					'People' => [Configure::read('Security.authModel')],
					'NominatedBy' => [Configure::read('Security.authModel')],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'approve_badges']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'approve_badges']);
		}

		$this->Authorization->authorize($link->badge);

		$link = $badges_table->patchEntity($link, [
			'approved' => true,
			'approved_by_id' => $this->UserCache->currentId(),
		]);

		if (!$badges_table->save($link)) {
			$this->Flash->warning(__('Failed to approve the badge.'));
			return $this->redirect(['action' => 'approve_badges']);
		} else {
			if ($link->badge->visibility != BADGE_VISIBILITY_ADMIN) {
				// Inform the nominator
				if (!$this->_sendMail([
					'to' => $link->nominated_by,
					'subject' => function() { return __('{0} Notification of Badge Approval', Configure::read('organization.name')); },
					'template' => 'badge_nomination_approved',
					'sendAs' => 'both',
					'viewVars' => ['link' => $link, 'person' => $link->person, 'badge' => $link->badge, 'nominator' => $link->nominated_by],
				]))
				{
					$this->Flash->warning(__('Error sending email to {0}.', $link->nominated_by->full_name));
				}

				// Inform the recipient
				if (!$this->_sendMail([
					'to' => $link->person,
					'subject' => function() { return __('{0} New Badge Awarded', Configure::read('organization.name')); },
					'template' => 'badge_awarded',
					'sendAs' => 'both',
					'viewVars' => ['link' => $link, 'person' => $link->person, 'badge' => $link->badge, 'nominator' => $link->nominated_by],
				]))
				{
					$this->Flash->warning(__('Error sending email to {0}.', $link->person->full_name));
				}
			}
		}
	}

	public function delete_badge() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('badge');
		try {
			$badges_table = TableRegistry::getTableLocator()->get('BadgesPeople');
			$link = $badges_table->get($id, [
				'contain' => [
					'Badges',
					'People' => [Configure::read('Security.authModel')],
					'NominatedBy' => [Configure::read('Security.authModel')],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'approve_badges']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['action' => 'approve_badges']);
		}

		$this->Authorization->authorize($link->badge, 'approve_badge');

		if (!$badges_table->delete($link)) {
			$this->Flash->warning(__('The badge could not be deleted. Please, try again.'));
			return $this->redirect(['action' => 'approve_badges']);
		} else {
			if ($link->badge->visibility != BADGE_VISIBILITY_ADMIN) {
				if ($link->approved) {
					// Inform the badge holder
					if (!$this->_sendMail([
						'to' => $link->person,
						'subject' => function() { return __('{0} Notification of Badge Deletion', Configure::read('organization.name')); },
						'template' => 'badge_deleted',
						'sendAs' => 'both',
						'viewVars' => ['person' => $link->person, 'badge' => $link->badge, 'comment' => $this->getRequest()->getData('comment')],
					])
					) {
						$this->Flash->warning(__('Error sending email to {0}.', $link->person->full_name));
					}
				} else {
					// Inform the nominator
					if (!$this->_sendMail([
						'to' => $link->nominated_by,
						'subject' => function() { return __('{0} Notification of Badge Rejection', Configure::read('organization.name')); },
						'template' => 'badge_nomination_rejected',
						'sendAs' => 'both',
						'viewVars' => ['person' => $link->person, 'badge' => $link->badge, 'nominator' => $link->nominated_by, 'comment' => $this->getRequest()->getData('comment')],
					])
					) {
						$this->Flash->warning(__('Error sending email to {0}.', $link->nominated_by->full_name));
					}
				}
			}
		}
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('person');
		try {
			$person = $this->People->get($id, [
				'contain' => [Configure::read('Security.authModel')]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		$dependencies = $this->People->dependencies($id, ['Affiliates', 'Groups', 'Relatives', 'Related', 'Skills', 'Settings', 'Subscriptions', 'CreatedNotes']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this person, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect('/');
		}

		if ($this->People->delete($person)) {
			$this->Flash->success(__('The person has been deleted.'));
		} else if ($person->getError('delete')) {
			$this->Flash->warning(current($person->getError('delete')));
		} else {
			$this->Flash->warning(__('The person could not be deleted. Please, try again.'));
		}

		return $this->redirect('/');
	}

	public function splash() {
		$this->Authorization->authorize($this);

		$relatives = $affiliates = $unmanaged = [];

		// TODO: These references to locked should use authorization instead
		if ($this->UserCache->read('Person.status') != 'locked') {
			$relatives = collection($this->UserCache->read('Relatives'))->match(['_joinData.approved' => 1])->toList();
		}

		if (Configure::read('feature.affiliates') && $this->UserCache->read('Person.status') != 'locked') {
			$affiliates_table = TableRegistry::getTableLocator()->get('Affiliates');
			$affiliates = $affiliates_table->find('active')->indexBy('id')->toArray();
			if ($this->Authorization->can(current($affiliates), 'add_manager')) {
				$unmanaged = $affiliates_table->find('active')
					->contain([
						'People' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['AffiliatesPeople.position' => 'manager']);
							},
						],
					])
					->toArray();
				foreach ($unmanaged as $key => $affiliate) {
					if (!empty($affiliate->people)) {
						unset($unmanaged[$key]);
					} else {
						unset($unmanaged[$key]->people);
					}
				}
			}
		}

		$applicable_affiliates = $this->Authentication->applicableAffiliateIDs();

		$this->set(compact('relatives', 'affiliates', 'unmanaged', 'applicable_affiliates'));
	}

	public function schedule() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('person');
		if (!$id) {
			$id = $this->UserCache->currentId();
		}
		$person = $this->UserCache->read('Person', $id);
		$this->Authorization->authorize($person);

		$teams = $this->UserCache->read('Teams', $id);
		$team_ids = $this->UserCache->read('TeamIDs', $id);
		$items = $this->_schedule([$id], $team_ids);

		$this->set(compact('id', 'items', 'teams', 'team_ids'));
	}

	private function _schedule($people, $team_ids) {
		if (!empty($team_ids)) {
			$limit = max(4, ceil(count(array_unique($team_ids)) * 1.5));
			$games_table = TableRegistry::getTableLocator()->get('Games');
			$past = $games_table->find('schedule', ['teams' => $team_ids])
				->find('withAttendance', compact('people'))
				->contain([
					'Divisions' => ['Days', 'Leagues'],
					'ScoreEntries' => [
						'queryBuilder' => function (Query $q) use ($team_ids) {
							return $q->where(['ScoreEntries.team_id IN' => $team_ids]);
						}
					],
				])
				->where([
					'Games.published' => true,
					'GameSlots.game_date <' => FrozenDate::now(),
					'GameSlots.game_date >=' => FrozenDate::now()->subWeeks(2),
				])
				->order(['GameSlots.game_date DESC', 'GameSlots.game_start DESC'])
				->limit($limit)
				->toArray();
			$past = array_reverse($past);

			$future = $games_table->find('schedule', ['teams' => $team_ids])
				->find('withAttendance', compact('people'))
				->contain([
					'Divisions' => ['Days', 'Leagues'],
				])
				->where([
					'Games.published' => true,
					'GameSlots.game_date >=' => FrozenDate::now(),
					'GameSlots.game_date <' => FrozenDate::now()->addWeeks(2),
				])
				->order(['GameSlots.game_date', 'GameSlots.game_start'])
				->limit($limit)
				->toArray();

			// Check if we need to update attendance records for any upcoming games
			$reread = false;
			foreach (array_merge($past, $future) as $game) {
				// TODO: This test won't actually be sufficient in the case where a relative has their attendance record,
				// but this person was just added to the team. Ideal solution will eliminate all this crud and the reread
				// block below too. And all the same for the team events further down.
				if (empty($game->attendances)) {
					if (!empty($game->home_team->id) && $game->home_team->track_attendance && in_array($game->home_team->id, $team_ids)) {
						$games_table->readAttendance($game->home_team->id, collection($game->division->days)->extract('id')->toArray(), $game->id);
						$reread = true;
					}
					if (!empty($game->away_team->id) && $game->away_team->track_attendance && in_array($game->away_team->id, $team_ids)) {
						$games_table->readAttendance($game->away_team->id, collection($game->division->days)->extract('id')->toArray(), $game->id);
						$reread = true;
					}
				}
			}

			if ($reread) {
				$past = $games_table->find('schedule', ['teams' => $team_ids])
					->find('withAttendance', compact('people'))
					->contain([
						'Divisions' => ['Days', 'Leagues'],
					])
					->where([
						'Games.published' => true,
						'GameSlots.game_date <' => FrozenDate::now(),
						'GameSlots.game_date >=' => FrozenDate::now()->subWeeks(2),
					])
					->order(['GameSlots.game_date DESC', 'GameSlots.game_start DESC'])
					->limit($limit)
					->toArray();
				$past = array_reverse($past);

				$future = $games_table->find('schedule', ['teams' => $team_ids])
					->find('withAttendance', compact('people'))
					->contain([
						'Divisions' => ['Days', 'Leagues'],
					])
					->where([
						'Games.published' => true,
						'GameSlots.game_date >=' => FrozenDate::now(),
						'GameSlots.game_date <' => FrozenDate::now()->addWeeks(2),
					])
					->order(['GameSlots.game_date', 'GameSlots.game_start'])
					->limit($limit)
					->toArray();
			}

			$items = array_merge($past, $future);

			$events_table = TableRegistry::getTableLocator()->get('TeamEvents');
			$past = $events_table->find('schedule', ['teams' => $team_ids])
				->where([
					'TeamEvents.date <' => FrozenDate::now(),
					'TeamEvents.date >=' => FrozenDate::now()->subWeeks(2),
				])
				->order(['TeamEvents.date DESC', 'TeamEvents.start DESC'])
				->limit($limit)
				->toArray();
			$past = array_reverse($past);

			$future = $events_table->find('schedule', ['teams' => $team_ids])
				->find('withAttendance', compact('people'))
				->where([
					'TeamEvents.date >=' => FrozenDate::now(),
					'TeamEvents.date <' => FrozenDate::now()->addWeeks(2),
				])
				->order(['TeamEvents.date', 'TeamEvents.start'])
				->limit($limit)
				->toArray();

			// Check if we need to update attendance records for any upcoming events
			$reread = false;
			foreach ($future as $team_event) {
				if ($team_event->team->track_attendance && empty($team_event->attendances)) {
					$events_table->readAttendance($team_event->team_id, $team_event->id);
					$reread = true;
				}
			}

			if ($reread) {
				$future = $events_table->find('schedule', ['teams' => $team_ids])
					->find('withAttendance', compact('people'))
					->where([
						'TeamEvents.date >=' => FrozenDate::now(),
						'TeamEvents.date <' => FrozenDate::now()->addWeeks(2),
					])
					->order(['TeamEvents.date', 'TeamEvents.start'])
					->limit($limit)
					->toArray();
			}

			$items = array_merge($items, $past, $future);
		} else {
			$items = [];
		}

		if (Configure::read('feature.tasks')) {
			foreach ($people as $id) {
				$tasks = $this->UserCache->read('Tasks', $id);
				if (!empty($tasks)) {
					$items = array_merge($items, $tasks);
				}
			}
		}

		usort($items, [GamesTable::class, 'compareDateAndField']);
		return $items;
	}

	public function consolidated_schedule() {
		$this->Authorization->authorize($this);
		$this->getRequest()->allowMethod('ajax');

		// We need to read attendance for all relatives, as shared games might not
		// be on everyone's list, but we still want to accurately show attendance
		if ($this->UserCache->read('Person.status') !== 'locked') {
			$people = $this->UserCache->read('RelativeIDs');
			$relatives = collection($this->UserCache->read('Relatives'))->match(['_joinData.approved' => 1])->toList();
		} else {
			$people = $relatives = [];
		}

		$id = $this->UserCache->currentId();
		array_unshift($people, $id);

		$teams = [];
		foreach ($people as $person_id) {
			$teams = array_merge($teams, $this->UserCache->read('Teams', $person_id));
		}
		$teams = collection($teams)->indexBy('id')->toArray();

		$items = $this->_schedule($people, array_keys($teams));
		$this->set(compact('id', 'items', 'relatives', 'teams'));
	}

	public function act_as() {
		$act_as = $this->getRequest()->getQuery('person');
		if ($act_as) {
			$target = $this->UserCache->read('Person', $act_as);
			if (!$target) {
				$this->Flash->info(__('Invalid person.'));
			} else {
				$this->Authorization->authorize($target);

				$user = $this->Authentication->getIdentity()->actAs($this->getRequest(), $this->getResponse(), $target);
				if ($user->real_person) {
					$this->Flash->success(__('You are now acting as {0}.', $target->full_name));
				} else {
					$this->Flash->success(__('You are now acting as yourself.'));
				}
			}
			return $this->redirect('/');
		}

		$this->Authorization->authorize($this, 'act_as_select');

		// Relatives come first...
		$relatives = $this->UserCache->read('Relatives');
		foreach($relatives as $relative) {
			$opts[$relative['id']] = $relative->full_name;
		}
		// ...then the real user. No harm if they're already in the list; this really just adds admins at the end, if applicable.
		if ($this->UserCache->realId() != $this->UserCache->currentId()) {
			$opts[$this->UserCache->realId()] = $this->UserCache->read('Person.full_name', $this->UserCache->realId());
		}
		if (empty($opts)) {
			$this->Flash->warning(__('There is nobody else you can act as.'));
			return $this->redirect('/');
		}
		$this->set(compact('opts'));
	}

	public function search() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliates();
		$this->set(compact('affiliates'));
		$this->_handlePersonSearch();
	}

	public function email_search() {
		$this->Authorization->authorize($this);
		[$params, $url] = $this->_extractSearchParams();
		$this->_handleEmailSearch($params, $url);
	}

	public function rule_search() {
		$this->Authorization->authorize($this);
		[$params, $url] = $this->_extractSearchParams();
		if (!$this->_handleRuleSearch($params, $url)) {
			return $this->redirect(['action' => 'rule_search']);
		}
	}

	public function league_search() {
		$this->Authorization->authorize($this);
		[$params, $url] = $this->_extractSearchParams();
		unset($url['league_id']);
		unset($url['include_subs']);
		if (array_key_exists('league_id', $params)) {
			if (!empty($params['include_subs'])) {
				$subs = ',include_subs';
			} else {
				$subs = '';
			}
			$params['rule'] = "COMPARE(LEAGUE_TEAM_COUNT({$params['league_id']}$subs) > '0')";
		}

		// Get the list of possible leagues to look at
		$affiliates = $this->Authentication->applicableAffiliates();
		$affiliate_leagues = $this->People->Affiliates->find()
			->contain(['Leagues' => [
				'queryBuilder' => function (Query $q) {
					return $q->order(['Leagues.open' => 'DESC']);
				},
			]])
			->where([
				'Affiliates.id IN' => array_keys($affiliates),
			])
			->order(['Affiliates.name']);
		$leagues = [];
		foreach ($affiliate_leagues as $affiliate) {
			if (!empty($affiliate->leagues)) {
				$leagues[$affiliate->name] = collection($affiliate->leagues)->combine('id', 'full_name')->toArray();
			}
		}
		if (count($leagues) == 1) {
			$leagues = reset($leagues);
		}
		$this->set(compact('leagues'));

		$this->_handleRuleSearch($params, $url);
	}

	public function inactive_search() {
		$this->Authorization->authorize($this);
		[$params, $url] = $this->_extractSearchParams();
		$params['affiliate_id'] = array_keys($this->Authentication->applicableAffiliates());
		if (!empty($params) || !Configure::read('feature.affiliates')) {
			$params['rule'] = "NOT(COMPARE(TEAM_COUNT('today') > '0'))";
		}
		if (!Configure::read('feature.affiliates')) {
			$params['affiliate_id'] = 1;
		}

		$this->_handleRuleSearch($params, $url);
	}

	protected function _handleEmailSearch($params, $url) {
		$affiliates = $this->Authentication->applicableAffiliates();
		$this->set(compact('url', 'affiliates'));

		if (array_key_exists('email', $params)) {
			$min = $this->Authentication->getIdentity()->isManager() ? 1 : 2;
			if (strlen($params['email']) < $min) {
				$this->set('search_error', __('The search terms used are too general. Please be more specific.'));
			} else {
				$value = $params['email'];
				$op = '';
				if (strpos($value, '*') !== false) {
					$op = ' LIKE';
					$value = strtr($value, '*', '%');
				}

				$user_model = Configure::read('Security.authModel');
				$users_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . $user_model);
				$users = $users_table->find()
					->where([$users_table->emailField . $op => $value])
					->extract('id')
					->toArray();

				$search_conditions = ["People.alternate_email$op" => $value];
				if (!empty($users)) {
					$search_conditions = ['OR' => [
						'user_id IN' => $users,
						$search_conditions,
					]];
				}
				$query = $this->People->find()
					->contain([$user_model])
					->where($search_conditions);

				if ($query->count()) {
					// Match people in the affiliate, or admins who are effectively in all
					if (array_key_exists('affiliate_id', $params)) {
						$admins = $this->People->find()
							->enableHydration(false)
							->select(['People.id'])
							->matching('Groups', function (Query $q) {
								return $q->where(['Groups.id' => GROUP_ADMIN]);
							})
							->extract('id')
							->toArray();
						$query->matching('Affiliates')
							->andwhere(['OR' => [
								'AffiliatesPeople.affiliate_id' => $params['affiliate_id'],
								'People.id IN' => $admins,
							]])
							->order(['Affiliates.name']);
					}

					$this->set('people', $this->paginate($query));
				} else {
					$this->Flash->info(__('No matches found!'));
					return false;
				}
			}
		}
	}

	protected function _handleRuleSearch($params, $url) {
		$affiliates = $this->Authentication->applicableAffiliates();
		$this->set(compact('url', 'affiliates'));
		unset($url['rule']);

		// If a rule has been submitted through the form, ignore whatever might be saved in the URL
		if (array_key_exists('rule', $params)) {
			unset($params['rule64']);
		}

		if (array_key_exists('rule64', $params)) {
			$params['rule'] = \App\Lib\base64_url_decode($params['rule64']);
		}

		if (array_key_exists('rule', $params)) {
			// Handle the rule
			$rule_obj = $this->moduleRegistry->load('RuleEngine');
			if (!$rule_obj->init($params['rule'])) {
				$this->Flash->info(__('Failed to parse the rule: {0}', $rule_obj->parse_error));
				return false;
			}
			if (!array_key_exists('rule64', $params)) {
				$url['rule64'] = \App\Lib\base64_url_encode($params['rule']);
			}
			$this->set(compact('url', 'params'));

			// TODO: Is this the kind of thing that might benefit from 'strategy' => 'subquery'?
			try {
				$people = $rule_obj->query($params['affiliate_id']);
			} catch (RuleException $ex) {
				$this->Flash->info($ex->getMessage());
				return false;
			}

			if (!empty($people)) {
				// Set the default pagination order; query params may override it.
				// TODO: Multiple default sort fields break pagination links.
				// https://github.com/cakephp/cakephp/issues/7324 has related info.
				//$this->paginate['order'] = ['People.last_name', 'People.first_name', 'People.id'];
				$this->paginate['order'] = ['People.last_name'];

				$query = $this->People->find()
					->contain([
						Configure::read('Security.authModel'),
						'Groups',
					])
					->where(['People.id IN' => $people]);

				if ($this->getRequest()->is('csv')) {
					$this->setResponse($this->getResponse()->withDownload('Search results.csv'));
					$this->set('people', $query->contain(['Related'])->toArray());
					$this->render('rule_search');
				} else {
					if (Configure::read('feature.badges')) {
						$badge_obj = $this->moduleRegistry->load('Badge');
						$query->contain(['Badges' => [
							'queryBuilder' => function (Query $q) use ($badge_obj) {
								return $q->where([
									'BadgesPeople.approved' => true,
									'Badges.visibility IN' => $badge_obj->visibility($this->Authentication->getIdentity(), BADGE_VISIBILITY_HIGH),
								]);
							},
						]]);
					}

					$query->contain(['Affiliates']);
					$this->set('people', $this->paginate($query));
				}
			} else {
				$this->Flash->info(__('No matches found!'));
				return false;
			}
		}

		return true;
	}

	public function list_new() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$user_model = Configure::read('Security.authModel');

		$new = $this->People->find()
			->contain([
				$user_model,
				'Affiliates' => [
					'queryBuilder' => function (Query $q) use ($affiliates) {
						return $q->where(['Affiliates.id IN' => $affiliates]);
					},
				],
			])
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->where([
				'People.status' => 'new',
				'People.complete' => true,
			])
			->order(['People.last_name', 'People.first_name'])
			->toArray();

		foreach ($new as $person) {
			$duplicates = $this->People->find('duplicates', compact('person'));
			$person->duplicate = ($duplicates->count() > 0);
		}

		$this->set(compact('new'));
	}

	public function approve() {
		$id = $this->getRequest()->getQuery('person');

		// We don't need to contain Relatives here; those will be handled in the updateAll calls
		$contain = [Configure::read('Security.authModel'), 'AffiliatesPeople', 'Skills', 'Groups', 'Related', 'Settings'];

		try {
			/** @var Person $person */
			$person = $this->People->get($id, ['contain' => $contain]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['action' => 'list_new']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['action' => 'list_new']);
		}

		$this->Authorization->authorize($person);

		$duplicates = $this->People->find('duplicates', compact('person'))
			->contain($contain);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if (empty($this->getRequest()->getData('disposition'))) {
				$this->Flash->info(__('You must select a disposition for this account.'));
			} else {
				if (strpos($this->getRequest()->getData('disposition'), ':') !== false) {
					[$disposition, $dup_id] = explode(':', $this->getRequest()->getData('disposition'));
					$duplicate = collection($duplicates)->firstMatch(['id' => $dup_id]);
					if ($duplicate) {
						if ($this->_approve($person, $disposition, $duplicate)) {
							return $this->redirect(['action' => 'list_new']);
						}
						// If this fails, we've messed up the duplicate record. Re-read the duplicates to reset it.
						$duplicates = $this->People->find('duplicates', compact('person'))
							->contain($contain);
					} else {
						$this->Flash->info(__('You have selected an invalid user!'));
					}
				} else {
					if ($this->_approve($person, $this->getRequest()->getData('disposition'))) {
						return $this->redirect(['action' => 'list_new']);
					}
				}
			}
		}

		$user_model = Configure::read('Security.authModel');
		$users_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . $user_model);
		$activated = $users_table->activated($person);

		$this->set(compact('person', 'duplicates', 'activated'));
	}

	protected function _approve(Person $person, $disposition, Person $duplicate = null) {
		$delete = $save = $fail_message = null;

		// First, take whatever steps are required to prepare the data for saving and/or deleting.
		// Also prepare the options for sending the notification email, if any.
		switch($disposition) {
			case 'approved':
				$person->status = 'active';
				$save = $person;
				$fail_message = __('Couldn\'t save new member activation');

				$mail_opts = [
					'subject' => function() use ($person) {
						return __('{0} {1} Activation for {2}',
							Configure::read('organization.name'),
							empty($person->user_id) ? __('Profile') : __('Account'),
							empty($person->user_id) ? $person->full_name : $person->user_name
						);
					},
					'template' => 'account_approved',
				];
				break;

			/** @noinspection PhpMissingBreakStatementInspection */
			case 'delete_duplicate':
				$mail_opts = [
					'subject' => function() { return __('{0} Account Update', Configure::read('organization.name')); },
					'template' => 'account_delete_duplicate',
				];
				// Intentionally fall through to the next option

			case 'delete':
				$delete = $person;
				break;

			// This is basically the same as delete duplicate, except
			// that some old information (e.g. user ID) is preserved
			case 'merge_duplicate':
				$duplicate->merge($person);
				$save = $duplicate;
				$delete = $person;
				$fail_message = __('Couldn\'t save new member information');

				$mail_opts = [
					'subject' => function() { return __('{0} Account Update', Configure::read('organization.name')); },
					'template' => 'account_merge_duplicate',
				];
				break;
		}

		if (!$this->People->getConnection()->transactional(function () use ($disposition, $save, $delete, $fail_message) {
			// If we are merging, we want to migrate all records that aren't part of the in-memory record.
			if ($disposition === 'merge_duplicate') {
				// For anything that we have in memory, we must skip doing a direct query
				$ignore = ['Affiliates'];
				$save->setHidden([]);
				foreach ($save->getVisible() as $prop) {
					if ($save->isAccessible($prop) && (is_array($delete->$prop))) {
						$ignore[] = Inflector::camelize($prop);
					}
				}

				$associations = $this->People->associations();

				foreach ($associations->getByType('BelongsToMany') as $association) {
					if (!in_array($association->getName(), $ignore)) {
						$foreign_key = $association->getForeignKey();
						$conditions = [$foreign_key => $delete->id];
						$association_conditions = $association->getConditions();
						if (!empty($association_conditions)) {
							$conditions += $association_conditions;
						}
						$association->junction()->updateAll([$foreign_key => $save->id], $conditions);
					}

					// BelongsToMany associations also create HasMany associations for the join tables.
					// Ignore them when we get there.
					$ignore[] = $association->junction()->getAlias();
				}

				foreach ($associations->getByType('HasMany') as $association) {
					if (!in_array($association->getName(), $ignore)) {
						$foreign_key = $association->getForeignKey();
						$conditions = [$foreign_key => $delete->id];
						$association_conditions = $association->getConditions();
						if (!empty($association_conditions)) {
							$conditions += $association_conditions;
						}
						$association->getTarget()->updateAll([$foreign_key => $save->id], $conditions);
					}
				}
			}

			if ($delete && !$this->People->delete($delete)) {
				$this->Flash->warning(__('Failed to delete {0}.', $delete->full_name));
				return false;
			}

			if ($save && !$this->People->save($save)) {
				$this->Flash->warning($fail_message);
				return false;
			}

			return true;
		})) {
			return false;
		}

		// Clear any related cached information
		// TODO: It's conceivable that there could also be stored teams, division, stats, etc. with the deleted person_id in them.
		// For now, we'll just clear everything whenever this happens...
		Cache::clear(false, 'long_term');
		/*
		Cache::delete("person/{$person->id}", 'long_term');
		foreach ($person->related as $relative) {
			$this->UserCache->clear('Relatives', $relative->id);
			$this->UserCache->clear('RelativeIDs', $relative->id);
		}
		if (isset($duplicate)) {
			Cache::delete("person/{$duplicate->id}", 'long_term');
			foreach ($duplicate->related as $relative) {
				$this->UserCache->clear('Relatives', $relative->id);
				$this->UserCache->clear('RelativeIDs', $relative->id);
			}
		}
		*/

		// Take care of any required notifications
		if (isset($mail_opts)) {
			if (!$this->_sendMail(array_merge($mail_opts, [
				'to' => isset($duplicate) ? [$person, $duplicate] : $person,
				'sendAs' => 'both',
				'viewVars' => compact('person', 'duplicate'),
			]))) {
				$this->Flash->warning(__('Error sending email to {0}.', $person->full_name));
			}
		}

		return true;
	}

	public function vcf() {
		$this->viewBuilder()->setLayout('vcf');
		$id = $this->getRequest()->getQuery('person');
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}
		$person->updateHidden($this->Authentication->getIdentity());

		$this->set(compact('person'));
		$this->setResponse($this->getResponse()->withDownload("{$person->full_name}.vcf"));
	}

	/**
	 * iCal method
	 *
	 * This function takes the parameter the old-fashioned way, to try to be more third-party friendly
	 *
	 * @param string|null $id Person id.
	 * @return void
	 * @throws \Cake\Http\Exception\GoneException When record not found.
	 */
	public function ical($id) {
		$this->viewBuilder()->setLayout('ical');
		$id = intval($id);

		// Check that the person has enabled this option
		try {
			$person = $this->People->get($id, [
				'contain' => [
					'Settings' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Settings.name' => 'enable_ical']);
						},
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			throw new GoneException();
		} catch (InvalidPrimaryKeyException $ex) {
			throw new GoneException();
		}

		$this->Authorization->authorize($person);

		$team_ids = $this->UserCache->read('TeamIDs', $id);

		if (!empty($team_ids)) {
			$games = TableRegistry::getTableLocator()->get('Games')
				->find('schedule', ['teams' => $team_ids])
				->contain([
					'ScoreEntries' => [
						'queryBuilder' => function (Query $q) use ($team_ids) {
							return $q->where(['ScoreEntries.team_id IN' => $team_ids]);
						},
					],
				])
				->where([
					'Games.published' => true,
				])
				->order(['GameSlots.game_date', 'GameSlots.game_start'])
				->toArray();

			$events = $this->People->Divisions->Teams->TeamEvents->find()
				->contain(['Teams'])
				->where([
					'TeamEvents.team_id IN' => $team_ids,
				])
				->toArray();

			// Game iCal element will handle team_id as an array
			$this->set('team_id', $team_ids);
			$this->set(compact('games', 'events'));
		}

		if (Configure::read('feature.tasks')) {
			$this->set('tasks', $this->UserCache->read('Tasks', $id));
		}

		$this->set('calendar_type', 'Player Schedule');
		$this->set('calendar_name', "{$person->full_name}'s schedule");
		$this->setResponse($this->getResponse()->withDownload("$id.ics"));
		$this->RequestHandler->ext = 'ics';
	}

	public function registrations() {
		$id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$query = $this->People->Registrations->find()
			->contain([
				'Events' => ['EventTypes', 'Prices', 'Affiliates', 'Divisions' => ['Leagues', 'Days']],
				'Prices',
				'Payments',
			])
			->matching('Events.Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->where(['person_id' => $id])
			->order(['Events.affiliate_id', 'Registrations.created' => 'DESC']);

		$this->set('registrations', $this->paginate($query));
		$this->set(compact('person', 'affiliates'));
	}

	public function credits() {
		$id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		try {
			$person = $this->People->get($id, [
				'contain' => [
					'Credits' => [
						'queryBuilder' => function (Query $q) use ($affiliates) {
							return $q
								->where(['Credits.affiliate_id IN' => $affiliates])
								->order(['Credits.affiliate_id', 'Credits.created']);
						},
						'Affiliates',
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		$this->set(compact('person', 'affiliates'));
	}

	public function teams() {
		$id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		$this->set(compact('person'));
		$this->set('teams', array_reverse($this->UserCache->read('AllTeams', $id)));
	}

	public function waivers() {
		$id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		try {
			$person = $this->People->get($id, [
				'contain' => [
					'Waivers' => [
						'Affiliates',
						'queryBuilder' => function (Query $q) use ($affiliates) {
							return $q
								->where(['Waivers.affiliate_id IN' => $affiliates])
								->order(['Waivers.affiliate_id', 'WaiversPeople.created' => 'DESC']);
						}
					],
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($person);

		$waivers = [];
		if ($id == $this->UserCache->currentId()) {
			foreach ($affiliates as $affiliate) {
				$signed_names = array_unique(
					collection($this->UserCache->read('WaiversCurrent'))
						->match(['affiliate_id' => $affiliate])
						->extract(function ($entity) { return $entity->translateField('name'); })
						->toArray()
				);
				$affiliate_waivers = $this->People->Waivers->find()
					->contain(['Affiliates'])
					->where([
						'Waivers.active' => true,
						'Waivers.expiry_type !=' => 'event',
						'Waivers.affiliate_id' => $affiliate,
					]);
				if (!empty($signed_names)) {
					$affiliate_waivers->andWhere(['NOT' => ['Waivers.name IN' => $signed_names]]);
				}
				$waivers = array_merge($waivers, $affiliate_waivers->toArray());
			}
		}

		$this->set(compact('person', 'affiliates', 'waivers'));
	}

}
