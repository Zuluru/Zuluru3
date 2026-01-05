<?php
namespace App\Policy;

use App\Model\Entity\Payment;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class PaymentPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		return parent::before($identity, $resource, $action);
	}

	public function canRefund_payment(IdentityInterface $identity, Payment $payment) {
		if (!$identity->isManagerOf($payment)) {
			return false;
		}

		// Check whether we can even refund this
		if ($payment->payment_amount == $payment->refunded_amount) {
			return new RedirectResult(__('This payment has already been fully refunded.'),
				['action' => 'view', '?' => ['registration' => $payment->registration_id]]);
		}
		if (!in_array($payment->payment_type, Configure::read('payment_payment'))) {
			return new RedirectResult(__('Only payments can be refunded.'),
				['action' => 'view', '?' => ['registration' => $payment->registration_id]]);
		}

		return true;
	}

	public function canCredit_payment(IdentityInterface $identity, Payment $payment) {
		if (!$identity->isManagerOf($payment)) {
			return false;
		}

		// Check whether we can even credit this
		if ($payment->payment_amount == $payment->refunded_amount) {
			return new RedirectResult(__('This payment has already been fully refunded.'),
				['action' => 'view', '?' => ['registration' => $payment->registration_id]]);
		}
		if (!in_array($payment->payment_type, Configure::read('payment_payment'))) {
			return new RedirectResult(__('Only payments can be credited.'),
				['action' => 'view', '?' => ['registration' => $payment->registration_id]]);
		}

		return true;
	}

}
