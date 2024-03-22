<?php
/**
 * Joomla-compliant password hashing.
 */
namespace App\PasswordHasher;

use App\Model\Table\UserJoomlaTable;
use Authentication\PasswordHasher\PasswordHasherInterface;
use Cake\Auth\AbstractPasswordHasher;

/**
 * Password hashing class that uses Joomla hashing algorithms. This class is
 * intended only to be used with Joomla databases.
 *
 */
class JoomlaPasswordHasher extends AbstractPasswordHasher implements PasswordHasherInterface {

	/**
	 * @inheritDoc
	 */
	public function hash($password): string {
		UserJoomlaTable::initializeJoomlaConfig();

		require_once JPATH_BASE . '/includes/defines.php';
		require_once JPATH_LIBRARIES . '/src/User/UserHelper.php';

		$salt = \JUserHelper::genRandomPassword(32);
		$crypt = \JUserHelper::getCryptedPassword($password, $salt);
		return "$crypt:$salt";
	}

	public function check($password, $hashedPassword): bool {
		UserJoomlaTable::initializeJoomlaConfig();

		require_once JPATH_BASE . '/includes/defines.php';
		require_once JPATH_LIBRARIES . '/src/User/UserHelper.php';

		if (strpos($hashedPassword, ':') !== false) {
			list($hash, $salt) = explode(':', $hashedPassword);
			$crypt = crypt($password, $hash);
			return ("$crypt:$salt" == $hashedPassword);
		} else {
			return \JUserHelper::verifyPassword($password, $hashedPassword);
		}
	}

	public function needsRehash($password): bool {
		// TODO: Include Joomla-specific checks?
		return false;
	}

}
