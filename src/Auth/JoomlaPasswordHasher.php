<?php
/**
 * Joomla-compliant password hashing.
 */
namespace App\Auth;

use App\Model\Table\UserJoomlaTable;
use Cake\Auth\AbstractPasswordHasher;
use Cake\Core\Configure;

/**
 * Password hashing class that uses Joomla hashing algorithms. This class is
 * intended only to be used with Joomla databases.
 *
 */
class JoomlaPasswordHasher extends AbstractPasswordHasher {

	/**
	 * Generates password hash.
	 *
	 * @param string $password Plain text password to hash.
	 * @return string Password hash
	 */
	public function hash($password) {
		UserJoomlaTable::initializeJoomlaConfig();

		require_once JPATH_BASE . '/includes/defines.php';
		require_once JPATH_LIBRARIES . '/joomla/user/helper.php';

		$salt = JUserHelper::genRandomPassword(32);
		$crypt = JUserHelper::getCryptedPassword($password, $salt);
		return "$crypt:$salt";
	}

	public function check($password, $hashedPassword) {
		UserJoomlaTable::initializeJoomlaConfig();

		require_once JPATH_BASE . '/includes/defines.php';
		require_once JPATH_LIBRARIES . '/joomla/user/helper.php';

		if (strpos($hashedPassword, ':') !== false) {
			list($hash, $salt) = explode(':', $hashedPassword);
			$crypt = crypt($password, $hash);
			return ("$crypt:$salt" == $hashedPassword);
		} else {
			return JUserHelper::verifyPassword($password, $hashedPassword);
		}
	}

}
