<?php
namespace App\Policy;

use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\League;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class LeaguePolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		$this->blockAnonymousExcept($identity, $action, ['stats']);
		$this->blockLocked($identity);
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, League $league) {
		return $identity->isManagerOf($league) ||  $identity->isCoordinatorOf($league);
	}

	public function canDelete(IdentityInterface $identity, League $league) {
		return $identity->isManagerOf($league);
	}

	public function canAdd_division_fields(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canAdd_division(IdentityInterface $identity, League $league) {
		return $identity->isManagerOf($league);
	}

	public function canSummary(IdentityInterface $identity, League $league) {
		return $identity->isManagerOf($league);
	}

	public function canParticipation(IdentityInterface $identity, League $league) {
		return $identity->isManagerOf($league) ||  $identity->isCoordinatorOf($league);
	}

	public function canEdit_schedule(IdentityInterface $identity, League $league) {
		return $identity->isManagerOf($league) ||  $identity->isCoordinatorOf($league);
	}

	public function canStats(IdentityInterface $identity = null, League $league) {
		if (!$league->hasStats()) {
			throw new ForbiddenRedirectException(__('This league does not have stat tracking enabled.'),
				['controller' => 'Leagues', 'action' => 'view', '?' => ['league' => $league->id]]);
		}

		return Configure::read('feature.public') || ($identity && $identity->isLoggedIn());
	}

}
