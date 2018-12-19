<?php
namespace App\Policy;

use App\Model\Entity\Contact;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class ContactPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.contacts')) {
			return false;
		}

		$this->blockAnonymous($identity);
		$this->blockLockedExcept($identity, $action, ['message']);
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
