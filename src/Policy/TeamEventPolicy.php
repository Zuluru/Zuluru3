<?php
namespace App\Policy;

use App\Model\Entity\TeamEvent;
use Authorization\IdentityInterface;

class TeamEventPolicy extends AppPolicy {

	public function canView(IdentityInterface $identity, TeamEvent $team_event) {
		return $identity->isManagerOf($team_event) || $identity->isPlayerOn($team_event) || $identity->isRelativePlayerOn($team_event);
	}

	public function canEdit(IdentityInterface $identity, TeamEvent $team_event) {
		return $identity->isManagerOf($team_event) || $identity->isCaptainOf($team_event);
	}

	public function canDelete(IdentityInterface $identity, TeamEvent $team_event) {
		return $identity->isManagerOf($team_event) || $identity->isCaptainOf($team_event);
	}

}
