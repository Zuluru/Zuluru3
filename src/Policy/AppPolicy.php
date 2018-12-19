<?php
namespace App\Policy;

use App\Exception\LockedIdentityException;
use Authorization\Exception\ForbiddenException;
use Authorization\Policy\BeforePolicyInterface;

class AppPolicy implements BeforePolicyInterface {

	public function before($identity, $resource, $action) {
		$this->blockAnonymous($identity);
		$this->blockLocked($identity);
	}

	public function blockAnonymous($identity) {
		if (!$identity) {
			throw new ForbiddenException();
		}
	}

	public function blockAnonymousExcept($identity, $action = null, $exceptions = []) {
		if (!empty($exceptions) && in_array($action, $exceptions)) {
			return;
		}

		$this->blockAnonymous($identity);
	}

	public function blockLocked($identity) {
		if ($identity && $identity->getOriginalData()->person->status == 'locked') {
			throw new LockedIdentityException();
		}
	}

	public function blockLockedExcept($identity, $action = null, $exceptions = []) {
		if (!empty($exceptions) && in_array($action, $exceptions)) {
			return;
		}

		$this->blockLocked($identity);
	}

	public function allowAdmin($identity) {
		if ($identity && $identity->isAdmin()) {
			return true;
		}
	}

	public function allowAdminExcept($identity, $action = null, $exceptions = []) {
		if (!empty($exceptions) && in_array($action, $exceptions)) {
			return;
		}

		return $this->allowAdmin($identity);
	}

}
