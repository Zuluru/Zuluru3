<?php
namespace App\Policy;

use App\Model\Entity\Facility;
use Authorization\IdentityInterface;

class FacilityPolicy extends AppPolicy {

	public function canClosed(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canAdd_field(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Facility $facility) {
		return $identity->isManagerOf($facility);
	}

	public function canOpen(IdentityInterface $identity, Facility $facility) {
		return $identity->isManagerOf($facility);
	}

	public function canClose(IdentityInterface $identity, Facility $facility) {
		return $identity->isManagerOf($facility);
	}

	public function canDelete(IdentityInterface $identity, Facility $facility) {
		return $identity->isManagerOf($facility);
	}

}
