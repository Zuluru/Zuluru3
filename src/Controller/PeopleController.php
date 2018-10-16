<?php
namespace App\Controller;

use Cake\Cache\Cache;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Filesystem\File;
use Cake\I18n\FrozenDate;
use Cake\Network\Exception\GoneException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use App\Auth\HasherTrait;
use App\Exception\RuleException;
use App\Model\Entity\BadgesPerson;
use App\Model\Entity\Person;
use App\Model\Entity\PeoplePerson;

/**
 * People Controller
 *
 * @property \App\Model\Table\PeopleTable $People
 */
class PeopleController extends AppController {

	use HasherTrait;

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _publicActions() {
		return ['view', 'tooltip', 'ical',
			// Relative approvals and removals may come from emailed links; people might not be logged in
			'approve_relative', 'remove_relative',
		];
	}

	/**
	 * _publicJsonActions method
	 *
	 * @return array of JSON actions that can be taken even by visitors that are not logged in.
	 */
	protected function _publicJsonActions() {
		return ['view'];
	}

	/**
	 * _freeActions method
	 *
	 * @return array list of actions that people can perform even if the system wants them to do something else
	 */
	protected function _freeActions() {
		return ['act_as'];
	}

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if badges are not enabled
	 */
	public function isAuthorized() {
		try {
			if ($this->request->getParam('action') == 'act_as') {
				// People can always act as their real id, or as any relative of the current or real user
				$person = $this->request->getQuery('person');
				if ($person) {
					$relatives = $this->UserCache->allActAs();
					if (array_key_exists($person, $relatives)) {
						return true;
					}
				} else {
					return true;
				}
			}

			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (!Configure::read('feature.badges')) {
				if (in_array($this->request->getParam('action'), [
					'nominate',
					'nominate_badge',
					'nominate_badge_reason',
					'approve_badges',
					'approve_badge',
					'delete_badge',
				]))
				{
					throw new MethodNotAllowedException('Badges are not enabled on this system.');
				}
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'index',
					'list_new',
					'statistics',
					'participation',
					'retention',
					'rule_search',
					'league_search',
					'inactive_search',
					'approve_badges',
				]))
				{
					// If an affiliate id is specified, check if we're a manager of that affiliate
					$affiliate = $this->request->getQuery('affiliate');
					if (!$affiliate) {
						// If there's no affiliate id, this is a top-level operation that all managers can perform
						return true;
					} else if (in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					} else {
						Configure::write('Perm.is_manager', false);
					}
				}

				if (in_array($this->request->getParam('action'), [
					'approve_badge',
					'delete_badge',
				]))
				{
					// If a badge id is specified, check if we're a manager of that badge's affiliate
					// This isn't the real badge id, but the id of the badge/person join table
					$badge = $this->request->getQuery('badge');
					if ($badge) {
						if (in_array($this->People->BadgesPeople->affiliate($badge), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}

				if (in_array($this->request->getParam('action'), [
					'approve_document',
					'edit_document',
				])) {
					$document = $this->request->getQuery('document');
					if ($document) {
						if (in_array($this->People->Uploads->affiliate($document), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}

				if (in_array($this->request->getParam('action'), [
					'edit',
					'deactivate',
					'reactivate',
					'waivers',
					'registrations',
					'credits',
					'act_as',
				]))
				{
					// If a person id is specified, check if we're a manager of that person's affiliate
					$person = $this->request->getQuery('person');
					if ($person) {
						if (!empty(array_intersect($this->UserCache->read('AffiliateIDs', $person), $this->UserCache->read('ManagedAffiliateIDs')))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}

			// Anyone that's logged in can perform these operations
			if (in_array($this->request->getParam('action'), [
				'search',
				'teams',
				'photo',
				'vcf',
				'note',
				'delete_note',
				'nominate',
				'nominate_badge',
				'nominate_badge_reason',
				'confirm',
			]))
			{
				return true;
			}

			// People can perform these operations on their own account
			if (in_array($this->request->getParam('action'), [
				'edit',
				'deactivate',
				'reactivate',
				'preferences',
				'link_relative',
				'waivers',
				'photo_upload',
				'photo_resize',
				'document_upload',
				'registrations',
				'credits',
			]))
			{
				// If a player id is specified, check if it's the logged-in user, or a relative
				// If no player id is specified, it's always the logged-in user
				$person = $this->request->getQuery('person');
				$relatives = $this->UserCache->read('RelativeIDs');
				if (!$person || $person == $this->UserCache->currentId() || in_array($person, $relatives)) {
					return true;
				}
			}

			// Parents can perform these operations on their own account
			if (in_array($this->request->getParam('action'), [
				'add_relative',
			]))
			{
				if (in_array(GROUP_PARENT, $this->UserCache->read('GroupIDs'))) {
					return true;
				}
			}

			// Anyone can perform these actions on their own documents. Managers can perform them on documents belonging
			// to their affiliates.
			if (in_array($this->request->getParam('action'), [
				'document',
				'delete_document',
			]))
			{
				$document = $this->request->getQuery('document');
				if ($document) {
					$person = $this->People->Uploads->field('person_id', ['id' => $document]);
					if ($person == $this->UserCache->currentId()) {
						return true;
					}

					if (Configure::read('Perm.is_manager')) {
						if (in_array($this->People->Uploads->affiliate($document), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						}
					}
				}
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));
		$group_id = $this->request->getQuery('group');

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
				$group = $this->People->Groups->field('name', ['id' => $group_id]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid group.'));
				return $this->redirect('/');
			}
			$this->set(compact('group'));
		}

		if ($this->request->is('csv')) {
			if ($group_id) {
				$this->response->download(Inflector::pluralize($group) . '.csv');
			} else {
				$this->response->download('People.csv');
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
							'Badges.visibility IN' => $badge_obj->visibility(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'), BADGE_VISIBILITY_HIGH),
						]);
					},
				]]);
			}

			$this->set('people', $this->paginate($query));
		}
	}

	public function statistics() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		// Get the list of accounts by status
		$query = $this->People->find();
		$status_count = $query
			->select(['status', 'person_count' => $query->func()->count('People.id')])
			->select($this->People->Affiliates)
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->group(['AffiliatesPeople.affiliate_id', 'People.status'])
			->order(['Affiliates.name', 'People.status']);

		// Get the list of players by gender
		$query = $this->People->find();
		$gender_count = $query
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
			->where(['Skills.enabled' => true])
			->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'People.gender'])
			->order(['Affiliates.name', 'Skills.sport', 'People.gender' => 'DESC']);

		// Get the list of players by roster designation
		if (Configure::read('gender.column') == 'roster_designation') {
			$query = $this->People->find();
			$roster_designation_count = $query
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
				->where(['Skills.enabled' => true])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'People.' . Configure::read('gender.column')])
				->order(['Affiliates.name', 'Skills.sport', 'People.' . Configure::read('gender.column') => Configure::read('gender.order')]);
		}

		// Get the list of accounts by group
		$query = $this->People->find();
		$group_count = $query
			->select([Configure::read('gender.column'), 'person_count' => $query->func()->count('People.id')])
			->select($this->People->Affiliates)
			->select($this->People->Groups)
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->matching('Groups')
			->group(['AffiliatesPeople.affiliate_id', 'Groups.id'])
			->order(['Affiliates.name', 'Groups.id']);

		// Get the list of players by age
		if (Configure::read('profile.birthdate')) {
			$query = $this->People->find();
			$age_count = $query
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
					'birthdate IS NOT' => null,
					'birthdate !=' => '0000-00-00',
				])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'age_bucket'])
				->order(['Affiliates.name', 'Skills.sport', 'age_bucket']);
		}

		// Get the list of players by year started for each sport
		if (Configure::read('profile.year_started')) {
			$query = $this->People->find();
			$started_count = $query
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
				->where(['Skills.enabled' => true])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'Skills.year_started'])
				->order(['Affiliates.name', 'Skills.sport', 'Skills.year_started']);
		}

		// Get the list of players by skill level for each sport
		if (Configure::read('profile.skill_level')) {
			$query = $this->People->find();
			$skill_count = $query
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
				->where(['Skills.enabled' => true])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'Skills.skill_level'])
				->order(['Affiliates.name', 'Skills.sport', 'Skills.skill_level']);
		}

		// Get the list of players by city
		if (Configure::read('profile.addr_city')) {
			$query = $this->People->find();
			$city_count = $query
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
				->where(['Skills.enabled' => true])
				->group(['AffiliatesPeople.affiliate_id', 'Skills.sport', 'addr_city'])
				->having(['person_count >' => 2])
				->order(['Affiliates.name', 'Skills.sport', 'person_count' => 'DESC']);
		}

		$this->set(compact('status_count', 'group_count', 'gender_count', 'roster_designation_count', 'age_count', 'started_count', 'skill_count', 'city_count'));
	}

	public function participation() {
		$min = min(
			TableRegistry::get('Events')->field('open', [], 'open')->year,
			TableRegistry::get('Leagues')->field('open', [], 'open')->year
		);
		$this->set(compact('min'));

		// Check form data
		if ($this->request->is(['patch', 'post', 'put'])) {
			if ($this->request->data['start'] > $this->request->data['end']) {
				$this->Flash->info(__('End date cannot precede start date.'));
				return;
			}

			$reports_table = TableRegistry::get('Reports');
			$report = $reports_table->newEntity([
				'report' => 'people_participation',
				'person_id' => $this->UserCache->currentId(),
				'params' => json_encode($this->request->data),
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
		$min = min(
			TableRegistry::get('Events')->field('open', [], 'open'),
			TableRegistry::get('Leagues')->field('open', [], 'open')
		);
		$this->set(compact('min'));

		// Check form data
		if ($this->request->is(['patch', 'post', 'put'])) {
			if ($this->request->data['start'] > $this->request->data['end']) {
				$this->Flash->info(__('End date cannot precede start date.'));
				return;
			}

			$reports_table = TableRegistry::get('Reports');
			$report = $reports_table->newEntity([
				'report' => 'people_retention',
				'person_id' => $this->UserCache->currentId(),
				'params' => json_encode($this->request->data),
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
		$id = $this->request->getQuery('person');
		$user_id = $this->request->getQuery('user');

		if ($user_id) {
			try {
				$id = $this->People->field('id', compact('user_id'));
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect('/');
			}
		} else if (!$id) {
			$id = Configure::read('Perm.my_id');
		}
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}
		if (!Configure::read('Perm.is_logged_in') && $person->status == 'inactive') {
			throw new GoneException();
		}

		$person->groups = $this->UserCache->read('Groups', $person->id);
		$person->skills = collection($this->UserCache->read('Skills', $person->id))->filter(function ($skill) { return $skill->enabled; })->toArray();
		$person->teams = $this->UserCache->read('Teams', $person->id);
		$photo = null;

		if (Configure::read('Perm.is_logged_in')) {
			// Public functions need an extra check for managers
			if (Configure::read('Perm.is_manager') && empty(array_intersect($this->UserCache->read('AffiliateIDs', $id), $this->UserCache->read('ManagedAffiliateIDs')))) {
				Configure::write('Perm.is_manager', false);
			}

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
				if (Configure::read('Perm.is_admin')) {
					$visibility[] = VISIBILITY_ADMIN;
				}
				$person->notes = $this->People->Notes->find()
					->contain(['CreatedPerson'])
					->where([
						'person_id' => $person->id,
						'OR' => [
							'Notes.created_person_id' => Configure::read('Perm.my_id'),
							'Notes.visibility IN' => $visibility,
						],
					])
					->toArray();
			}
			if (Configure::read('feature.tasks') && ($id == Configure::read('Perm.my_id') || Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) {
				$person->tasks = $this->UserCache->read('Tasks', $person->id);
			}
			if (Configure::read('feature.badges')) {
				$badge_obj = $this->moduleRegistry->load('Badge');
				$badge_obj->visibility(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'));

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

		$is_me = ($id == Configure::read('Perm.my_id'));
		$is_relative = in_array($id, $this->UserCache->read('RelativeIDs'));
		$person->updateHidden(array_merge($this->_connections($id), compact('is_me', 'is_relative')));
		$photo_url = $person->photoUrl($photo);
		$this->set(compact('person', 'photo', 'photo_url', 'is_me', 'is_relative'));
		$this->set('_serialize', ['person', 'photo_url']);
	}

	public function tooltip() {
		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('person');
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}
		$photo = null;

		if (Configure::read('Perm.is_logged_in')) {
			// Public functions need an extra check for managers
			if (Configure::read('Perm.is_manager') && empty(array_intersect($this->UserCache->read('AffiliateIDs', $id), $this->UserCache->read('ManagedAffiliateIDs')))) {
				Configure::write('Perm.is_manager', false);
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
			if (Configure::read('feature.badges')) {
				$badge_obj = $this->moduleRegistry->load('Badge');
				$badge_obj->visibility(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'), BADGE_VISIBILITY_HIGH);

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

		$this->set(compact('person', 'photo'));
		$this->set('is_me', ($id == $this->UserCache->currentId()));
		$this->set($this->_connections($id));
	}

	protected function _connections($id) {
		$connections = [];

		// Pull some lists of team and division IDs for later comparisons
		$my_team_ids = $this->UserCache->read('TeamIDs');
		$my_owned_team_ids = $this->UserCache->read('OwnedTeamIDs');
		$my_owned_division_ids = $this->UserCache->read('DivisionIDs');
		$my_captain_division_ids = collection($this->UserCache->read('OwnedTeams'))->extract('division_id')->toArray();
		$their_team_ids = $this->UserCache->read('TeamIDs', $id);
		$their_owned_team_ids = $this->UserCache->read('OwnedTeamIDs', $id);
		$their_owned_division_ids = $this->UserCache->read('DivisionIDs', $id);
		$their_captain_division_ids = collection($this->UserCache->read('OwnedTeams', $id))->extract('division_id')->toArray();

		// Check if the current user is a captain of a team the viewed player is on
		$connections['is_captain'] = !empty(array_intersect($my_owned_team_ids, $their_team_ids));

		// Check if the current user is on a team the viewed player is a captain of
		$connections['is_my_captain'] = !empty(array_intersect($my_team_ids, $their_owned_team_ids));

		// Check if the current user is a coordinator of a division the viewed player is a captain in
		$connections['is_coordinator'] = !empty(array_intersect($my_owned_division_ids, $their_captain_division_ids));

		// Check if the current user is a captain in a division the viewed player is a coordinator of
		$connections['is_my_coordinator'] = !empty(array_intersect($my_captain_division_ids, $their_owned_division_ids));

		// Check if the current user is a captain in a division the viewed player is a captain in
		$connections['is_division_captain'] = !empty(array_intersect($my_captain_division_ids, $their_captain_division_ids));

		return $connections;
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->getQuery('person') ?: $this->UserCache->currentId();
		$is_me = ($id === $this->UserCache->currentId());
		$this->set(compact('id', 'is_me'));

		$this->_loadAddressOptions();
		// We always want to include players, even if they aren't a valid "create account" group.
		$this->set('groups', $this->People->Groups->find('options', ['require_player' => true])->toArray());
		$this->_loadAffiliateOptions();

		try {
			$contain = ['Affiliates', 'Skills', 'Groups', Configure::read('Security.authModel')];
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
			$person->dirty('skills', false);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}
		$this->set('upload', Configure::read('feature.photos') && $is_me && empty($person->uploads));

		if ($this->request->is(['patch', 'post', 'put'])) {
			$access = [PROFILE_USER_UPDATE, PROFILE_REGISTRATION];
			// People with incomplete profiles can update any of the fields that
			// normally only admins can edit, so that they can successfully fill
			// out all of the profile.
			if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || !$person->complete) {
				$access[] = PROFILE_ADMIN_UPDATE;
			}

			// TODO: Centralize checking of profile fields
			$columns = $this->People->schema()->columns();
			$accessible = [
				'id' => false,
				'user_id' => false,
				'complete' => false,
				'modified' => false,
				'status' => Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'),
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

			$person = $this->People->patchEntity($person, $this->request->data, [
				'associated' => ['Affiliates', 'Groups', 'Skills', Configure::read('Security.authModel')],
				'accessibleFields' => $accessible,
			]);

			if ($this->People->save($person, ['manage_affiliates' => true, 'manage_groups' => true])) {
				if ($is_me) {
					$this->Flash->success(__('Your profile has been saved.'));
				} else {
					$this->Flash->success(__('The person has been saved.'));
				}
				return $this->redirect('/');
			} else {
				$this->Flash->warning(__('The person could not be saved. Please correct the errors below and try again.'));
			}
		}

		$users_table = $this->loadModel(Configure::read('Security.authModel'));
		$this->set([
			'person' => $person,
			'user_model' => Configure::read('Security.authModel'),
			'id_field' => $users_table->primaryKey(),
			'user_field' => $users_table->userField,
			'email_field' => $users_table->emailField,
			'_serialize' => true,
		]);
	}

	/**
	 * Deactivate profile method
	 *
	 * @return void|\Cake\Network\Response Redirects
	 */
	public function deactivate() {
		$id = $this->request->getQuery('person') ?: $this->UserCache->currentId();
		try {
			$person = $this->People->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

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

		if ($this->request->is(['patch', 'post', 'put'])) {
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
		$id = $this->request->getQuery('person') ?: $this->UserCache->currentId();
		try {
			$person = $this->People->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		if ($person->status != 'inactive') {
			$this->Flash->info(__('Only inactive profiles can be reactivated.'));
			return $this->redirect('/');
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
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
		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$person = $this->People->get($this->UserCache->currentId());
		$this->People->touch($person);
		if ($this->People->save($person)) {
			$this->Flash->success(__("Profile details have been confirmed, thank you.\nYou will be reminded about this again periodically."));
		} else {
			$this->Flash->info(__("Failed to update profile details.\nYou will likely be prompted about this again very soon.\n\nIf problems persist, contact your system administrator."));
			$this->log($person->errors());
			return $this->redirect('/');
		}
	}

	public function note() {
		$note_id = $this->request->getQuery('note');

		if ($note_id) {
			try {
				$note = $this->People->Notes->get($note_id, [
					'contain' => ['People'],
				]);

				// Check that this user is allowed to edit this note
				if ($note->created_person_id != Configure::read('Perm.my_id')) {
					$this->Flash->warning(__('You are not allowed to edit that note.'));
					return $this->redirect(['action' => 'view', 'person' => $note->person->id]);
				}
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
				$person = $this->People->get($this->request->getQuery('person'));
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

		if ($this->request->is(['patch', 'post', 'put'])) {
			$note = $this->People->Notes->patchEntity($note, $this->request->data);

			if (empty($note->note)) {
				if ($note->isNew()) {
					$this->Flash->warning(__('You entered no text, so no note was added.'));
					return $this->redirect(['action' => 'view', 'person' => $person->id]);
				} else {
					if ($this->People->Notes->delete($note)) {
						$this->Flash->success(__('The note has been deleted.'));
						return $this->redirect(['action' => 'view', 'person' => $person->id]);
					} else if ($note->errors('delete')) {
						$this->Flash->warning(current($note->errors('delete')));
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
		$this->request->allowMethod(['post', 'delete']);

		$note_id = $this->request->getQuery('note');

		try {
			$note = $this->People->Notes->get($note_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid note.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid note.'));
			return $this->redirect('/');
		}

		if ($note->created_person_id == Configure::read('Perm.my_id') || (Configure::read('Perm.is_admin') && $note->visibility == VISIBILITY_ADMIN)) {
			if ($this->People->Notes->delete($note)) {
				$this->Flash->success(__('The note has been deleted.'));
			} else if ($note->errors('delete')) {
				$this->Flash->warning(current($note->errors('delete')));
			} else {
				$this->Flash->warning(__('The note could not be deleted. Please, try again.'));
			}
		} else {
			$this->Flash->warning(__('You are not allowed to delete that note.'));
		}
		return $this->redirect(['action' => 'view', 'person' => $note->person_id]);
	}

	public function preferences() {
		$id = $this->request->getQuery('person') ?: $this->UserCache->currentId();
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$settings = $this->People->Settings->find()
			->where(['person_id' => $id])
			->toArray();

		if ($this->request->is(['patch', 'post', 'put'])) {
			$settings = $this->People->Settings->patchEntities($settings, $this->request->data);

			if ($this->People->Settings->connection()->transactional(function () use ($settings) {
				foreach ($settings as $setting) {
					if (!$this->People->Settings->save($setting)) {
						return false;
					}
				}
				return true;
			})) {
				$this->Flash->success(__('The preferences have been saved.'));

				if ($id == $this->UserCache->currentId()) {
					// Reload the configuration right away, so it affects any rendering we do now,
					// and rebuild the menu based on any changes.
					$this->Configuration->loadUser($id);
					$this->_setLanguage();
					$this->_initMenu();
				}
			} else {
				$this->Flash->warning(__('The preferences could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->set(compact('id', 'person', 'settings'));
	}

	public function add_relative() {
		$this->_loadAffiliateOptions();
		$person = $this->People->newEntity();

		if ($this->request->is(['patch', 'post', 'put'])) {
			/* TODODATABASE: User and person records may be in separate databases, so we need a transaction for each
			$users_table = $this->loadModel(Configure::read('Security.authModel'));
			$user_transaction = new DatabaseTransaction($users_table);
			$person_transaction = new DatabaseTransaction($this->Person);
			*/

			$this->request->data['is_child'] = true;
			$person = $this->People->patchEntity($person, $this->request->data, [
				'validate' => 'create',
				'associated' => ['Affiliates', 'Groups', 'Skills'],
			]);

			if ($this->People->connection()->transactional(function () use ($person) {
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
				if ($this->request->data['action'] == 'continue') {
					$person = $this->People->newEntity();
				} else {
					return $this->redirect('/');
				}
			}
		}

		$this->set(compact('person'));
	}

	public function link_relative() {
		$person_id = $this->request->getQuery('person');
		if (!$person_id) {
			$person_id = $this->UserCache->currentId();
		}
		try {
			$person = $this->People->get($person_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}
		$this->set(compact('person'));

		$relative_id = $this->request->getQuery('relative');
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
							'subject' => __('You have been linked as a relative'),
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
		$person_id = $this->request->getQuery('person');
		$relative_id = $this->request->getQuery('relative');
		if ($relative_id === null || $person_id === null) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['action' => 'view', 'person' => $person_id]);
		}

		$relation = collection($this->UserCache->read('RelatedTo', $person_id))->firstMatch(['_joinData.approved' => false, '_joinData.person_id' => $relative_id]);
		if (!$relation) {
			$this->Flash->info(__('This person does not have an outstanding relative request for you.'));
			return $this->redirect(['action' => 'view', 'person' => $person_id]);
		}

		// We must do other permission checks here, because we allow non-logged-in users to approve
		// through email links
		$code = $this->request->getQuery('code');
		if ($code) {
			// Authenticate the hash code
			if (!$this->_checkHash([$relation->_joinData->id, $relation->_joinData->person_id, $relation->_joinData->relative_id, $relation->_joinData->created], $code)) {
				$this->Flash->warning(__('The authorization code is invalid.'));
				return $this->redirect(['action' => 'view', 'person' => $person_id]);
			}
		} else {
			// Public functions need an extra check for managers
			if (Configure::read('Perm.is_manager') && empty(array_intersect($this->UserCache->read('AffiliateIDs', $person_id), $this->UserCache->read('ManagedAffiliateIDs')))) {
				Configure::write('Perm.is_manager', false);
			}

			if (!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager') && $person_id != $this->UserCache->currentId()) {
				$this->Flash->warning(__('You are not allowed to approve this relative request.'));
				return $this->redirect(['action' => 'view', 'person' => $person_id]);
			}
		}

		$relation->_joinData->approved = true;
		$people_people_table = TableRegistry::get('PeoplePeople');
		if (!$people_people_table->save($relation->_joinData)) {
			$this->Flash->warning(__('Failed to approve the relative request.'));
			return $this->redirect(['action' => 'view', 'person' => $person_id]);
		}

		$this->Flash->success(__('Approved the relative request.'));

		$person = $this->UserCache->read('Person', $person_id);
		$relative = $this->UserCache->read('Person', $relative_id);
		if (!$this->_sendMail([
			'to' => $relative,
			'replyTo' => $person,
			'subject' => __('{0} approved your relative request', $person->full_name),
			'template' => 'relative_approve',
			'sendAs' => 'both',
			'viewVars' => compact('person', 'relative'),
		]))
		{
			$this->Flash->warning(__('Error sending email to {0}.', $relative->full_name));
		}

		return $this->redirect(['action' => 'view', 'person' => $person_id]);
	}

	public function remove_relative() {
		$person_id = $this->request->getQuery('person');
		$relative_id = $this->request->getQuery('relative');
		if ($relative_id === null || $person_id === null) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['action' => 'view', 'person' => $person_id]);
		}

		// TODOLATER: Put this check after the permissions check, so as not to leak any information
		// about who is related to whom, but in such a way that the $relative references don't break
		// things. When we do that, testRemoveRelativeAsOther will need to be updated to look for a
		// redirect to / instead of person view page.
		$relative = collection($this->UserCache->read('RelatedTo', $person_id))->firstMatch(['_joinData.person_id' => $relative_id]);
		if (empty($relation)) {
			$relative = collection($this->UserCache->read('Relatives', $person_id))->firstMatch(['_joinData.relative_id' => $relative_id]);
			if (empty($relative)) {
				$this->Flash->info(__('This person is already not related to you.'));
				return $this->redirect(['action' => 'view', 'person' => $person_id]);
			}
		}

		// We must do other permission checks here, because we allow non-logged-in users to remove
		// through email links
		$code = $this->request->getQuery('code');
		if ($code) {
			// Authenticate the hash code
			if (!$this->_checkHash([$relative->_joinData->id, $relative->_joinData->person_id, $relative->_joinData->relative_id, $relative->_joinData->created], $code)) {
				$this->Flash->warning(__('The authorization code is invalid.'));
				return $this->redirect(['action' => 'view', 'person' => $person_id]);
			}
		} else {
			// Public functions need an extra check for managers
			if (Configure::read('Perm.is_manager') && empty(array_intersect($this->UserCache->read('AffiliateIDs', $person_id), $this->UserCache->read('ManagedAffiliateIDs')))) {
				Configure::write('Perm.is_manager', false);
			}

			if (!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager') && $person_id != $this->UserCache->currentId() && $relative_id != $this->UserCache->currentId()) {
				$this->Flash->warning(__('You do not have permission to access that page.'));
				return $this->redirect(['action' => 'view', 'person' => $person_id]);
			}
		}

		$person = $this->UserCache->read('Person', $person_id);
		$relative = $this->UserCache->read('Person', $relative_id);
		// TODOLATER: Check for unlink return value, if they change it so it returns success
		// https://github.com/cakephp/cakephp/issues/8196
		$this->People->Relatives->unlink($person, [$relative]);
		$this->Flash->success(__('Removed the relation.'));

		if ($person_id == $this->UserCache->currentId()) {
			if (!$this->_sendMail([
				'to' => $relative,
				'replyTo' => $person,
				'subject' => __('{0} removed your relation', $person->full_name),
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
				'subject' => __('{0} removed your relation', $relative->full_name),
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
				'subject' => __('An administrator removed your relation'),
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

		if (!empty($this->request->params['url']['oauth_token'])) {
			$response = $this->request->session()->read('Twitter.response');
			$this->request->session()->delete('Twitter.response');
			if ($this->request->params['url']['oauth_token'] !== $response['oauth_token']) {
				$this->Flash->warning(__('The oauth token you started with doesn\'t match the one you\'ve been redirected with. Do you have multiple tabs open?'));
				return $this->redirect(['action' => 'preferences']);
			}

			if (!isset($this->request->params['url']['oauth_verifier'])) {
				$this->Flash->warning(__('The oauth verifier is missing so we cannot continue. Did you deny the appliction access?'));
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
					'oauth_verifier' => trim($this->request->params['url']['oauth_verifier']),
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
					'oauth_callback' => Router::url(Router::normalize($this->request->here()), true),
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
				$this->request->session()->write('Twitter.response', $response);
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
		if (!Configure::read('feature.photos')) {
			return;
		}

		$photo = $this->People->Uploads->find()
			->where([
				'person_id' => $this->request->getQuery('person'),
				'type_id IS' => null,
			])
			->first();
		if (!empty($photo)) {
			$this->response->file(Configure::read('App.paths.uploads') . DS . $photo->filename);
			return $this->response;
		}
	}

	public function photo_upload() {
		if (!Configure::read('feature.photos')) {
			throw new MethodNotAllowedException('Uploading of photos is not enabled on this system.');
		}

		// We don't want the Upload behavior here: we're getting an image from the
		// cropping plugin in the browser, not from the standard form upload method.
		if ($this->People->Uploads->hasBehavior('Upload')) {
			$this->People->Uploads->removeBehavior('Upload');
		}

		$temp_dir = Configure::read('App.paths.files') . DS . 'temp';
		if (!is_dir($temp_dir) || !is_writable($temp_dir)) {
			if (Configure::read('Perm.is_admin')) {
				$this->Flash->warning(__('Your temp folder {0} does not exist or is not writable.', $temp_dir));
			} else {
				$this->Flash->warning(__('This system does not appear to be properly configured for photo uploads. Please contact your administrator to have them correct this.'));
			}
			return $this->redirect('/');
		}
		$file_dir = Configure::read('App.paths.uploads');
		if (!is_dir($file_dir) || !is_writable($file_dir)) {
			if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
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

		if ($this->request->is(['patch', 'post', 'put'])) {
			// Extract base64 file for standard data
			$fileBin = file_get_contents($this->request->data['cropped']);
			$mimeType = mime_content_type($this->request->data['cropped']);

			// Check allowed mime type
			if (substr($mimeType, 0, 6) == 'image/') {
				list(, $ext) = explode('/', $mimeType);
				$filename = $person->id . '.' . strtolower($ext);
				file_put_contents(Configure::read('App.paths.uploads') . DS . $filename, $fileBin);

				// Are approvals required?
				$approved = (Configure::read('feature.approve_photos') ? false : true);
				$upload = $this->People->Uploads->patchEntity($upload, array_merge($this->request->data, compact('filename', 'approved')));
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
		if (!Configure::read('feature.photos')) {
			throw new MethodNotAllowedException('Uploading of photos is not enabled on this system.');
		}

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
		if (!Configure::read('feature.photos')) {
			throw new MethodNotAllowedException('Uploading of photos is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('person');
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

		$photo->approved = true;

		if (!$this->People->Uploads->save($photo)) {
			$this->Flash->warning(__('Failed to approve the photo.'));
			return $this->redirect(['action' => 'approve_photos']);
		} else {
			if (!$this->_sendMail([
				'to' => $photo->person,
				'subject' => __('{0} Notification of Photo Approval', Configure::read('organization.name')),
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
		if (!Configure::read('feature.photos')) {
			throw new MethodNotAllowedException('Uploading of photos is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('person');
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

		if ($this->People->Uploads->delete($photo)) {
			if (!$this->_sendMail([
				'to' => $photo->person,
				'subject' => __('{0} Notification of Photo Deletion', Configure::read('organization.name')),
				'template' => 'photo_deleted',
				'sendAs' => 'both',
				'viewVars' => ['person' => $photo->person],
			])) {
				$this->Flash->warning(__('Error sending email to {0}.', $photo->person->full_name));
			}
		}
	}

	public function document() {
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is not enabled on this system.');
		}

		try {
			$document = $this->People->Uploads->get($this->request->getQuery('document'), [
				'contain' => ['People', 'UploadTypes']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		}

		$this->response->type(Configure::read('new_mime_types'));
		$f = new File($document->filename);
		$this->response->file(Configure::read('App.paths.uploads') . DS . $document->filename, [
			'name' => $document->filename,
			'download' => !in_array($f->ext(), Configure::read('no_download_extensions')),
		]);
		return $this->response;
	}

	public function document_upload() {
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is not enabled on this system.');
		}

		$file_dir = Configure::read('App.paths.uploads');
		if (!is_dir($file_dir) || !is_writable($file_dir)) {
			if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
				$this->Flash->warning(__('Your uploads folder {0} does not exist or is not writable.', $file_dir));
			} else {
				$this->Flash->warning(__('This system does not appear to be properly configured for document uploads. Please contact your administrator to have them correct this.'));
			}
			return $this->redirect('/');
		}

		$id = $this->request->getQuery('person');
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

		$affiliates = $this->_applicableAffiliateIDs();
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

		if ($this->request->is('post')) {
			// Add some configuration that the upload behaviour will use
			$filename = $person->id . '_' . md5(mt_rand());
			$this->People->Uploads->behaviors()->get('Upload')->config([
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

			$upload = $this->People->Uploads->patchEntity($upload, $this->request->data);

			if ($this->People->Uploads->save($upload)) {
				$this->Flash->success(__('Document saved, you will receive an email when it has been approved.'));
				return $this->redirect(['action' => 'view', 'person' => $person->id]);
			} else {
				$this->Flash->warning(__('Failed to save your document.'));
			}
		} else {
			$upload->type_id = $this->request->getQuery('type');
		}

		$this->set(compact('person', 'types', 'upload'));
	}

	public function approve_documents() {
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is not enabled on this system.');
		}

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
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is not enabled on this system.');
		}

		try {
			$document = $this->People->Uploads->get($this->request->getQuery('document'), [
				'contain' => ['People' => [Configure::read('Security.authModel')], 'UploadTypes']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$document = $this->People->Uploads->patchEntity($document, $this->request->data);
			if ($this->People->Uploads->save($document)) {
				$this->Flash->success(__('Approved document.'));

				if (!$this->_sendMail([
					'to' => $document->person,
					'subject' => __('{0} Notification of Document Approval', Configure::read('organization.name')),
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
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is not enabled on this system.');
		}

		try {
			$document = $this->People->Uploads->get($this->request->getQuery('document'), [
				'contain' => ['People' => [Configure::read('Security.authModel')], 'UploadTypes']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$document = $this->People->Uploads->patchEntity($document, $this->request->data);
			if ($this->People->Uploads->save($document)) {
				$this->Flash->success(__('Updated document.'));

				if (!$this->_sendMail([
					'to' => $document->person,
					'subject' => __('{0} Notification of Document Update', Configure::read('organization.name')),
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
		} else {
			$this->request->data = $document;
		}
		$this->set(compact('document'));
		$this->render('approve_document');
	}

	public function delete_document() {
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		try {
			$document = $this->People->Uploads->get($this->request->getQuery('document'), [
				'contain' => ['People' => [Configure::read('Security.authModel')], 'UploadTypes']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid document.'));
			return $this->redirect('/');
		}

		if (!$this->People->Uploads->delete($document)) {
			$this->Flash->warning(__('The document could not be deleted. Please, try again.'));
			return $this->redirect(['action' => 'approve_documents']);
		}

		if ($document->person_id != $this->UserCache->currentId()) {
			if (!$this->_sendMail([
				'to' => $document->person,
				'subject' => __('{0} Notification of Document Deletion', Configure::read('organization.name')),
				'template' => 'document_deleted',
				'sendAs' => 'both',
				'viewVars' => array_merge([
					'person' => $document->person,
				], $this->request->data, compact('document')),
			]))
			{
				$this->Flash->warning(__('Error sending email to {0}.', $document->person->full_name));
			}
		}
	}

	public function nominate() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		if ($this->request->is('post')) {
			if (empty($this->request->data['badge'])) {
				$this->Flash->warning(__('You must select a badge!'));
			} else {
				return $this->redirect(['action' => 'nominate_badge', 'badge' => $this->request->data['badge']]);
			}
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$conditions = [
			'Badges.active' => true,
			'Badges.affiliate_id IN' => $affiliates,
		];
		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
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
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$badge_id = $this->request->getQuery('badge');
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

		if (!$badge->active) {
			$this->Flash->info(__('Inactive badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}
		if ($badge->visibility == BADGE_VISIBILITY_ADMIN && !(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}
		if ($badge->category != 'nominated' && ($badge->category != 'assigned' || !(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')))) {
			$this->Flash->info(__('This badge must be earned, not granted.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}
		$this->set(compact('badge'));
		$this->Configuration->loadAffiliate($badge->affiliate_id);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->_handlePersonSearch(['badge']);
	}

	public function nominate_badge_reason() {
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$badge_id = $this->request->getQuery('badge');
		if (!$badge_id) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}
		$person_id = $this->request->getQuery('person');
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

		if (!$badge->active) {
			$this->Flash->info(__('Inactive badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}
		if ($badge->visibility == BADGE_VISIBILITY_ADMIN && !(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) {
			$this->Flash->info(__('Invalid badge.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}
		if ($badge->category != 'nominated' && ($badge->category != 'assigned' || !(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')))) {
			$this->Flash->info(__('This badge must be earned, not granted.'));
			return $this->redirect(['controller' => 'Badges', 'action' => 'index']);
		}
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
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('badge', 'person', 'affiliates'));

		if ($this->request->is('post')) {
			$data = [
				'reason' => $this->request->data['reason'],
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
							'subject' => __('{0} New Badge Awarded', Configure::read('organization.name')),
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
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
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
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('badge');
		try {
			$badges_table = TableRegistry::get('BadgesPeople');
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

		$link = $badges_table->patchEntity($link, [
			'approved' => true,
			'approved_by_id' => $this->UserCache->currentId(),
		]);

		if (!$badges_table->save($link)) {
			$this->Flash->warning(__('Failed to approve the badge.'));
			return $this->redirect(['action' => 'approve_badges']);
		} else {
			if ($link['badge']['visibility'] != BADGE_VISIBILITY_ADMIN) {
				// Inform the nominator
				if (!$this->_sendMail([
					'to' => $link['nominated_by'],
					'subject' => __('{0} Notification of Badge Approval', Configure::read('organization.name')),
					'template' => 'badge_nomination_approved',
					'sendAs' => 'both',
					'viewVars' => ['link' => $link, 'person' => $link->person, 'badge' => $link->badge, 'nominator' => $link->nominated_by],
				]))
				{
					$this->Flash->warning(__('Error sending email to {0}.', $link->nominated_by->full_name));
				}

				// Inform the recipient
				if (!$this->_sendMail([
					'to' => $link['person'],
					'subject' => __('{0} New Badge Awarded', Configure::read('organization.name')),
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
		if (!Configure::read('feature.badges')) {
			throw new MethodNotAllowedException('Badges are not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('badge');
		try {
			$badges_table = TableRegistry::get('BadgesPeople');
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

		if (!$badges_table->delete($link)) {
			$this->Flash->warning(__('The badge could not be deleted. Please, try again.'));
			return $this->redirect(['action' => 'approve_badges']);
		} else {
			if ($link['badge']['visibility'] != BADGE_VISIBILITY_ADMIN) {
				if ($link['approved']) {
					// Inform the badge holder
					if (!$this->_sendMail([
						'to' => $link['person'],
						'subject' => __('{0} Notification of Badge Deletion', Configure::read('organization.name')),
						'template' => 'badge_deleted',
						'sendAs' => 'both',
						'viewVars' => ['person' => $link->person, 'badge' => $link->badge, 'comment' => $this->request->data['comment']],
					])
					) {
						$this->Flash->warning(__('Error sending email to {0}.', $link->person->full_name));
					}
				} else {
					// Inform the nominator
					if (!$this->_sendMail([
						'to' => $link['nominated_by'],
						'subject' => __('{0} Notification of Badge Rejection', Configure::read('organization.name')),
						'template' => 'badge_nomination_rejected',
						'sendAs' => 'both',
						'viewVars' => ['person' => $link->person, 'badge' => $link->badge, 'nominator' => $link->nominated_by, 'comment' => $this->request->data['comment']],
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
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('person');
		$dependencies = $this->People->dependencies($id, ['Affiliates', 'Groups', 'Relatives', 'Related', 'Skills', 'Settings', 'Subscriptions']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this person, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect('/');
		}

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

		if ($this->People->delete($person)) {
			$this->Flash->success(__('The person has been deleted.'));
		} else if ($person->errors('delete')) {
			$this->Flash->warning(current($person->errors('delete')));
		} else {
			$this->Flash->warning(__('The person could not be deleted. Please, try again.'));
		}

		return $this->redirect('/');
	}

	public function act_as() {
		$act_as = $this->request->getQuery('person');
		if ($act_as) {
			if (!$this->UserCache->read('Person', $act_as)) {
				$this->Flash->info(__('Invalid person.'));
			} else if ($act_as == $this->UserCache->realId()) {
				$this->request->session()->delete('Zuluru.act_as_id');
				$this->request->session()->delete('Zuluru.act_as_temporary');
				$this->Flash->success(__('You are now acting as yourself.'));
			} else if (Configure::read('Perm.is_admin') && in_array(GROUP_ADMIN, $this->UserCache->read('GroupIDs', $act_as))) {
				$this->Flash->warning(__('Administrators cannot act as other administrators.'));
			} else if (!Configure::read('Perm.is_admin') && Configure::read('Perm.is_manager') && in_array(GROUP_MANAGER, $this->UserCache->read('GroupIDs', $act_as))) {
				$this->Flash->warning(__('Managers cannot act as other managers.'));
			} else {
				$this->request->session()->write('Zuluru.act_as_id', $act_as);
				$this->Flash->success(__('You are now acting as {0}.', $this->UserCache->read('Person.full_name', $act_as)));
			}
			return $this->redirect('/');
		}

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
		$affiliates = $this->_applicableAffiliates();
		$this->set(compact('affiliates'));
		$this->_handlePersonSearch();
	}

	public function rule_search() {
		list($params, $url) = $this->_extractSearchParams();
		if (!$this->_handleRuleSearch($params, $url)) {
			return $this->redirect(['action' => 'rule_search']);
		}
	}

	public function league_search() {
		list($params, $url) = $this->_extractSearchParams();
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
		$affiliates = $this->_applicableAffiliates();
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
		if (count($leagues == 1)) {
			$leagues = reset($leagues);
		}
		$this->set(compact('leagues'));

		$this->_handleRuleSearch($params, $url);
	}

	public function inactive_search() {
		list($params, $url) = $this->_extractSearchParams();
		$params['affiliate_id'] = array_keys($this->_applicableAffiliates());
		if (!empty($params) || !Configure::read('feature.affiliates')) {
			$params['rule'] = "NOT(COMPARE(TEAM_COUNT('today') > '0'))";
		}
		if (!Configure::read('feature.affiliates')) {
			$params['affiliate_id'] = 1;
		}

		$this->_handleRuleSearch($params, $url);
	}

	protected function _handleRuleSearch($params, $url) {
		if ($this->request->is('ajax')) {
			$this->viewBuilder()->className('Ajax.Ajax');
		}

		$affiliates = $this->_applicableAffiliates();
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

				if ($this->request->is('csv')) {
					$this->response->download('Search results.csv');
					$this->set('people', $query->contain(['Related'])->toArray());
					$this->render('rule_search');
				} else {
					if (Configure::read('feature.badges')) {
						$badge_obj = $this->moduleRegistry->load('Badge');
						$query->contain(['Badges' => [
							'queryBuilder' => function (Query $q) use ($badge_obj) {
								return $q->where([
									'BadgesPeople.approved' => true,
									'Badges.visibility IN' => $badge_obj->visibility(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'), BADGE_VISIBILITY_HIGH),
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
		$affiliates = $this->_applicableAffiliateIDs(true);
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
		$id = $this->request->getQuery('person');

		try {
			$person = $this->People->get($id, [
				// We don't need to contain Relatives here; those will be handled in the updateAll calls
				'contain' => [Configure::read('Security.authModel'), 'Affiliates', 'Skills', 'Groups', 'Related', 'Settings']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['action' => 'list_new']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect(['action' => 'list_new']);
		}
		if ($person->status != 'new') {
			$this->Flash->info(__('That account has already been approved.'));
			return $this->redirect(['action' => 'list_new']);
		}

		$duplicates = $this->People->find('duplicates', compact('person'))
			->contain(['Affiliates', 'Skills', 'Groups', 'Related', 'Settings']);

		if ($this->request->is(['patch', 'post', 'put'])) {
			if (empty($this->request->data['disposition'])) {
				$this->Flash->info(__('You must select a disposition for this account.'));
			} else {
				if (strpos($this->request->data['disposition'], ':') !== false) {
					list($disposition, $dup_id) = explode(':', $this->request->data['disposition']);
					$duplicate = collection($duplicates)->firstMatch(['id' => $dup_id]);
					if ($duplicate) {
						if ($this->_approve($person, $disposition, $duplicate)) {
							return $this->redirect(['action' => 'list_new']);
						}
						// If this fails, we've messed up the duplicate record. Re-read the duplicates to reset it.
						$duplicates = $this->People->find('duplicates', compact('person'))
							->contain(['Affiliates', 'Skills', 'Groups', 'Related', 'Settings']);
					} else {
						$this->Flash->info(__('You have selected an invalid user!'));
					}
				} else {
					if ($this->_approve($person, $this->request->data['disposition'])) {
						return $this->redirect(['action' => 'list_new']);
					}
				}
			}
		}

		$user_model = Configure::read('Security.authModel');
		$users_table = TableRegistry::get($user_model);
		$activated = $users_table->activated($person);

		$this->set(compact('person', 'duplicates', 'activated'));
	}

	protected function _approve(Person $person, $disposition, Person $duplicate = null) {
		$delete = $save = null;

		// First, take whatever steps are required to prepare the data for saving and/or deleting.
		// Also prepare the options for sending the notification email, if any.
		switch($disposition) {
			case 'approved':
				$person->status = 'active';
				$save = $person;
				$fail_message = __('Couldn\'t save new member activation');

				$mail_opts = [
					'subject' => __('{0} {1} Activation for {2}', Configure::read('organization.name'),
						empty($person->user_id) ? __('Profile') : __('Account'),
						empty($person->user_id) ? $person->full_name : $person->user_name
					),
					'template' => 'account_approved',
				];
				break;

			/** @noinspection PhpMissingBreakStatementInspection */
			case 'delete_duplicate':
				$mail_opts = [
					'subject' => __('{0} Account Update', Configure::read('organization.name')),
					'template' => 'account_delete_duplicate',
				];
				// Intentionally fall through to the next option

			case 'delete':
				$delete = $person;
				break;

			// This is basically the same as the delete duplicate, except
			// that some old information (e.g. user ID) is preserved
			case 'merge_duplicate':
				$duplicate->merge($person);
				$save = $duplicate;
				$delete = $person;
				$fail_message = __('Couldn\'t save new member information');

				$mail_opts = [
					'subject' => __('{0} Account Update', Configure::read('organization.name')),
					'template' => 'account_merge_duplicate',
				];
				break;
		}

		// TODODATABASE: User and person records may be in separate databases, so we need a transaction for each
		//$user_transaction = new DatabaseTransaction($users_table);
		if (!$this->People->connection()->transactional(function () use ($save, $delete, $fail_message) {
			// If we are both deleting and saving, that's a merge operation, and we will want to migrate all
			// records that aren't part of the in-memory record.
			if ($save && $delete) {
				// For anything that we have in memory, we must skip doing a direct query
				$ignore = [];
				foreach ($save->visibleProperties() as $prop) {
					if ($save->accessible($prop) && (is_array($delete->$prop))) {
						$ignore[] = Inflector::camelize($prop);
					}
				}

				$associations = $this->People->associations();

				foreach ($associations->type('BelongsToMany') as $association) {
					if (!in_array($association->name(), $ignore)) {
						$foreign_key = $association->foreignKey();
						$conditions = [$foreign_key => $delete->id];
						$association_conditions = $association->conditions();
						if (!empty($association_conditions)) {
							$conditions += $association_conditions;
						}
						$association->junction()->updateAll([$foreign_key => $save->id], $conditions);
					}

					// BelongsToMany associations also create HasMany associations for the join tables.
					// Ignore them when we get there.
					$ignore[] = $association->junction()->alias();
				}

				foreach ($associations->type('HasMany') as $association) {
					if (!in_array($association->name(), $ignore)) {
						$foreign_key = $association->foreignKey();
						$conditions = [$foreign_key => $delete->id];
						$association_conditions = $association->conditions();
						if (!empty($association_conditions)) {
							$conditions += $association_conditions;
						}
						$association->target()->updateAll([$foreign_key => $save->id], $conditions);
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
		$this->viewBuilder()->layout('vcf');
		$id = $this->request->getQuery('person');
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->set(compact('person'));
		$this->response->download("{$person->full_name}.vcf");
		$this->set($this->_connections($id));
	}

	// This function takes the parameter the old-fashioned way, to try to be more third-party friendly
	public function ical($id) {
		$this->viewBuilder()->layout('ical');
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
		if (empty($person->settings) || !$person->settings[0]->value) {
			throw new GoneException();
		}

		$team_ids = $this->UserCache->read('TeamIDs', $id);

		if (!empty($team_ids)) {
			$games = TableRegistry::get('Games')
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
		$this->response->download("$id.ics");
		$this->RequestHandler->ext = 'ics';
	}

	public function registrations() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$id = $this->request->getQuery('person') ?: $this->UserCache->currentId();
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}
		$affiliates = $this->_applicableAffiliateIDs(true);

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
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$id = $this->request->getQuery('person') ?: $this->UserCache->currentId();
		$affiliates = $this->_applicableAffiliateIDs(true);

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

		$this->set(compact('person', 'affiliates'));
	}

	public function teams() {
		$id = $this->request->getQuery('person') ?: $this->UserCache->currentId();
		$person = $this->UserCache->read('Person', $id);
		if (empty($person)) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->set(compact('person'));
		$this->set('teams', array_reverse($this->UserCache->read('AllTeams', $id)));
	}

	public function waivers() {
		$id = $this->request->getQuery('person') ?: $this->UserCache->currentId();
		$affiliates = $this->_applicableAffiliateIDs(true);
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

		if ($id == $this->UserCache->currentId()) {
			$waivers = [];
			foreach ($affiliates as $affiliate) {
				$signed_names = array_unique(collection($this->UserCache->read('WaiversCurrent'))->match(['affiliate_id' => $affiliate])->extract('name')->toArray());
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
