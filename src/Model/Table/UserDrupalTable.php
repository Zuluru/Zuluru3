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
			$root = Configure::read('Security.authenticators.Drupal.drupalRoot') ?: $_SERVER['DOCUMENT_ROOT'];
			define('DRUPAL_ROOT', $root);
		}

		require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
		drupal_settings_initialize();
		Configure::write('Security.drupalPrefix', $GLOBALS['databases']['default']['default']['prefix']);
		Configure::write('Security.drupalCookieDomain', $GLOBALS['cookie_domain']);

		// drupal_settings_initialize overwrites the session name that we want to use
		session_name(Configure::read('Session.cookie'));

		// Reset these; we don't want them to be readily available to third-party code
		unset($GLOBALS['databases']);
		unset($GLOBALS['drupal_hash_salt']);
	}

}
