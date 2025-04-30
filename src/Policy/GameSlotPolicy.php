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

	public function canSubmit(IdentityInterface $identity, GameSlot $game_slot) {
		if (empty($game_slot->games)) {
			return false;
		}

		$game = current($game_slot->games);
		return $identity->isCaptainOf($game) || $identity->isOfficialOf($game);
	}
}
