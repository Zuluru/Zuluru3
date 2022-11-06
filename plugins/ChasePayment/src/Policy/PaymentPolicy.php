<?php
namespace ChasePayment\Policy;

use App\Policy\AppPolicy;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class PaymentPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canFrom_email(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canFrom_email_confirmation(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

}
