<?php
namespace App\Policy;

use App\Model\Entity\Contact;
use Authorization\IdentityInterface;
use Authorization\Policy\ResultInterface;
use Cake\Core\Configure;

class ContactPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.contacts')) {
			return false;
		}

		$result = $this->blockAnonymous($identity);
		if ($result === false || $result instanceof ResultInterface) {
			return $result;
		}

		$result = $this->blockLockedExcept($identity, $action, ['message']);
		if ($result === false || $result instanceof ResultInterface) {
			return $result;
		}
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canAdd(IdentityInterface $identity, Contact $contact) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Contact $contact) {
		return $identity->isManagerOf($contact);
	}

	public function canDelete(IdentityInterface $identity, Contact $contact) {
		return $identity->isManagerOf($contact);
	}

	public function canMessage(IdentityInterface $identity, $controller) {
		return true;
	}

}
