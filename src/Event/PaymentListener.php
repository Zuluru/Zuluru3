<?php
/**
 * Implementation of payment event listeners.
 */

namespace App\Event;

use App\Controller\AppController;
use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use App\Model\Entity\Registration;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventListenerInterface;

class PaymentListener implements EventListenerInterface {

	public function implementedEvents(): array {
		return [
			'Model.Payment.afterSave' => 'afterSave',
		];
	}

	public function afterSave(CakeEvent $cakeEvent, Payment $payment, Registration $registration, Event $event) {
		// Don't send any notifications about refunds or credits, those are handled elsewhere.
		// Also, don't notify about updates to payments, e.g. when we are refunding one.
		if (!$payment->isNew() || in_array($payment->payment_type, ['Refund', 'Credit'])) {
			return;
		}

		// Only notify admins about online payments
		if (Configure::read('registration.notify_admin') && in_array($payment->payment_method, ['Online', 'Credit Redeemed'])) {
			AppController::_sendMail([
				'to' => [Configure::read('email.admin_email') => Configure::read('email.admin_name')],
				'subject' => function() use ($payment) {
					return __('Payment received');
				},
				'template' => 'payment_received_admin',
				'sendAs' => 'both',
				'viewVars' => [
					'event' => $event,
					'registration' => $registration,
					'payment' => $payment,
					'person' => $registration->person,
				],
			]);
		}

		// Notify the registrant about any payment
		if (Configure::read('registration.notify_registrant')) {
			AppController::_sendMail([
				'to' => $registration->person,
				'subject' => function() use ($payment) {
					return __('{0} Payment received', Configure::read('organization.name'));
				},
				'template' => 'payment_received_registrant',
				'sendAs' => 'both',
				'viewVars' => [
					'event' => $event,
					'registration' => $registration,
					'payment' => $payment,
					'person' => $registration->person,
				],
			]);
		}
	}

}
