<?php
namespace App\Policy;

use App\Model\Entity\Affiliate;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class AffiliatePolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.affiliates')) {
			return false;
		}

		parent::before($identity, $resource, $action);

		return $this->allowAdmin($identity);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return false;
	}

	public function canView(IdentityInterface $identity, Affiliate $affiliate) {
		return false;
	}

	public function canAdd(IdentityInterface $identity, Affiliate $affiliate) {
		return false;
	}

	public function canEdit(IdentityInterface $identity, Affiliate $affiliate) {
		return false;
	}

	public function canDelete(IdentityInterface $identity, Affiliate $affiliate) {
		return false;
	}

	public function canAdd_manager(IdentityInterface $identity, Affiliate $affiliate) {
		return false;
	}

	public function canRemove_manager(IdentityInterface $identity, Affiliate $affiliate) {
		return false;
	}

	public function canSelect(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canView_all(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canEdit_settings(IdentityInterface $identity, Affiliate $affiliate) {
		return $identity->isManagerOf($affiliate);
	}

	public function canAutocomplete(IdentityInterface $identity, Affiliate $affiliate) {
		return $identity->isManagerOf($affiliate);
	}

}
