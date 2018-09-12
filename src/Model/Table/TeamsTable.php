<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Routing\Router;
use Cake\Validation\Validator;
use App\Core\UserCache;

/**
 * Teams Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Divisions
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\BelongsTo $Fields
 * @property \Cake\ORM\Association\BelongsTo $Regions
 * @property \Cake\ORM\Association\HasMany $Attendances
 * @property \Cake\ORM\Association\HasMany $Incidents
 * @property \Cake\ORM\Association\HasMany $Notes
 * @property \Cake\ORM\Association\HasMany $ScoreEntries
 * @property \Cake\ORM\Association\HasMany $SpiritEntries
 * @property \Cake\ORM\Association\HasMany $Stats
 * @property \Cake\ORM\Association\HasMany $TeamEvents
 * @property \Cake\ORM\Association\BelongsToMany $Franchises
 * @property \Cake\ORM\Association\BelongsToMany $Facilities
 * @property \Cake\ORM\Association\BelongsToMany $People
 */
class TeamsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('teams');
		$this->displayField('name');
		$this->primaryKey('id');

		$this->addBehavior('Trim');

		$this->belongsTo('Divisions', [
			'foreignKey' => 'division_id',
		]);
		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
		]);
		$this->belongsTo('Fields', [
			'foreignKey' => 'home_field_id',
		]);
		$this->belongsTo('Regions', [
			'foreignKey' => 'region_preference_id',
		]);

		$this->hasMany('Attendances', [
			'foreignKey' => 'team_id',
			'dependent' => true,
		]);
		$this->hasMany('Incidents', [
			'foreignKey' => 'team_id',
			'dependent' => false,
		]);
		$this->hasMany('Notes', [
			'foreignKey' => 'team_id',
			'dependent' => true,
		]);
		$this->hasMany('ScoreEntries', [
			'foreignKey' => 'team_id',
			'dependent' => true,
			// Required to delete from score_entries
			'cascadeCallbacks' => true,
		]);
		$this->hasMany('SpiritEntries', [
			'foreignKey' => 'team_id',
			'dependent' => true,
		]);
		$this->hasMany('Stats', [
			'foreignKey' => 'team_id',
			'dependent' => true,
		]);
		$this->hasMany('TeamEvents', [
			'foreignKey' => 'team_id',
			'dependent' => true,
		]);

		$this->belongsToMany('Franchises', [
			'foreignKey' => 'team_id',
			'targetForeignKey' => 'franchise_id',
			'joinTable' => 'franchises_teams',
			'through' => 'FranchisesTeams',
			'saveStrategy' => 'append',
			'sort' => 'Franchises.name',
		]);
		$this->belongsToMany('Facilities', [
			'foreignKey' => 'team_id',
			'targetForeignKey' => 'facility_id',
			'joinTable' => 'teams_facilities',
			'through' => 'TeamsFacilities',
			'saveStrategy' => 'replace',
			'sort' => 'TeamsFacilities.rank',
		]);
		$this->belongsToMany('People', [
			'foreignKey' => 'team_id',
			'targetForeignKey' => 'person_id',
			'joinTable' => 'teams_people',
			'through' => 'TeamsPeople',
			'saveStrategy' => 'append',
			'sort' => ['People.last_name', 'People.first_name'],
			// Required for the TeamsPeopleTable::beforeDelete function to be called
			'cascadeCallbacks' => true,
		]);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator) {
		$validator
			->numeric('id')
			->allowEmpty('id', 'create')

			->requirePresence('name', 'create', __('Team name must not be blank.'))
			->notEmpty('name', __('The name cannot be blank.'))
			->provider('zuluru', 'App\Validation\Zuluru')
			->add('name', 'unique', ['provider' => 'zuluru', 'rule' => ['teamUnique'], 'message' => __('There is already a team by that name in this league.')])

			->url('url', __('Enter a valid URL, or leave blank.'))
			->allowEmpty('website')

			->notEmpty('shirt_colour', __('Shirt colour must not be blank.'))

			->numeric('home_field_id')
			->allowEmpty('home_field_id')

			->numeric('region_preference_id')
			->allowEmpty('region_preference_id')

			->boolean('open_roster')
			->allowEmpty('open_roster')

			->numeric('rating')
			->allowEmpty('rating')

			->boolean('track_attendance')
			->allowEmpty('track_attendance')

			->numeric('attendance_reminder', __('Please enter a number.'))
			->range('attendance_reminder', [-1, 5], __('Attendance reminders can be sent a maximum of five days in advance.'))
			->allowEmpty('attendance_reminder')

			->numeric('attendance_summary', __('Please enter a number.'))
			->range('attendance_summary', [-1, 5], __('Attendance summaries can be sent a maximum of five days in advance.'))
			->allowEmpty('attendance_summary')

			->numeric('attendance_notification', __('Please enter a number.'))
			->range('attendance_notification', [-1, 14], __('Attendance notifications can be sent starting a maximum of 14 days in advance.'))
			->allowEmpty('attendance_notification')

			->numeric('initial_rating')
			->allowEmpty('initial_rating')

			->allowEmpty('short_name')

			->allowEmpty('logo')

			->numeric('initial_seed')
			->notEmpty('initial_seed')

			->numeric('seed')
			->notEmpty('seed')

			->allowEmpty('flickr_user')

			->allowEmpty('flickr_set')

			->boolean('flickr_ban')
			->allowEmpty('flickr_ban')

			->allowEmpty('twitter_user')

			;

		return $validator;
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules) {
		$rules->add($rules->existsIn(['division_id'], 'Divisions', __('You must select a valid division.')));
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));
		return $rules;
	}

	/**
	 * Perform additional operations after it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterSave(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if ($entity->dirty('division_id') && !empty($entity->getOriginal('division_id'))) {
			$this->Divisions->clearCache($entity->getOriginal('division_id'));
		}
		if (!empty($entity->division_id)) {
			$this->Divisions->clearCache($entity->division_id);
		}

		if ($entity->dirty('name') || $entity->dirty('division_id') || $entity->dirty('website')
			|| $entity->dirty('shirt_colour') || $entity->dirty('logo') || $entity->dirty('short_name')
			|| $entity->dirty('track_attendance')
		) {
			if (!$entity->has('people')) {
				$this->loadInto($entity, ['People']);
			}
			$user_cache = UserCache::getInstance();
			foreach ($entity->people as $person) {
				$user_cache->_deleteTeamData($person->id);
			}
		}
	}

	/**
	 * Perform additional operations after it is deleted.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterDelete event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was deleted
	 * @param \ArrayObject $options The options passed to the delete method
	 * @return void
	 */
	public function afterDelete(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if (!empty($entity->division_id)) {
			$this->Divisions->clearCache($entity->division_id);
		}

		if (Configure::read('feature.franchises') && $options->offsetExists('registration')) {
			$franchise_id = $options['event_obj']->extractAnswer($options['registration']->responses, FRANCHISE_ID_CREATED);

			if ($franchise_id) {
				// Delete the franchise record too, if it's empty now
				$franchise = $this->Franchises->get($franchise_id, [
					'contain' => ['Teams', 'People']
				]);
				if (empty($franchise->teams)) {
					$this->Franchises->delete($franchise);
					foreach ($franchise->people as $person) {
						UserCache::getInstance()->_deleteFranchiseData($person->id);
					}
				}
			}
		}
	}

	public function findOpenRoster(Query $query, Array $options) {
		return $query
			->contain(['Divisions' => ['Leagues' => ['Affiliates']]])
			->where([
				'Leagues.affiliate_id IN' => $options['affiliates'],
				'Teams.open_roster' => true,
				'OR' => [
					'Divisions.is_open',
					'Divisions.open >' => FrozenDate::now(),
				],
			]);
	}

	public function readByPlayerId($id, $open = true) {
		// Check for invalid users
		if ($id === null) {
			return [];
		}

		$conditions = [
			'Teams.division_id IS NOT' => null,
		];
		if ($open) {
			$conditions['OR'] = [
				'Divisions.is_open' => true,
				'Divisions.open >' => FrozenDate::now(),
			];
		}

		$teams = $this->find()
			->contain([
				'Divisions' => [
					'Leagues' => ['Affiliates'],
					'Days',
				],
				'Franchises',
			])
			->matching('People', function (Query $q) use ($id) {
				return $q
					->where(['People.id' => $id]);
			})
			->where($conditions)
			->toArray();

		usort($teams, ['App\Model\Table\LeaguesTable', 'compareLeagueAndDivision']);

		return $teams;
	}

	public static function compareRoster($a, $b, $options = []) {
		static $rosterMap = null;
		if ($rosterMap == null) {
			$rosterMap = array_flip(array_keys(Configure::read('options.roster_role')));
		}

		if (!is_a($a, 'App\Model\Entity\Person') || !is_a($b, 'App\Model\Entity\Person')) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}

		// If there is no request, we're running in CLI mode (i.e. a shell task), and nothing we do there cares about gender sorting
		if (array_key_exists('team', $options) && Router::getRequest()) {
			$include_gender = $options['team']->display_gender;
		} else {
			$include_gender = false;
		}

		// Sort eligible from non-eligible
		if ($a->has('can_add') && $b->has('can_add')) {
			if ($a->can_add === true && $b->can_add !== true) {
				return -1;
			} else if ($a->can_add !== true && $b->can_add === true) {
				return 1;
			}
		}

		if ($a->_joinData->status == ROSTER_APPROVED && $b->_joinData->status != ROSTER_APPROVED) {
			return -1;
		} else if ($a->_joinData->status != ROSTER_APPROVED && $b->_joinData->status == ROSTER_APPROVED) {
			return 1;
		} else if ($rosterMap[$a->_joinData->role] > $rosterMap[$b->_joinData->role]) {
			return 1;
		} else if ($rosterMap[$a->_joinData->role] < $rosterMap[$b->_joinData->role]) {
			return -1;
		} else if ($include_gender && $a->roster_designation < $b->roster_designation) {
			return 1;
		} else if ($include_gender && $a->roster_designation > $b->roster_designation) {
			return -1;
		} else if ($a->last_name > $b->last_name) {
			return 1;
		} else if ($a->last_name < $b->last_name) {
			return -1;
		} else if ($a->first_name > $b->first_name) {
			return 1;
		} else {
			return -1;
		}
	}

	public function canEditRoster($team_id, $this_is_admin, $is_team_manager, $allow_captain = true) {
		if (is_a($team_id, 'App\Model\Entity\Team')) {
			$team = $team_id;
			$team_id = $team->id;
		}

		// Find some information that will help inform the final decision on permissions
		if (isset($team)) {
			$division_id = $team->division_id;
		} else {
			try {
				$division_id = $this->field('division_id', ['id' => $team_id]);
			} catch (RecordNotFoundException $ex) {
				return false;
			}
		}
		if ($division_id) {
			$affiliate_id = $this->Divisions->affiliate($division_id);
			$roster_deadline = $this->Divisions->field('roster_deadline', ['id' => $division_id]);
			if ($roster_deadline === null) {
				$roster_deadline = $this->Divisions->field('close', ['id' => $division_id]);
			}
		} else {
			if (isset($team)) {
				$affiliate_id = $team->affiliate_id;
			} else {
				$affiliate_id = $this->field('affiliate_id', ['id' => $team_id]);
			}
		}

		$user_cache = UserCache::getInstance();
		$on_team = in_array($team_id, $user_cache->read('TeamIDs'));
		$this_is_captain = $allow_captain && in_array($team_id, $user_cache->read('OwnedTeamIDs'));
		$this_is_coordinator = in_array($team_id, collection($user_cache->read('Divisions'))->extract('teams.{*}.id')->toList());
		$is_team_manager &= in_array($affiliate_id, $user_cache->read('ManagedAffiliateIDs'));

		// Any admin, manager or coordinator can edit, if they are not on the team
		if (!$on_team) {
			return $this_is_admin || $is_team_manager || $this_is_coordinator;
		}

		// Any admin, manager, coordinator or captain on the team can edit, if the roster deadline hasn't passed (or isn't set)
		if ($this_is_admin || $is_team_manager || $this_is_coordinator || $this_is_captain) {
			if (!isset($roster_deadline) || !$roster_deadline->isPast()) {
				return true;
			}
			$msg = __('The roster deadline for this division has already passed.');
			if (!$this_is_captain) {
				$msg .= ' ' . __('As a member of this team, your permissions have been restricted to prevent accidental misuse.');
			}
			return $msg;
		}

		return false;
	}

	public function affiliate($id) {
		// Teams may be unassigned
		try {
			$division = $this->field('division_id', ['id' => $id]);
			if ($division) {
				return $this->Divisions->affiliate($division);
			} else {
				return $this->field('affiliate_id', ['id' => $id]);
			}
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function sport($id) {
		// Teams may be unassigned
		try {
			$division = $this->field('division_id', ['id' => $id]);
			if ($division) {
				$league = $this->Divisions->field('league_id', ['id' => $division]);
				return $this->Divisions->Leagues->field('sport', ['id' => $league]);
			} else {
				return current(array_keys(Configure::read('options.sport')));
			}
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
