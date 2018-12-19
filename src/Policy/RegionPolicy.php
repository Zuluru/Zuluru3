<?php
namespace App\Policy;

use App\Model\Entity\Region;
use Authorization\IdentityInterface;

class RegionPolicy extends AppPolicy {

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, Region $region) {
		return $identity->isManagerOf($region);
	}

	public function canAdd(IdentityInterface $identity, Region $region) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Region $region) {
		return $identity->isManagerOf($region);
	}

	public function canDelete(IdentityInterface $identity, Region $region) {
		return $identity->isManagerOf($region);
	}

	public function canAdd_game_slots(IdentityInterface $identity, Region $region) {
		return $identity->isManagerOf($region);
	}

}
