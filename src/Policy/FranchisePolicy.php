<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Core\UserCache;
use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Franchise;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class FranchisePolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.franchises')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canEdit(IdentityInterface $identity, Franchise $franchise) {
		return $identity->isManagerOf($franchise) || $this->isOwnerOf($franchise);
	}

	public function canDelete(IdentityInterface $identity, Franchise $franchise) {
		return $identity->isManagerOf($franchise);
	}

	public function canAdd_team(IdentityInterface $identity, Franchise $franchise) {
		return $this->isOwnerOf($franchise);
	}

	public function canRemove_team(IdentityInterface $identity, Franchise $franchise) {
		return $identity->isManagerOf($franchise) || $this->isOwnerOf($franchise);
	}

	public function canAdd_owner(IdentityInterface $identity, Franchise $franchise) {
		return $identity->isManagerOf($franchise) || $this->isOwnerOf($franchise);
	}

	public function canRemove_owner(IdentityInterface $identity, ContextResource $resource) {
		$franchise = $resource->resource();
		$people = $resource->people;

		if (count($people) == 1) {
			throw new ForbiddenRedirectException(__('You cannot remove the only owner of a franchise!'),
				['controller' => 'Franchises', 'action' => 'view', 'franchise' => $franchise->id], 'warning');
		}

		return $identity->isManagerOf($franchise) || $this->isOwnerOf($franchise);
	}

	private function isOwnerOf(Franchise $franchise) {
		return in_array($franchise->id, UserCache::getInstance()->read('FranchiseIDs'));
	}

}
