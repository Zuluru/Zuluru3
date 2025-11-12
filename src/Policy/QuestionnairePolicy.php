<?php
namespace App\Policy;

use App\Model\Entity\Questionnaire;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class QuestionnairePolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		return parent::before($identity, $resource, $action);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canDeactivated(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, Questionnaire $questionnaire) {
		return $identity->isManagerOf($questionnaire);
	}

	public function canAdd(IdentityInterface $identity, Questionnaire $questionnaire) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Questionnaire $questionnaire) {
		return $identity->isManagerOf($questionnaire);
	}

	public function canActivate(IdentityInterface $identity, Questionnaire $questionnaire) {
		return $identity->isManagerOf($questionnaire);
	}

	public function canDeactivate(IdentityInterface $identity, Questionnaire $questionnaire) {
		return $identity->isManagerOf($questionnaire);
	}

	public function canDelete(IdentityInterface $identity, Questionnaire $questionnaire) {
		return $identity->isManagerOf($questionnaire);
	}

	public function canAdd_question(IdentityInterface $identity, Questionnaire $questionnaire) {
		return $identity->isManagerOf($questionnaire);
	}

	public function canRemove_question(IdentityInterface $identity, Questionnaire $questionnaire) {
		return $identity->isManagerOf($questionnaire);
	}

}
