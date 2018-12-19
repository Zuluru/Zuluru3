<?php
namespace App\Policy;

use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Badge;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class BadgePolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.badges')) {
			return false;
		}

		parent::before($identity, $resource, $action);
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

		throw new ForbiddenRedirectException(__('Invalid badge.'), ['action' => 'index']);
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
		// This will either return true, in which case further checks are done, or throw an exception.
		$this->canView($identity, $badge);

		if ($badge->category == 'nominated' || ($badge->category == 'assigned' && $identity->isManagerOf($badge))) {
			return true;
		}

		throw new ForbiddenRedirectException(__('This badge must be earned, not granted.'), ['action' => 'index']);
	}

	public function canApprove_badge(IdentityInterface $identity, Badge $badge) {
		return $identity->isManagerOf($badge);
	}

}
