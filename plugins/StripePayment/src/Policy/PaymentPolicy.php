<?php
namespace StripePayment\Policy;

use App\Policy\AppPolicy;
use Authorization\IdentityInterface;

class PaymentPolicy extends AppPolicy {

	public function canSuccess(IdentityInterface $identity, $controller) {
		return true;
	}

}
