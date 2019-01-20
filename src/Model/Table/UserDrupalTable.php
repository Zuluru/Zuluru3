<?php
namespace App\Model\Table;

use App\Model\Entity\Person;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;
use Cake\ORM\TableRegistry;

/**
 * Class for handling authentication using the Drupal user database.
 */
class UserDrupalTable extends UsersTable {
	/**
	 * Column in the table where usernames are stored.
	 */
	public $userField = 'name';

	/**
	 * Column in the table where passwords are stored.
	 */
	public $pwdField = 'pass';

	/**
	 * Column in the table where email addresses are stored.
	 */
	public $emailField = 'mail';

	/**
	 * Column in the table where last login is stored.
	 */
	public $loginField = 'login';

	/**
	 * Class to use for hashing passwords.
	 */
	public $hasher = 'App\PasswordHasher\DrupalPasswordHasher';

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);

		$this->_initializeDrupal();
		$this->table(Configure::read('Security.drupalPrefix') . 'users');
		$this->displayField($this->userField);
		$this->primaryKey('uid');

		$this->hasOne('DrupalSessions', [
			'foreignKey' => 'uid',
		]);
	}

	public function activated(Person $person) {
		return ($person->user && $person->user->status != 0);
	}

	/**
	 * Perform additional operations before it is deleted.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeDelete event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity to be deleted
	 * @param \ArrayObject $options The options passed to the delete method
	 * @return bool
	 */
	public function beforeDelete(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// TODOSECOND: Delete users_roles record too
	}

	/**
	 * Modifies the entity before it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The beforeSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function beforeSave(CakeEvent $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		if ($entity->isNew()) {
			// Drupal doesn't use auto increment on the uid column.
			// This hack is adapted from Drupal's methods...
			// It will leave extra records in the sequences table,
			// but Drupal will take care of that for us.
			$sequences_table = TableRegistry::get(Configure::read('Security.drupalPrefix') . 'sequences');
			$sequence = $sequences_table->newEntity(['value' => null]);
			if (!$sequences_table->save($sequence)) {
				return false;
			}
			$entity->uid = $sequence->value;
			$entity->status = 1; // don't require further activation in Drupal
			$entity->created = time();
		}
	}

	protected function _initializeDrupal() {
		if (!defined('DRUPAL_ROOT')) {
			$root = Configure::read('Security.authenticators.DrupalSession.drupalRoot') ?: $_SERVER['DOCUMENT_ROOT'];
			define('DRUPAL_ROOT', $root);
		}

		require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
		if (file_exists(DRUPAL_ROOT . '/' . conf_path() . '/settings.php')) {
			require DRUPAL_ROOT . '/' . conf_path() . '/settings.php';
		} else {
			trigger_error(__('Could not find Drupal settings.php.'), E_USER_ERROR);
		}

		Configure::write('Security.drupalPrefix', $databases['default']['default']['prefix']);

		// Replicate Drupal's method of finding the session name and cookie domain.
		// We can't just call drupal_settings_initialize, because that will use the
		// entire URL including Zuluru subfolder when calculating the session name
		// in the case where the cookie_domain isn't set in the settings.php.
		if (!isset($cookie_domain) && !empty($_SERVER['HTTP_HOST'])) {
			$cookie_domain = $_SERVER['HTTP_HOST'];
			// Strip leading periods, www., and port numbers from cookie domain.
			$cookie_domain = ltrim($cookie_domain, '.');
			if (strpos($cookie_domain, 'www.') === 0) {
				$cookie_domain = substr($cookie_domain, 4);
			}
		}

		$prefix = ini_get('session.cookie_secure') ? 'SSESS' : 'SESS';
		$drupal_session_name = $prefix . substr(hash('sha256', $cookie_domain), 0, 32);
		Configure::write('Security.drupalSessionName', $drupal_session_name);

		$cookie_domain = explode(':', $cookie_domain);
		$cookie_domain = '.' . $cookie_domain[0];
		if (count(explode('.', $cookie_domain)) > 2 && !is_numeric(str_replace('.', '', $cookie_domain))) {
			ini_set('session.cookie_domain', $cookie_domain);
		}
	}

}
