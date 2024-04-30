<?php
namespace App\Model\Table;

use App\Authorization\ContextResource;
use App\Model\Entity\Team;
use App\Model\Entity\TeamsPerson;
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
use App\Model\Table\LeaguesTable;
use InvalidArgumentException;

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
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('teams');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

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

		// TODO: The "TeamsPeople" association is used in some unit tests.
		$this->hasMany('TeamsPeople', [
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
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator
			->numeric('id')
			->allowEmptyString('id', null, 'create')

			->requirePresence('name', 'create', __('Team name must not be blank.'))
			->notEmptyString('name', __('The name cannot be blank.'))
			->setProvider('zuluru', \App\Validation\Zuluru::class)
			->add('name', 'unique', ['provider' => 'zuluru', 'rule' => ['teamUnique'], 'message' => __('There is already a team by that name in this league.')])

			->url('url', __('Enter a valid URL, or leave blank.'))
			->allowEmptyString('website')

			->notEmptyString('shirt_colour', __('Shirt colour must not be blank.'))

			->numeric('home_field_id')
			->allowEmptyString('home_field_id')

			->numeric('region_preference_id')
			->allowEmptyString('region_preference_id')

			->boolean('open_roster')
			->allowEmptyString('open_roster')

			->numeric('rating')
			->allowEmptyString('rating')

			->boolean('track_attendance')
			->allowEmptyString('track_attendance')

			->numeric('attendance_reminder', __('Please enter a number.'))
			->range('attendance_reminder', [-1, 5], __('Attendance reminders can be sent a maximum of five days in advance.'))
			->allowEmptyString('attendance_reminder')

			->numeric('attendance_summary', __('Please enter a number.'))
			->range('attendance_summary', [-1, 5], __('Attendance summaries can be sent a maximum of five days in advance.'))
			->allowEmptyString('attendance_summary')

			->numeric('attendance_notification', __('Please enter a number.'))
			->range('attendance_notification', [-1, 14], __('Attendance notifications can be sent starting a maximum of 14 days in advance.'))
			->allowEmptyString('attendance_notification')

			->numeric('initial_rating')
			->allowEmptyString('initial_rating')

			->allowEmptyString('short_name')

			->allowEmptyString('logo')

			->numeric('initial_seed')
			->notEmptyString('initial_seed')

			->numeric('seed')
			->notEmptyString('seed')

			->allowEmptyString('flickr_user')

			->allowEmptyString('flickr_set')

			->boolean('flickr_ban')
			->allowEmptyString('flickr_ban')

			->allowEmptyString('twitter_user')

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
	public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker {
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
	public function afterSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if ($entity->isDirty('division_id') && !empty($entity->getOriginal('division_id'))) {
			$this->Divisions->clearCache($entity->getOriginal('division_id'));
		}
		if ($entity->division_id) {
			$this->Divisions->clearCache($entity->division_id);
		}

		if ($entity->isDirty('name') || $entity->isDirty('division_id') || $entity->isDirty('website')
			|| $entity->isDirty('shirt_colour') || $entity->isDirty('logo') || $entity->isDirty('short_name')
			|| $entity->isDirty('track_attendance')
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
	public function afterDelete(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if ($entity->division_id) {
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

	public function findOpenRoster(Query $query, array $options) {
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
					'Leagues' => [
						'Affiliates',
					],
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

		@usort($teams, [LeaguesTable::class, 'compareLeagueAndDivision']);

		return $teams;
	}

	public static function compareRoster($a, $b, $options = []) {
		if (is_a($a, TeamsPerson::class)) {
			$roster_a = $a;
			$person_a = $a->person;
			$roster_b = $b;
			$person_b = $b->person;
		} else {
			$roster_a = $a->_joinData;
			$person_a = $a;
			$roster_b = $b->_joinData;
			$person_b = $b;
		}

		static $rosterMap = null;
		if ($rosterMap == null) {
			$rosterMap = array_flip(array_keys(Configure::read('options.roster_role')));
		}

		$include_gender = $options['include_gender'] ?? false;

		// Sort eligible from non-eligible
		if ($person_a->has('can_add') && $person_b->has('can_add')) {
			if ($person_a->can_add === true && $person_b->can_add !== true) {
				return -1;
			} else if ($person_a->can_add !== true && $person_b->can_add === true) {
				return 1;
			}
		}

		if ($roster_a->status == ROSTER_APPROVED && $roster_b->status != ROSTER_APPROVED) {
			return -1;
		} else if ($roster_a->status != ROSTER_APPROVED && $roster_b->status == ROSTER_APPROVED) {
			return 1;
		} else if ($rosterMap[$roster_a->role] > $rosterMap[$roster_b->role]) {
			return 1;
		} else if ($rosterMap[$roster_a->role] < $rosterMap[$roster_b->role]) {
			return -1;
		} else if ($include_gender && $person_a->roster_designation < $person_b->roster_designation) {
			return 1;
		} else if ($include_gender && $person_a->roster_designation > $person_b->roster_designation) {
			return -1;
		}

		return PeopleTable::comparePerson($person_a, $person_b);
	}

	public function affiliate($id) {
		// Teams may be unassigned
		try {
			$division = $this->division($id);
			if ($division) {
				return $this->Divisions->affiliate($division);
			} else {
				return $this->field('affiliate_id', [$this->aliasField('id') => $id]);
			}
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function division($id) {
		try {
			return $this->field('division_id', [$this->aliasField('id') => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function sport($id) {
		// Teams may be unassigned
		try {
			$division = $this->field('division_id', [$this->aliasField('id') => $id]);
			if ($division) {
				$league = $this->Divisions->field('league_id', ['Divisions.id' => $division]);
				return $this->Divisions->Leagues->field('sport', ['Leagues.id' => $league]);
			} else {
				return current(array_keys(Configure::read('options.sport')));
			}
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
