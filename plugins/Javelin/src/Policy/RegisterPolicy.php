<?php
namespace Javelin\Policy;

use App\Policy\AppPolicy;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class RegisterPolicy extends AppPolicy {

	/**
	 * This policy overrides the default before function, because there are a few situations where admins
	 * don't actually have complete access, and because we allow some roster operations to happen
	 * through emailed links, usable by people who aren't logged in.
	 */
	public function before($identity, $resource, $action) {
		$this->blockAnonymous($identity);
		$this->blockLocked($identity);

		return !Configure::check('javelin.api_key') && $this->allowAdmin($identity);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return false;
	}

}
