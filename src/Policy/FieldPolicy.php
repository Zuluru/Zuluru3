<?php
namespace App\Policy;

use App\Model\Entity\Field;
use Authorization\IdentityInterface;

class FieldPolicy extends AppPolicy {

	public function canOpen(IdentityInterface $identity, Field $field) {
		return $identity->isManagerOf($field);
	}

	public function canClose(IdentityInterface $identity, Field $field) {
		return $identity->isManagerOf($field);
	}

	public function canDelete(IdentityInterface $identity, Field $field) {
		return $identity->isManagerOf($field);
	}

	public function canAdd_game_slots(IdentityInterface $identity, Field $field) {
		return $identity->isManagerOf($field);
	}

	public function canBookings(IdentityInterface $identity, Field $field) {
		return true;
	}

}
