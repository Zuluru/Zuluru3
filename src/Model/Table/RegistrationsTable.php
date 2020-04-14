<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\Number;
use Cake\I18n\FrozenTime;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Controller\AppController;
use App\Core\UserCache;
use App\Core\ModuleRegistry;

/**
 * Registrations Model
 *
 * @property \Cake\ORM\Association\BelongsTo $People
 * @property \Cake\ORM\Association\BelongsTo $Events
 * @property \Cake\ORM\Association\BelongsTo $Prices
 * @property \Cake\ORM\Association\HasMany $Payments
 * @property \Cake\ORM\Association\HasMany $Responses
 */
class RegistrationsTable extends AppTable {

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('registrations');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');
		$this->addBehavior('Muffin/Footprint.Footprint', [
			'events' => [
				'Model.beforeSave' => [
					'person_id' => 'new',
				],
			],
			'propertiesMap' => [
				'person_id' => '_footprint.person.id',
			],
		]);

		$this->belongsTo('People', [
			'foreignKey' => 'person_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Events', [
			'foreignKey' => 'event_id',
			'joinType' => 'INNER',
		]);
		$this->belongsTo('Prices', [
			'foreignKey' => 'price_id',
			'joinType' => 'INNER',
		]);

		$this->hasMany('Payments', [
			'foreignKey' => 'registration_id',
			'dependent' => true,
		]);
		$this->hasMany('Responses', [
			'foreignKey' => 'registration_id',
			'dependent' => true,
			'saveStrategy' => 'replace',
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

			->allowEmpty('payment')

			->allowEmpty('notes')

			->numeric('deposit_amount')
			->allowEmpty('deposit_amount')

			->dateTime('reservation_expires')
			->allowEmpty('reservation_expires', function($context) {
				return (!empty($context['data']['payment']) && $context['data']['payment'] != 'Reserved') || (Configure::read('registration.reservation_time') == 0);
			})

			->boolean('delete_on_expiry')
			->allowEmpty('delete_on_expiry')

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
		$rules->add($rules->existsIn(['person_id'], 'People'));
		$rules->add($rules->existsIn(['event_id'], 'Events'));
		$rules->add($rules->existsIn(['price_id'], 'Prices'));

		$rules->add(function (EntityInterface $entity, Array $options) {
			if ($entity->person_id == UserCache::getInstance()->currentId() &&
				$entity->has('price') && $entity->price->has('canRegister') && !$entity->price->canRegister['allowed'])
			{
				return __('Select a valid option.');
			}
			return true;
		}, 'canRegister', [
			'errorField' => 'price_id',
		]);

		$rules->add(function (EntityInterface $entity, Array $options) {
			// This should happen only when creating or editing a registration, not if they're ever moved to or from the waiting list, etc.
			if ($entity->payment_type == 'Deposit') {
				if ($entity->deposit_amount < $entity->price->minimum_deposit) {
					return __('A minimum deposit of {0} is required.', Number::currency($entity->price->minimum_deposit));
				} else if ($entity->deposit_amount >= $entity->price->total) {
					return __('This deposit exceeds the total cost of {0}.', Number::currency($entity->price->total));
				}
			}
			return true;
		}, 'validDeposit', [
			'errorField' => 'deposit_amount',
		]);

		return $rules;
	}

	/**
	 * Modifies the entity before it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return bool
	 */
	public function beforeSave(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// For any registration where the price point selection has changed, calculate the total price.
		if (!$entity->has('price') || $entity->price_id != $entity->price->id) {
			$entity->price = $this->Prices->get($entity->price_id);
			$entity->setDirty('price', false);
		}
		if ($entity->isDirty('price_id')) {
			$entity->total_amount = $entity->price->total;
		}

		if ($entity->isDirty('payments')) {
			// Determine the "payment" status based on the new set of payments.
			if (end($entity->payments)->payment_amount < 0) {
				if ($entity->mark_refunded) {
					$entity->payment = 'Cancelled';
				}
				// Any other refund leaves the payment status unchanged.
			} else {
				if ($entity->total_payment == $entity->total_amount) {
					$entity->payment = 'Paid';
				} else if (count($entity->payments) == 1) {
					$entity->payment = 'Deposit';
				} else {
					$entity->payment = 'Partial';
				}
			}
		} else {
			if (!$entity->price->allow_deposit) {
				$entity->deposit_amount = 0;
			} else if (($entity->price->deposit_only || $entity->payment_type == 'Deposit') && $entity->price->fixed_deposit) {
				$entity->deposit_amount = $entity->price->minimum_deposit;
			}

			if ($entity->isNew() || !empty($options['from_expire_reservations'])) {
				// Check whether we've gone past the cap
				$roster_designation = $this->People->field('roster_designation', ['People.id' => $entity->person_id]);
				$cap = $options['event']->cap($roster_designation);

				// If we're expiring a reservation, we don't want to count it against the cap,
				// but the status hasn't been changed yet, so it will be counted below.
				$offset = !empty($options['from_expire_reservations']) ? 1 : 0;

				if ($cap != CAP_UNLIMITED && $options['event']->count($roster_designation) >= $cap + $offset) {
					$entity->payment = 'Waiting';
				} else if ($entity->total_amount == 0) {
					$entity->payment = 'Paid';
				} else if ($entity->price->allow_reservations && empty($options['from_expire_reservations'])) {
					$entity->payment = 'Reserved';
					$entity->reservation_expires = FrozenTime::now()->addMinutes($entity->price->reservation_duration);
				} else {
					$entity->payment = 'Unpaid';
				}
			}
		}

		return $this->preProcess($entity, $options, $entity->isNew() ? null : $entity->getOriginal('payment'), $entity->payment);
	}

	/**
	 * Perform post-processing to ensure that any required event-type-specific steps are taken.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterSave(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		$this->postProcess($entity, $options, $entity->isNew() ? null : $entity->getOriginal('payment'), $entity->payment);
	}

	/**
	 * Perform additional operations before it is deleted.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeDelete event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity to be deleted
	 * @param \ArrayObject $options The options passed to the delete method
	 * @return bool
	 */
	public function beforeDelete(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		$options['event'] = $entity->event;
		return $this->preProcess($entity, $options, $entity->payment, null);
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
		$options['event'] = $entity->event;
		$this->postProcess($entity, $options, $entity->payment, null);
	}

	// TODOLATER: If there are no $this references, move it to the Registration entity?
	/**
	 * @param EntityInterface $registration
	 * @param ArrayObject $options
	 * @param $original_payment
	 * @param $new_payment
	 * @return bool
	 */
	private function preProcess(EntityInterface $registration, ArrayObject $options, $original_payment, $new_payment) {
		if (!array_key_exists('event_obj', $options)) {
			$options['event_obj'] = ModuleRegistry::getInstance()->load("EventType:{$options['event']->event_type->type}");
		}

		$reserved = Configure::read('registration_reserved');
		$paid = Configure::read('registration_paid');
		$was_registered = ($original_payment != null);
		$now_registered = ($new_payment != null);
		$was_reserved = in_array($original_payment, $reserved);
		$now_reserved = in_array($new_payment, $reserved);
		$was_paid = in_array($original_payment, $paid);
		$now_paid = in_array($new_payment, $paid);

		if (!$was_registered && $now_registered && !$options['event_obj']->beforeRegister($options['event'], $registration, $options)) {
			return false;
		}

		if (!$was_reserved && $now_reserved && !$options['event_obj']->beforeReserve($options['event'], $registration, $options)) {
			return false;
		}

		if (!$was_paid && $now_paid && !$options['event_obj']->beforePaid($options['event'], $registration, $options)) {
			return false;
		}

		if ($was_paid && !$now_paid && !$options['event_obj']->beforeUnpaid($options['event'], $registration, $options)) {
			return false;
		}

		if ($was_reserved && !$now_reserved && !$options['event_obj']->beforeUnreserve($options['event'], $registration, $options)) {
			return false;
		}

		if ($was_registered && !$now_registered && !$options['event_obj']->beforeUnregister($options['event'], $registration, $options)) {
			return false;
		}

		if ($was_registered == $now_registered && $was_reserved == $now_reserved && $was_paid == $now_paid && !$options['event_obj']->beforeReregister($options['event'], $registration, $options)) {
			return false;
		}

		if ($now_registered && !empty($registration->responses) && $registration->isDirty('responses')) {
			// Manually add the event id to all of the responses
			foreach ($registration->responses as $response) {
				$this->Responses->patchEntity($response, ['event_id' => $options['event']->id]);
			}
		}

		return true;
	}

	private function postProcess(EntityInterface $registration, ArrayObject $options, $original_payment, $new_payment) {
		if (!array_key_exists('event_obj', $options)) {
			$options['event_obj'] = ModuleRegistry::getInstance()->load("EventType:{$options['event']->event_type->type}");
		}

		$reserved = Configure::read('registration_reserved');
		$paid = Configure::read('registration_paid');
		$was_registered = ($original_payment != null);
		$now_registered = ($new_payment != null);
		$was_reserved = in_array($original_payment, $reserved);
		$now_reserved = in_array($new_payment, $reserved);
		$was_paid = in_array($original_payment, $paid);
		$now_paid = in_array($new_payment, $paid);

		if (!$was_registered && $now_registered) {
			$options['event_obj']->afterRegister($options['event'], $registration, $options);
		}

		if (!$was_reserved && $now_reserved) {
			$options['event_obj']->afterReserve($options['event'], $registration, $options);
		}

		if (!$was_paid && $now_paid) {
			$options['event_obj']->afterPaid($options['event'], $registration, $options);
		}

		if ($was_paid && !$now_paid) {
			$options['event_obj']->afterUnpaid($options['event'], $registration, $options);
		}

		if ($was_reserved && !$now_reserved) {
			$options['event_obj']->afterUnreserve($options['event'], $registration, $options);
		}

		if ($was_registered && !$now_registered) {
			$options['event_obj']->afterUnregister($options['event'], $registration, $options);
		}

		if ($was_registered == $now_registered && $was_reserved == $now_reserved && $was_paid == $now_paid) {
			$options['event_obj']->afterReregister($options['event'], $registration, $options);
		}

		UserCache::getInstance()->_deleteRegistrationData($registration->person_id);
	}

	public function expireReservations() {
		$expired = $this->find()
			->contain([
				'Events' => ['EventTypes'],
				'Prices',
				'People' => [Configure::read('Security.authModel')],
				'Responses',
			])
			->where([
				'payment' => 'Reserved',
				'reservation_expires <' => FrozenTime::now(),
			])
			->toArray();

		foreach ($expired as $registration) {
			if ($registration->delete_on_expiry) {
				// This reservation was created from the waiting list, and should be deleted
				$this->delete($registration, ['from_expire_reservations' => true]);
			} else {
				$registration->payment = 'Unpaid';
				$this->save($registration, ['event' => $registration->event, 'from_expire_reservations' => true]);
			}

			AppController::_sendMail([
				'to' => $registration->person,
				'subject' => function() { return __('{0} Reservation expired', Configure::read('organization.name')); },
				'template' => 'reservation_expired',
				'sendAs' => 'both',
				'viewVars' => [
					'event' => $registration->event,
					'registration' => $registration,
					'person' => $registration->person,
				],
			]);
		}
	}

	public function affiliate($id) {
		try {
			return $this->Events->affiliate($this->field('event_id', ['Registrations.id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
