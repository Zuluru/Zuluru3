<?php
namespace App\Policy;

use App\Model\Entity\Holiday;
use Authorization\IdentityInterface;

class HolidayPolicy extends AppPolicy {

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canAdd(IdentityInterface $identity, Holiday $holiday) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Holiday $holiday) {
		return $identity->isManagerOf($holiday);
	}

	public function canDelete(IdentityInterface $identity, Holiday $holiday) {
		return $identity->isManagerOf($holiday);
	}

}
