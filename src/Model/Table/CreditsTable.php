<?php
namespace App\Model\Table;

use App\Core\UserCache;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

/**
 * Credits Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Affiliates
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $Payments
 */
class CreditsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('credits');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');
		$this->addBehavior('Muffin/Footprint.Footprint', [
			'events' => [
				'Model.beforeSave' => [
					'created_person_id' => 'new',
				],
			],
			'propertiesMap' => [
				'created_person_id' => '_footprint.person.id',
			],
		]);

		$this->belongsTo('Affiliates', [
			'foreignKey' => 'affiliate_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Payments', [
			'foreignKey' => 'payment_id',
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

			->numeric('amount')
			->notEmptyString('amount')
			->add('amount', 'valid', ['rule' => ['comparison', '>', 0], 'message' => __('{0} amounts must be positive.', __('Credit'))])

			->numeric('amount_used')
			->allowEmptyString('amount_used')

			->allowEmptyString('notes')

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
		$rules->add($rules->existsIn(['affiliate_id'], 'Affiliates'));
		$rules->add($rules->existsIn(['person_id'], 'People'));
		$rules->add($rules->existsIn(['created_person_id'], 'People'));

		$rules->add(function (EntityInterface $entity, array $options) {
			return $entity->amount_used === null || round($entity->amount_used, 2) <= round($entity->amount, 2);
		}, 'validAmountUsed', [
			'errorField' => 'amount_used',
		]);

		return $rules;
	}

	/**
	 * Modifies the entity before it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function beforeSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, \ArrayObject $options) {
		if ($entity->isDirty('amount') && $entity->payment) {
			$entity->payment->payment_amount = - $entity->amount;
			$entity->setDirty('payment', true);

			// Since we'll be saving a payment, we need the registration information in the options.
			// But it needs the updated payment details, so this gets ugly. :-(
			$registration = $this->Payments->Registrations->get($entity->payment->registration_id, [
				'contain' => [
					'Payments' => [
						'queryBuilder' => function (Query $q) use ($entity) {
							return $q->where(['Payments.id NOT IN' => [$entity->payment->id, $entity->payment->payment_id]]);
						}
					],
				]
			]);
			$registration->payments[] = $entity->payment;

			if ($entity->payment->payment) {
				$entity->payment->payment->refunded_amount += ($entity->amount - $entity->getOriginal('amount'));
				$entity->payment->setDirty('payment', true);
				$registration->payments[] = $entity->payment->payment;
			}

			$options['registration'] = $registration;
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
	public function afterSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, \ArrayObject $options) {
		UserCache::getInstance()->clear('Credits', $entity->person_id);
	}

	/**
	 * Perform post-processing to ensure that any required event-type-specific steps are taken.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterDelete(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, \ArrayObject $options) {
		UserCache::getInstance()->clear('Credits', $entity->person_id);
	}

	public function affiliate($id) {
		try {
			return $this->field('affiliate_id', ['Credits.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
