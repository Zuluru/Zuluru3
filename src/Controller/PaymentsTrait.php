<?php
namespace App\Controller;

use Cake\ORM\TableRegistry;

trait PaymentsTrait {

	private function _processPayment($result, $audit, $registration_ids, $debit_ids) {
		$credits_table = TableRegistry::getTableLocator()->get('Credits');

		$errors = [];
		if ($result) {
			$registrations = $this->Registrations->find()
				->contain([
					'People',
					'Events' => [
						'EventTypes',
						'Divisions' => ['Leagues'],
						'Questionnaires' => ['Questions'],
					],
					'Prices',
					'Payments',
					'Responses',
				])
				->where(['Registrations.id IN' => $registration_ids])
				->toArray();
			$this->Configuration->loadAffiliate($registrations[0]->event->affiliate_id);

			// We need another copy of the registrations, to send to the invoice page,
			// so that it will display registration state as it stood before the payment.
			// TODO: Maybe change the invoice page instead?
			$registrations_original = $this->Registrations->find()
				->contain([
					'People',
					'Events' => [
						'EventTypes',
						'Divisions' => ['Leagues'],
					],
					'Prices',
					'Payments',
					'Responses',
				])
				->where(['Registrations.id IN' => $registration_ids])
				->toArray();

			if (!empty($debit_ids)) {
				$debits = $credits_table->find()
					->where(['Credits.id IN' => $debit_ids])
					->toArray();
			} else {
				$debits = [];
			}

			$audit = $this->Registrations->Payments->RegistrationAudits->newEntity($audit);
			if (!$this->Registrations->Payments->RegistrationAudits->save($audit)) {
				$errors[] = __('There was an error updating the audit record in the database. Contact the office to ensure that your information is updated, quoting order #<b>{0}</b>, or you may not be allowed to be added to rosters, etc.', $audit->order_id);
				$this->log(print_r($audit->getErrors(), true));
			}

			foreach ($registrations as $registration) {
				[$cost, $tax1, $tax2] = $registration->paymentAmounts();
				$registration->payments[] = $this->Registrations->Payments->newEntity([
					'registration_audit_id' => $audit->id,
					'payment_method' => 'Online',
					'payment_amount' => $cost + $tax1 + $tax2,
				], ['validate' => 'payment', 'registration' => $registration]);
				$registration->setDirty('payments', true);

				// The registration is also passed as an option, so that the payment rules have easy access to it
				if (!$this->Registrations->save($registration, ['registration' => $registration, 'event' => $registration->event])) {
					$errors[] = __('Your payment was approved, but there was an error updating your payment status in the database. Contact the office to ensure that your information is updated, quoting order #<b>{0}</b>, or you may not be allowed to be added to rosters, etc.', $audit->order_id);
				}
			}

			foreach ($debits as $debit) {
				$debit->amount_used = $debit->amount;
				if (!$credits_table->save($debit)) {
					$errors[] = __('Your payment was approved, but there was an error updating your payment status in the database. Contact the office to ensure that your information is updated, quoting order #<b>{0}</b>, or you may not be allowed to be added to rosters, etc.', $audit->order_id);
				}
			}
		} else {
			$registrations_original = [];
		}
		$this->set(array_merge(compact('result', 'audit', 'errors'), ['registrations' => $registrations_original]));
	}

}
