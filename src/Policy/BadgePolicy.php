<?php
namespace App\Policy;

use App\Model\Entity\Badge;
use Authorization\IdentityInterface;
use Authorization\Policy\ResultInterface;
use Cake\Core\Configure;

class BadgePolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.badges')) {
			return false;
		}

		return parent::before($identity, $resource, $action);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canDeactivated(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, Badge $badge) {
		if ($identity->isManagerOf($badge)) {
			return true;
		}

		if ($badge->active && $badge->visibility != BADGE_VISIBILITY_ADMIN) {
			return true;
		}

		return new RedirectResult(__('Invalid badge.'), ['action' => 'index']);
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Badge $badge) {
		return $identity->isManagerOf($badge);
	}

	public function canDelete(IdentityInterface $identity, Badge $badge) {
		return $identity->isManagerOf($badge);
	}

	public function canNominate_badge(IdentityInterface $identity, Badge $badge) {
		$result = $this->canView($identity, $badge);
		if ($result === false || $result instanceof ResultInterface) {
			return $result;
		}

		if ($badge->category == 'nominated' || ($badge->category == 'assigned' && $identity->isManagerOf($badge))) {
			return true;
		}

		return new RedirectResult(__('This badge must be earned, not granted.'), ['action' => 'index']);
	}

	public function canApprove_badge(IdentityInterface $identity, Badge $badge) {
		return $identity->isManagerOf($badge);
	}

}
