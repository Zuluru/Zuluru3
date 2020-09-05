<?php
namespace PayPalPayment\Policy;

use App\Policy\AppPolicy;
use Authorization\IdentityInterface;

class PaymentPolicy extends AppPolicy {

	public function canIndex(IdentityInterface $identity, $controller) {
		return true;
	}

}
