<?php
namespace App\Model\Table;

use App\Model\Entity\Event;
use App\Model\Entity\Price;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Core\ModuleRegistry;
use InvalidArgumentException;

/**
 * Events Model
 *
 * @property \Cake\ORM\Association\BelongsTo $EventTypes
 * @property \Cake\ORM\Association\BelongsTo $Questionnaires
 * @property \Cake\ORM\Association\BelongsTo $Divisions
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\BelongsTo $Seasons
 * @property \Cake\ORM\Association\HasMany $Preregistrations
 * @property \Cake\ORM\Association\HasMany $Prices
 * @property \Cake\ORM\Association\HasMany $Registrations
 * @property \Cake\ORM\Association\HasMany $Responses
 */
class EventsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('events');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');
		$this->addBehavior('Translate', [
			'strategyClass' => \Cake\ORM\Behavior\Translate\ShadowTableStrategy::class,
			'fields' => ['name', 'description'],
		]);

		$this->belongsTo('EventTypes', [
			'foreignKey' => 'event_type_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Questionnaires', [
			'foreignKey' => 'questionnaire_id',
		]);
		$this->belongsTo('Divisions', [
			'foreignKey' => 'division_id',
		]);
		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Preregistrations', [
			'foreignKey' => 'event_id',
			'dependent' => true,
		]);
		$this->hasMany('Prices', [
			'foreignKey' => 'event_id',
			'dependent' => true,
		]);
		$this->hasMany('Registrations', [
			'foreignKey' => 'event_id',
		]);

		$this->belongsToMany('Predecessor', [
			'className' => 'Events',
			'joinTable' => 'events_connections',
			'targetForeignKey' => 'connected_event_id',
			'saveStrategy' => 'replace',
			'conditions' => ['connection' => EVENT_PREDECESSOR],
			'sort' => ['Predecessor.event_type_id', 'Predecessor.open', 'Predecessor.close', 'Predecessor.id'],
		]);
		$this->belongsToMany('Successor', [
			'className' => 'Events',
			'joinTable' => 'events_connections',
			'targetForeignKey' => 'connected_event_id',
			'saveStrategy' => 'replace',
			'conditions' => ['connection' => EVENT_SUCCESSOR],
			'sort' => ['Successor.event_type_id', 'Successor.open', 'Successor.close', 'Successor.id'],
		]);
		$this->belongsToMany('Alternate', [
			'className' => 'Events',
			'joinTable' => 'events_connections',
			'targetForeignKey' => 'connected_event_id',
			'saveStrategy' => 'replace',
			'conditions' => ['connection' => EVENT_ALTERNATE],
			'sort' => ['Alternate.event_type_id', 'Alternate.open', 'Alternate.close', 'Alternate.id'],
		]);
		$this->belongsToMany('PredecessorTo', [
			'className' => 'Events',
			'joinTable' => 'events_connections',
			'foreignKey' => 'connected_event_id',
			'targetForeignKey' => 'event_id',
			'saveStrategy' => 'replace',
			'conditions' => ['connection' => EVENT_PREDECESSOR],
			'sort' => ['PredecessorTo.event_type_id', 'PredecessorTo.open', 'PredecessorTo.close', 'PredecessorTo.id'],
		]);
		$this->belongsToMany('SuccessorTo', [
			'className' => 'Events',
			'joinTable' => 'events_connections',
			'foreignKey' => 'connected_event_id',
			'targetForeignKey' => 'event_id',
			'saveStrategy' => 'replace',
			'conditions' => ['connection' => EVENT_SUCCESSOR],
			'sort' => ['SuccessorTo.event_type_id', 'SuccessorTo.open', 'SuccessorTo.close', 'SuccessorTo.id'],
		]);
		$this->belongsToMany('AlternateTo', [
			'className' => 'Events',
			'joinTable' => 'events_connections',
			'foreignKey' => 'connected_event_id',
			'targetForeignKey' => 'event_id',
			'saveStrategy' => 'replace',
			'conditions' => ['connection' => EVENT_ALTERNATE],
			'sort' => ['AlternateTo.event_type_id', 'AlternateTo.open', 'AlternateTo.close', 'AlternateTo.id'],
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

			->requirePresence('name', 'create')
			->notEmptyString('name', __('The name cannot be blank.'))

			->requirePresence('affiliate_id', 'create')
			->notEmptyString('affiliate_id', __('You must select a valid affiliate.'))

			->requirePresence('description', 'create')
			->notEmptyString('description', __('The description cannot be blank.'))

			->requirePresence('event_type_id', 'create')
			->notEmptyString('event_type_id', __('You must select a valid event type.'))

			->numeric('open_cap', __('You must enter a number for the open cap.'))
			->requirePresence('open_cap', 'create', __('You must enter a number for the open cap.'))
			->notEmptyString('open_cap', __('You must enter a number for the open cap.'))
			->add('open_cap', 'range', [
				'rule' => ['comparison', '>=', -1],
				'message' => __('The open cap cannot be less than -1.'),
			])

			->numeric('women_cap', __('You must enter a number for the women cap.'))
			->requirePresence('women_cap', 'create', __('You must enter a number for the women cap.'))
			->notEmptyString('women_cap', __('You must enter a number for the women cap.'))
			->add('women_cap', 'range', [
				'rule' => ['comparison', '>=', CAP_COMBINED],
				'message' => __('The women cap cannot be less than -2.'),
			])

			->boolean('multiple', __('Indicate whether multiple registrations are allowed.'))
			->allowEmptyString('multiple')

			->allowEmptyString('custom')

			;

		return $validator;
	}

	// TODOLATER: How can we move these into the derived classes? See SpiritEntries...

	/**
	 * Custom validation rules for generic-type events.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationGeneric(Validator $validator) {
		return $this->validationDefault($validator);
	}

	/**
	 * Custom validation rules for individual-type events.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationIndividual(Validator $validator) {
		return $this->validationDefault($validator);
	}

	/**
	 * Custom validation rules for membership-type events.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationMembership(Validator $validator) {
		$validator = $this->validationDefault($validator);

		$validator
			->date('membership_begins', ['ymd'], __('You must select a valid beginning date.'))
			->notEmptyDate('membership_begins', __('You must select a valid beginning date.'))

			->date('membership_ends', ['ymd'], __('You must select a valid ending date.'))
			->notEmptyDate('membership_ends', __('You must select a valid ending date.'))

			->notEmptyString('membership_type', __('You must select a valid membership type.'))

			;

		return $validator;
	}

	/**
	 * Custom validation rules for team-type events.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationTeam(Validator $validator) {
		$validator = $this->validationDefault($validator);

		$validator
			->boolean('ask_status')
			->requirePresence('ask_status', 'create')
			->notEmptyString('ask_status')

			->boolean('ask_attendance')
			->requirePresence('ask_attendance', 'create')
			->notEmptyString('ask_attendance')

			;

		if (Configure::read('feature.region_preference')) {
			$validator
				->boolean('ask_region')
				->requirePresence('ask_region', 'create')
				->notEmptyString('ask_region');
		}

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
		$rules->add($rules->existsIn(['event_type_id'], 'EventTypes', __('You must select a valid event type.')));
		$rules->add($rules->existsIn(['questionnaire_id'], 'Questionnaires', __('You must select a valid questionnaire.')));
		$rules->add($rules->existsIn(['division_id'], 'Divisions', __('You must select a valid division.')));
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates', __('You must select a valid affiliate.')));

		$rules->add(function (EntityInterface $entity, array $options) {
			/** @var Event $entity */
			$others = $this->find()
				->where([
					'name' => $entity->name,
					'event_type_id' => $entity->event_type_id,
					'close >=' => $entity->open,
					'open <=' => $entity->close,
				]);

			if (!$entity->isNew()) {
				$others->where(['id !=' => $entity->id]);
			}

			return $others->count() === 0;
		}, 'validName', [
			'errorField' => 'name',
			'message' => __('There is already an event of this type open at the same time with the same name.'),
		]);

		$rules->add(function (EntityInterface $entity, array $options) {
			/** @var Event $entity */
			if ($entity->open_cap == CAP_UNLIMITED) {
				return true;
			}

			return collection($entity->prices ?? [])
				->every(function (Price $price) {
					return $price->allow_reservations;
				});
		}, 'validReservation', [
			'errorField' => 'open_cap',
			'message' => __('Any event with a cap must enable reservations on all price points.'),
		]);

		return $rules;
	}

	/**
	 * Handle custom field serialization before trying to write anything out.
	 *
	 * @param CakeEvent $cakeEvent Unused
	 * @param ArrayObject $data The data record being patched in
	 * @param ArrayObject $options Unused
	 */
	public function beforeMarshal(CakeEvent $cakeEvent, ArrayObject $data, ArrayObject $options) {
		if (!empty($data['event_type']['type'])) {
			$type = $data['event_type']['type'];
		} else if (!empty($data['event_type_id'])) {
			$type = $this->EventTypes->field('type', ['EventTypes.id' => $data['event_type_id']]);
		}
		if (isset($type)) {
			$event_obj = ModuleRegistry::getInstance()->load("EventType:{$type}");
			// Pull out the custom configuration fields
			$custom = [];
			foreach ($event_obj->configurationFields() as $field) {
				if ($data->offsetExists($field)) {
					$custom[$field] = $data[$field];
				}
			}
			$data['custom'] = serialize($custom);
		}
	}

	/**
	 * Perform post-processing to ensure that any required event-type-specific steps are taken.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// There might be unpaid registrations now to be moved to the waiting list, or if the cap has risen we can
		// invite some people fom the waiting list.
		$entity->processWaitingList();
	}

	public function findOpen(Query $query, array $options) {
		$query->where([
			'OR' => [
				// TODO: Use variable intervals for extended vs regular
				'Events.open <' => FrozenDate::now()->addDays(180),
				'Events.close >' => FrozenDate::now()->subDays(30),
			],
		]);

		if (!empty($options['affiliates'])) {
			$query->andWhere(['Events.affiliate_id IN' => $options['affiliates']]);
		}

		return $query;
	}

	public function findMembership(Query $query, array $options) {
		$membership_types = $this->EventTypes->find('list', [
			'conditions' => ['type' => 'membership'],
			'keyField' => 'id',
			'valueField' => 'id',
		])->toArray();

		if (empty($membership_types)) {
			// A query that will never return anything
			return $query->andWhere([0 => 1]);
		}

		return $query->andWhere(['event_type_id IN' => $membership_types]);
	}

	public function findNotMembership(Query $query, array $options) {
		$membership_types = $this->EventTypes->find('list', [
			'conditions' => ['type' => 'membership'],
			'keyField' => 'id',
			'valueField' => 'id',
		])->toArray();

		if (empty($membership_types)) {
			return $query;
		}

		return $query->andWhere(['NOT' => ['event_type_id IN' => $membership_types]]);
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['Events.id' => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

	public function division($id) {
		try {
			return $this->field('division_id', ['Events.id' => $id]);
		} catch (RecordNotFoundException|InvalidArgumentException $ex) {
			return null;
		}
	}

}
