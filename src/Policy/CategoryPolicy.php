<?php
namespace App\Policy;

use App\Model\Entity\Category;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class CategoryPolicy extends AppPolicy {

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, Category $category) {
		return $identity->isManagerOf($category);
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Category $category) {
		return $identity->isManagerOf($category);
	}

	public function canDelete(IdentityInterface $identity, Category $category) {
		return $identity->isManagerOf($category);
	}

}
