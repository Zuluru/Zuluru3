<?php
namespace App\Policy;

use App\Model\Entity\Price;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class PricePolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canDelete(IdentityInterface $identity, Price $price) {
		return $identity->isManagerOf($price);
	}

}
