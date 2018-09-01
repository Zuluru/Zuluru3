<?php
/**
 * Drupal-compliant password hashing.
 */
namespace App\Auth;

use Cake\Auth\AbstractPasswordHasher;
use Cake\Core\Configure;
use Cake\ORM\Entity;

/**
 * Password hashing class that uses Drupal hashing algorithms. This class is
 * intended only to be used with Drupal databases.
 *
 */
class DrupalPasswordHasher extends AbstractPasswordHasher {

	/**
	 * Generates password hash.
	 *
	 * @param string $password Plain text password to hash.
	 * @return string Password hash
	 */
	public function hash($password) {
		if (!defined('DRUPAL_ROOT')) {
			define('DRUPAL_ROOT', Configure::read('Security.drupalRoot'));
		}

		require_once DRUPAL_ROOT . '/includes/password.inc';

		return user_hash_password($password);
	}

	public function check($password, $hashedPassword) {
		if (!defined('DRUPAL_ROOT')) {
			define('DRUPAL_ROOT', Configure::read('Security.drupalRoot'));
		}

		require_once DRUPAL_ROOT . '/includes/password.inc';

		$account = new Entity();
		$account->pass = $hashedPassword;
		return user_check_password($password, $account);
	}

}
