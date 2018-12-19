<?php
namespace App\Policy;

use Authorization\IdentityInterface;

class SettingPolicy extends AppPolicy {

	public function canEdit(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canPayment_provider_fields(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

}
