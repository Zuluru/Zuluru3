<?php
namespace App\Policy;

use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Credit;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class CreditPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.registration')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canView(IdentityInterface $identity, Credit $credit) {
		return $identity->isManagerOf($credit) || $identity->isMe($credit) || $identity->isRelative($credit);
	}

	public function canAdd(IdentityInterface $identity, Credit $credit) {
		return $identity->isManager() && $identity->isManagerOf($credit->person) && !in_array($credit->person->status, ['locked', 'inactive']);
	}

	public function canEdit(IdentityInterface $identity, Credit $credit) {
		return $identity->isManagerOf($credit);
	}

	public function canTransfer(IdentityInterface $identity, Credit $credit) {
		if (!$identity->isManagerOf($credit) && !$identity->isMe($credit) && !$identity->isRelative($credit)) {
			return false;
		}

		// Check whether we can even transfer this
		if ($credit->balance <= 0) {
			throw new ForbiddenRedirectException(__('This credit has already been spent.'), ['/']);
		}

		return true;
	}

	public function canDelete(IdentityInterface $identity, Credit $credit) {
		if (!$identity->isManagerOf($credit)) {
			return false;
		}

		// Check whether we can even delete this
		if ($credit->amount_used > 0) {
			throw new ForbiddenRedirectException(__('Credits that have been even partially spent cannot be deleted.'), ['/']);
		}

		return true;
	}

}
