<?php
namespace App\Policy;

use App\Model\Entity\Note;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class NotePolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.annotations')) {
			return false;
		}

		return parent::before($identity, $resource, $action);
	}

	public function canEdit_game(IdentityInterface $identity, Note $note) {
		return $this->canEdit($identity, $note, 'game_id');
	}

	public function canEdit_person(IdentityInterface $identity, Note $note) {
		return $this->canEdit($identity, $note, 'person_id');
	}

	public function canEdit_team(IdentityInterface $identity, Note $note) {
		return $this->canEdit($identity, $note, 'team_id');
	}

	protected function canEdit(IdentityInterface $identity, Note $note, $field) {
		if (empty($note->$field)) {
			throw new \InvalidArgumentException('Note does not have the required field set.');
		}

		// isManagerOf includes admins
		return $identity->isMine($note) ||
			($note->visibility == VISIBILITY_ADMIN && $identity->isManagerOf($note)) ||
			($note->visibility == VISIBILITY_COORDINATOR && $identity->isCoordinatorOf($note));
	}

	public function canDelete_game(IdentityInterface $identity, Note $note) {
		return $this->canDelete($identity, $note, 'game_id');
	}

	public function canDelete_person(IdentityInterface $identity, Note $note) {
		return $this->canDelete($identity, $note, 'person_id');
	}

	public function canDelete_team(IdentityInterface $identity, Note $note) {
		return $this->canDelete($identity, $note, 'team_id');
	}

	protected function canDelete(IdentityInterface $identity, Note $note, $field) {
		if (empty($note->$field)) {
			throw new \InvalidArgumentException('Note does not have the required field set.');
		}

		// isManagerOf includes admins
		return $identity->isMine($note) ||
			($note->visibility == VISIBILITY_ADMIN && $identity->isManagerOf($note)) ||
			($note->visibility == VISIBILITY_COORDINATOR && ($identity->isCoordinatorOf($note) || $identity->isManagerOf($note)));
	}

}
