<?php
namespace App\Policy;

use Authorization\Policy\BeforePolicyInterface;
use Authorization\Policy\Result;
use Authorization\Policy\ResultInterface;

class AppPolicy implements BeforePolicyInterface {

	public function before($identity, $resource, $action) {
		$result = $this->blockAnonymous($identity);
		if ($result === false || $result instanceof ResultInterface) {
			return $result;
		}

		$result = $this->blockLocked($identity);
		if ($result === false || $result instanceof ResultInterface) {
			return $result;
		}
	}

	public function blockAnonymous($identity) {
		if (!$identity) {
			return new Result(false);
		}
	}

	public function blockAnonymousExcept($identity, $action = null, $exceptions = []) {
		if (!empty($exceptions) && in_array($action, $exceptions)) {
			return;
		}

		return $this->blockAnonymous($identity);
	}

	public function blockLocked($identity) {
		if ($identity && $identity->getOriginalData()->person->status == 'locked') {
			return new LockedResult();
		}
	}

	public function blockLockedExcept($identity, $action = null, $exceptions = []) {
		if (!empty($exceptions) && in_array($action, $exceptions)) {
			return;
		}

		return $this->blockLocked($identity);
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
