<?php
namespace App\Policy;

use Authorization\IdentityInterface;

class AllPolicy extends AppPolicy {

	public function canClear_cache(IdentityInterface $identity, $controller) {
		return $this->allowAdmin($identity) ? true : false;
	}

}
