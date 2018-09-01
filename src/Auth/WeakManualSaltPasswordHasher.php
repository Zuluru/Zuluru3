<?php
/**
 * Temporary transitional class, doing hashing that matches the various codes that may have been mailed out.
 * We can eliminate this in 2017 when we're pretty sure that old mailed out links are no longer being clicked on.
 */
namespace App\Auth;

use Cake\Auth\WeakPasswordHasher;
use Cake\Core\Configure;
use Cake\Utility\Security;

class WeakManualSaltPasswordHasher extends WeakPasswordHasher
{

	/**
	 * Generates password hash.
	 *
	 * @param string $password Plain text password to hash.
	 * @return string Password hash
	 */
	public function hash($password) {
		return Security::hash($password . ':' . Configure::read('Security.salt'), 'md5', false);
	}
}
