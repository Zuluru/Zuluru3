<?php
/**
 * Identical to Cake's LegacyPasswordHasher, except that it doesn't use the salt.
 */
namespace App\PasswordHasher;

use Authentication\PasswordHasher\LegacyPasswordHasher;
use Cake\Utility\Security;

/**
 * Password hashing class that use legacy hashing algorithms. This class is
 * intended only to be used with legacy databases where passwords have
 * not been migrated to a stronger algorithm yet.
 *
 */
class LegacyNoSaltPasswordHasher extends LegacyPasswordHasher {

	/**
     * @inheritDoc
	 */
	public function hash($password): string {
		return Security::hash($password, $this->_config['hashType'], false);
	}
}
