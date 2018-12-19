<?php
namespace App\Policy;

use App\Model\Entity\GameSlot;
use Authorization\IdentityInterface;

class GameSlotPolicy extends AppPolicy {

	public function canView(IdentityInterface $identity, GameSlot $game_slot) {
		return $identity->isManagerOf($game_slot);
	}

	public function canEdit(IdentityInterface $identity, GameSlot $game_slot) {
		return $identity->isManagerOf($game_slot);
	}

	public function canDelete(IdentityInterface $identity, GameSlot $game_slot) {
		return $identity->isManagerOf($game_slot);
	}

	public function canSubmit_score(IdentityInterface $identity, GameSlot $game_slot) {
		return $identity->isCaptainOf($game_slot);
	}

}
