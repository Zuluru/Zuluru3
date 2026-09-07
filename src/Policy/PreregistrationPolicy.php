<?php
namespace App\Policy;

use App\Model\Entity\Preregistration;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class PreregistrationPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		return parent::before($identity, $resource, $action);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canDelete(IdentityInterface $identity, Preregistration $preregistration) {
		return $identity->isManagerOf($preregistration) || $identity->isMe($preregistration);
	}

}
