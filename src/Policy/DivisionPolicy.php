<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Division;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class DivisionPolicy extends AppPolicy {

	public function canScores(IdentityInterface $identity = null, Division $division) {
		if (in_array($division->schedule_type, ['competition', 'none'])) {
			throw new ForbiddenRedirectException(__('Invalid schedule type.'),
				['controller' => 'Leagues', 'action' => 'index']);
		}

		return Configure::read('feature.public') || ($identity && $identity->isLoggedIn());
	}

	public function canEdit(IdentityInterface $identity, Division $division) {
		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

	public function canScheduling_fields(IdentityInterface $identity, $controller) {
		return $identity->isManager() || $identity->isCoordinator();
	}

	public function canAdd_coordinator(IdentityInterface $identity, Division $division) {
		return $identity->isManagerOf($division);
	}

	public function canRemove_coordinator(IdentityInterface $identity, Division $division) {
		return $identity->isManagerOf($division);
	}

	public function canAdd_teams(IdentityInterface $identity, Division $division) {
		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

	public function canDelete(IdentityInterface $identity, Division $division) {
		return $identity->isManagerOf($division);
	}

	public function canAllstars(IdentityInterface $identity, Division $division) {
		if ($division->allstars == 'never') {
			return false;
		}

		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

	public function canEmails(IdentityInterface $identity, Division $division) {
		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

	public function canSpirit(IdentityInterface $identity, ContextResource $resource) {
		if (!$resource->league->hasSpirit()) {
			return false;
		}

		return $identity->isManagerOf($resource->resource()) || $identity->isCoordinatorOf($resource->resource());
	}

	public function canApprove_scores(IdentityInterface $identity, Division $division) {
		if (in_array($division->schedule_type, ['competition', 'none'])) {
			throw new ForbiddenRedirectException(__('Invalid schedule type.'),
				['controller' => 'Leagues', 'action' => 'index']);
		}

		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

	public function canInitialize_ratings(IdentityInterface $identity, Division $division) {
		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

	public function canInitialize_dependencies(IdentityInterface $identity, Division $division) {
		if (in_array($division->schedule_type, ['competition', 'none'])) {
			throw new ForbiddenRedirectException(__('Invalid schedule type.'),
				['controller' => 'Leagues', 'action' => 'index']);
		}

		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

	public function canDelete_stage(IdentityInterface $identity, Division $division) {
		if (in_array($division->schedule_type, ['competition', 'none'])) {
			throw new ForbiddenRedirectException(__('Invalid schedule type.'),
				['controller' => 'Leagues', 'action' => 'index']);
		}

		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

	public function canSelect(IdentityInterface $identity, $controller) {
		return $identity->isManager() || $identity->isCoordinator();
	}

	public function canEdit_schedule(IdentityInterface $identity, Division $division) {
		if ($division->schedule_type == 'none') {
			throw new ForbiddenRedirectException(__('Invalid schedule type.'),
				['controller' => 'Leagues', 'action' => 'index']);
		}

		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

	public function canView_spirit(IdentityInterface $identity, ContextResource $resource) {
		return $resource->league->hasSpirit();
	}

	public function canView_spirit_scores(IdentityInterface $identity, ContextResource $resource) {
		$division = $resource->resource();
		$league = $resource->league;

		if (!$league->hasSpirit()) {
			return false;
		}

		switch ($league->display_sotg) {
			case 'symbols_only':
				return $identity->isAdmin();

			case 'coordinator_only':
				return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
		}

		return true;
	}

	public function canView_score_entries(IdentityInterface $identity, Division $division) {
		if (in_array($division->schedule_type, ['competition', 'none'])) {
			throw new ForbiddenRedirectException(__('Invalid schedule type.'),
				['controller' => 'Leagues', 'action' => 'index']);
		}

		return $identity->isManagerOf($division) || $identity->isCoordinatorOf($division);
	}

}
