<?php
namespace App\Policy;

use App\Model\Entity\Answer;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class AnswerPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canActivate(IdentityInterface $identity, Answer $answer) {
		return $identity->isManagerOf($answer);
	}

	public function canDeactivate(IdentityInterface $identity, Answer $answer) {
		return $identity->isManagerOf($answer);
	}

	public function canDelete(IdentityInterface $identity, Answer $answer) {
		return $identity->isManagerOf($answer);
	}

}
