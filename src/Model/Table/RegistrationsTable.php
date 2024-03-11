<?php
namespace App\Model\Table;

use App\Event\FlashTrait;
use App\Exception\PaymentException;
use App\Http\API;
use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use App\Model\Entity\Registration;
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

	use FlashTrait;

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
			'sort' => 'created',
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
	 * Modifies the entity before rules are run. Updates done in here rely on the earlier games in the set already
	 * having been saved so their ID is available, so they can't be done in beforeMarshal.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeRules event that was fired
	 * @param Registration $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @param mixed $operation The operation (e.g. create, delete) about to be run
	 * @return void
	 */
	public function beforeRules(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options, $operation) {
		if (!$entity->has('price') || $entity->price_id != $entity->price->id) {
			$entity->price = $this->Prices->get($entity->price_id);
			$entity->setDirty('price', false);
		}

		if ($entity->payment_type == 'Deposit' && $entity->price->fixed_deposit) {
			$entity->deposit_amount = $entity->price->minimum_deposit;
		}
	}

	/**
	 * Modifies the entity before it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeSave event that was fired
	 * @param Registration $entity The entity that is going to be saved
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
	 * @param Registration $entity The entity that was saved
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
	 * @param Registration $entity The entity to be deleted
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
	 * @param Registration $entity The entity that was deleted
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

	public function refund(Event $event, Registration $registration, $data) {
		return $this->getConnection()->transactional(function () use ($event, $registration, $data) {
			if (empty($registration->payments)) {
				$this->Flash('warning', __('This registration has no payments recorded. When receiving offline payments, be sure to use the "Add Payment" function, rather than just marking the registration as "Paid".'));
				return false;
			}

			switch ($data['amount_type']) {
				case 'total':
					$refund_amount = $registration->total_payment;
					break;
				case 'prorated':
					$refund_amount = round($registration->total_amount * $data['payment_percent'] / 100, 2);
					break;
				case 'input':
					$refund_amount = $data['payment_amount'];
					break;
			}

			if (array_key_exists('credit_notes', $data)) {
				$credit_notes = $data['credit_notes'];
			} else {
				$credit_notes = null;
			}

			// Check if there's a payment that exactly matches the refund amount
			$payment = collection($registration->payments)->match(['paid' => $refund_amount])->first();
			if ($payment) {
				// The registration is also passed as an option, so that the payment marshaller has easy access to it
				$refund = $this->Payments->newEntity(array_merge($data, [
					'registration_id' => $registration->id,
					'payment_amount' => $refund_amount,
				]), ['validate' => 'refund', 'registration' => $registration]);

				return $this->refundPayment($event, $registration, $payment, $refund, $data['mark_refunded'], $data['online_refund'] ?? false, $credit_notes);
			}

			if ($refund_amount > round(collection($registration->payments)->sumOf('paid'), 2)) {
				$this->Flash('warning', __('This would refund more than the amount paid.'));
				return false;
			}

			// Go through all the payments, refunding them one at a time, until the requested amount has been covered
			foreach ($registration->payments as $payment) {
				$amount = min($refund_amount, $payment->paid);
				$refund_amount -= $amount;

				// The registration is also passed as an option, so that the payment marshaller has easy access to it
				$refund = $this->Payments->newEntity(array_merge($data, [
					'registration_id' => $registration->id,
					'payment_amount' => $amount,
				]), ['validate' => 'refund', 'registration' => $registration]);

				if (!$this->refundPayment($event, $registration, $payment, $refund, $data['mark_refunded'], $data['online_refund'] ?? false, $credit_notes)) {
					if ($payment->getErrors()) {
						foreach ($payment->getErrors() as $errors) {
							foreach ($errors as $error) {
								$this->Flash('warning', $error);
							}
						}
					}

					if ($refund->getErrors()) {
						foreach ($refund->getErrors() as $errors) {
							foreach ($errors as $error) {
								$this->Flash('warning', $error);
							}
						}
					}

					return false;
				}

				if ($refund_amount == 0) {
					// We don't need to refund any more payments to complete the request
					return true;
				}
			}
		});
	}

	public function refundPayment(Event $event, Registration $registration, Payment $payment, Payment $refund, $mark_refunded, $online_refund, $credit_notes = null) {
		// The form has a positive amount to be refunded, but the refund record has a negative amount.
		$payment->refunded_amount = round($payment->refunded_amount - $refund->payment_amount, 2);
		$registration->mark_refunded = $mark_refunded;

		$registration->payments[] = $refund;
		$refund->payment_id = $payment->id;
		$registration->setDirty('payments', true);

		if ($refund->payment_type === 'Credit') {
			if (empty($refund->credits)) {
				$refund->credits = [];
			}
			$refund->credits[] = $this->Payments->Credits->newEntity([
				'affiliate_id' => $event->affiliate_id,
				'person_id' => $registration->person_id,
				'amount' => -$refund->payment_amount,
				'notes' => $credit_notes,
			]);
			$refund->setDirty('credits', true);
		}

		return $this->getConnection()->transactional(function () use ($event, $registration, $payment, $refund, $online_refund) {
			$safe_payment = $registration->payment;

			// The registration is also passed as an option, so that the payment rules have easy access to it
			if (!$this->save($registration, ['registration' => $registration, 'event' => $event])) {
				$this->Flash('warning', __('The refund could not be saved. Please correct the errors below and try again.'));

				if ($payment->getError('payment_amount')) {
					$refund->setErrors(['payment_amount' => $payment->getError('payment_amount')]);
				}

				// Reset the payment status; it might have been changed in beforeSave
				$registration->payment = $safe_payment;
				$registration->clean();

				return false;
			}

			// Not sure why this has to be done, but the one returned from saving the registration seems to be a
			// different object than the one it was before the save.
			$refund = end($registration->payments);

			// Some "refunds" are actually credits. Don't process them online.
			if ($refund->payment_type === 'Refund' && $online_refund && $payment->registration_audit_id) {
				/** @var API $api */
				$api = $this->Payments->RegistrationAudits->getAPI($payment->registration_audit);
				if ($api && $api->canRefund($payment)) {
					try {
						$data = $api->refund($event, $payment, $refund);

						$audit = $this->Payments->RegistrationAudits->newEntity($data);
						if (!$this->Payments->RegistrationAudits->save($audit)) {
							throw new PaymentException(__('Issued the refund online, but failed to save the audit record. ' .
								'Re-issue the refund here but without checking the "{0}" box. ',
								__('Issue refund through online payment provider')
							));
						}

						// We don't use patchEntity here because beforeMarshal currently expects to be given all the data for a new entity
						$refund->registration_audit_id = $audit->id;
						$refund->payment_method = 'Online';

						// The registration is also passed as an option, so that the payment rules have easy access to it
						if (!$this->Payments->save($refund, ['registration' => $registration, 'event' => $registration->event])) {
							throw new PaymentException(__('Issued the refund online, but failed to save the payment record. ' .
								'Re-issue the refund here but without checking the "{0}" box. ',
								__('Issue refund through online payment provider')
							));
						}
					} catch (PaymentException $ex) {
						$this->Flash('error', __('Failed to issue refund through online processor. ' .
							'Refund data was NOT saved. ' .
							'You can try again, or uncheck the "{0}" box and issue the refund manually.',
							__('Issue refund through online payment provider')
						));

						// Reset the payment status; it might have been changed in beforeSave
						$registration->payment = $safe_payment;
						$registration->clean();

						return false;
					}
				}
			}

			AppController::_sendMail([
				'to' => $registration->person,
				'subject' => function() use ($refund) {
					if ($refund->payment_type == 'Refund') {
						return __('{0} Registration refunded', Configure::read('organization.name'));
					} else {
						return __('{0} Registration credited', Configure::read('organization.name'));
					}
				},
				'template' => 'registration_refunded',
				'sendAs' => 'both',
				'viewVars' => [
					'event' => $event,
					'registration' => $registration,
					'refund' => $refund,
					'person' => $registration->person,
				],
			]);

			return true;
		});
	}

	public function affiliate($id) {
		try {
			return $this->Events->affiliate($this->field('event_id', ['Registrations.id' => $id]));
		} catch (RecordNotFoundException $ex) {
			return null;
		}
	}

}
