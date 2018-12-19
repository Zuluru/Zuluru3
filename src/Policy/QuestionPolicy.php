<?php
namespace App\Policy;

use App\Model\Entity\Question;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class QuestionPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canDeactivated(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, Question $question) {
		return $identity->isManagerOf($question);
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Question $question) {
		return $identity->isManagerOf($question);
	}

	public function canActivate(IdentityInterface $identity, Question $question) {
		return $identity->isManagerOf($question);
	}

	public function canDeactivate(IdentityInterface $identity, Question $question) {
		return $identity->isManagerOf($question);
	}

	public function canDelete(IdentityInterface $identity, Question $question) {
		return $identity->isManagerOf($question);
	}

	public function canAdd_answer(IdentityInterface $identity, Question $question) {
		return $identity->isManagerOf($question);
	}

	public function canAdd_question(IdentityInterface $identity, Question $question) {
		return $identity->isManagerOf($question);
	}

	public function canRemove_question(IdentityInterface $identity, Question $question) {
		return $identity->isManagerOf($question);
	}

}
