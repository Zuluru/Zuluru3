<?php
/**
 * Identical to Cake's WeakPasswordHasher, except that it doesn't use the salt.
 */
namespace App\Auth;

use Cake\Auth\WeakPasswordHasher;
use Cake\Utility\Security;

/**
 * Password hashing class that use weak hashing algorithms. This class is
 * intended only to be used with legacy databases where passwords have
 * not been migrated to a stronger algorithm yet.
 *
 */
class WeakNoSaltPasswordHasher extends WeakPasswordHasher {

	/**
	 * Generates password hash.
	 *
	 * @param string $password Plain text password to hash.
	 * @return string Password hash
	 */
	public function hash($password) {
		return Security::hash($password, $this->_config['hashType'], false);
	}
}
