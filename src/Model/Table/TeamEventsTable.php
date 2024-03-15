<?php
namespace App\Model\Table;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Model\Rule\InConfigRule;

/**
 * TeamEvents Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Teams
 * @property \Cake\ORM\Association\HasMany $Attendances
 * @property \Cake\ORM\Association\HasMany $AttendanceReminderEmails
 * @property \Cake\ORM\Association\HasMany $AttendanceSummaryEmails
 */
class TeamEventsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('team_events');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');
		$this->addBehavior('Trim');
		$this->addBehavior('Formatter', [
			'fields' => [
				'location_street' => 'proper_case_format',
				'location_city' => 'proper_case_format',
			],
		]);

		$this->belongsTo('Teams', [
			'foreignKey' => 'team_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Attendances', [
			'foreignKey' => 'team_event_id',
			'dependent' => true,
			'conditions' => ['team_event_id IS NOT' => null],
		]);
		$this->hasMany('AttendanceReminderEmails', [
			'foreignKey' => 'team_event_id',
			'className' => 'ActivityLogs',
			'dependent' => true,
			'conditions' => ['type' => 'email_event_attendance_reminder'],
		]);
		$this->hasMany('AttendanceSummaryEmails', [
			'foreignKey' => 'team_event_id',
			'className' => 'ActivityLogs',
			'dependent' => true,
			'conditions' => ['type' => 'email_event_attendance_summary'],
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
			->allowEmptyString('id', null, 'create')

			->requirePresence('name', 'create', __('Event name must not be blank.'))
			->notEmptyString('name', __('The name cannot be blank.'))

			->allowEmptyString('description')

			->url('website', __('Enter a valid URL, or leave blank.'))
			->allowEmptyString('website')

			->date('date', __('You must provide a valid date.'))
			->allowEmptyDate('date')

			->time('start', __('You must select a valid start time.'))
			->allowEmptyTime('start')

			->time('end', __('You must select a valid end time.'))
			->allowEmptyTime('end')

			->notEmptyString('location_name', __('Location name must not be blank.'))

			->notEmptyString('location_street', __('You must supply a valid street address.'))

			->notEmptyString('location_city', __('You must supply a city.'))

			->notEmptyString('location_province', __('Select a province/state from the list.'))

			->notEmptyString('repeat_count', __('You must specify a number of events to create.'), function ($context) { return !empty($context['data']['repeat']); })
			->numeric('repeat_count', __('Number of events to create must be numeric.'))
			->range('repeat_count', [1, 100], __('Number of events to create must be between 1 and 100. If you need more than 100, just add a second batch.'))

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
		$rules->add($rules->existsIn(['team_id'], 'Teams'));

		$rules->add(new InConfigRule('provinces'), 'validProvince', [
			'errorField' => 'location_province',
			'message' => __('Select a province/state from the list.'),
		]);

		return $rules;
	}

	public function findSchedule(Query $query, array $options) {
		$query->contain(['Teams']);
		if (!empty($options['teams'])) {
			$query->where(['TeamEvents.team_id IN' => $options['teams']]);
		}
		return $query;
	}

	public function findWithAttendance(Query $query, array $options) {
		$contain = [
			'Attendances' => [
				'queryBuilder' => function (Query $q) use ($options) {
					if (isset($options['people'])) {
						$q->where(['Attendances.person_id IN' => $options['people']]);
					}
					if (isset($options['teams'])) {
						$q->contain(['People'])->where(['Attendances.team_id IN' => $options['teams']]);
					}
					if (isset($options['status'])) {
						$q->where(['Attendances.status IN' => $options['status']]);
					}
					return $q;
				},
			]
		];
		return $query->contain($contain);
	}

	/**
	 * Read the attendance records for an event.
	 * This will also create any missing records, with "unknown" status.
	 *
	 * @param mixed $team The team to read attendance for.
	 * @param mixed $event_id The event id, or null for all team events.
	 * @param bool $attending_only If true, only include attendance for those who are attending
	 * @return mixed List of events with attendance records.
	 *
	 */
	public function readAttendance($team, $event_id = null, $attending_only = false) {
		// We accept either a pre-read team entity with roster info, or just an id
		if (is_numeric($team)) {
			try {
				$team = $this->Teams->get($team, [
					'contain' => ['People'],
				]);
			} catch (RecordNotFoundException $ex) {
				return [];
			} catch (InvalidPrimaryKeyException $ex) {
				return [];
			}
		} else {
			if (!is_a($team, \App\Model\Entity\Team::class) || !$team->has('people')) {
				trigger_error('Team records must include rosters when used with readAttendance', E_USER_ERROR);
			}
		}

		// Make sure that all required records exist
		$event_conditions = ['team_id' => $team->id];
		if ($event_id === null) {
			$events = $this->find('list', [
				'keyField' => 'id',
				'valueField' => 'date',
				'conditions' => ['team_id' => $team->id],
			])->toArray();
			if (empty($events)) {
				return [];
			}
			$attendance_conditions = ['team_event_id IN' => array_keys($events)];
			foreach ($events as $id => $date) {
				$this->createAttendance($team, $id, $date);
			}
		} else {
			$event_conditions['id'] = $event_id;
			$attendance_conditions = ['team_event_id' => $event_id];
			$date = $this->field('date', ['TeamEvents.id' => $event_id]);
			$this->createAttendance($team, $event_id, $date);
		}

		// Re-read whatever is current, including join tables that will be useful in the output
		if ($attending_only) {
			$attendance_conditions['Attendances.status'] = ATTENDANCE_ATTENDING;
		}
		$attendance = $this->find()
			->contain([
				'Attendances' => [
					'People',
					'queryBuilder' => function (Query $q) use ($attendance_conditions) {
						return $q->where($attendance_conditions);
					},
				],
			])
			->where($event_conditions)
			->order('TeamEvents.date');

		if ($event_id === null) {
			return $attendance->toArray();
		} else {
			return $attendance->first();
		}
	}

	public function createAttendance($team, $event_id, $date) {
		// Find event details
		try {
			$event = $this->get($event_id);
		} catch (RecordNotFoundException $ex) {
			return;
		} catch (InvalidPrimaryKeyException $ex) {
			return;
		}
		if ($event->team_id != $team->id) {
			return;
		}

		// Find all attendance records for this event
		$attendance = $this->Attendances->find()
			->where([
				'team_event_id' => $event_id,
			])
			->toArray();

		// Extract list of players on the roster as of this date
		$roster = collection($team->people)->filter(function ($person) use ($date) {
			return $person->_joinData->created < $date->addDay() && $person->_joinData->status == ROSTER_APPROVED;
		})->toArray();

		// Go through the roster and make sure there are records for all players on this date.
		foreach ($roster as $person) {
			$record = collection($attendance)->firstMatch(['person_id' => $person->id]);

			if (empty($record)) {
				// We didn't find any appropriate record, so create a new one
				$attendance_update = $this->Attendances->newEntity([
					'team_id' => $team->id,
					'game_date' => $date,
					'team_event_id' => $event_id,
					'person_id' => $person->id,
					'status' => ATTENDANCE_UNKNOWN,
				]);
				$this->Attendances->save($attendance_update);
			}
		}
	}

	public function affiliate($id) {
		try {
			return $this->Teams->affiliate($this->team($id));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

	public function team($id) {
		try {
			return $this->field('team_id', ['TeamEvents.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
