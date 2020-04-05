<?php
namespace App\Model\Table;

use App\Model\Entity\Person;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;

/**
 * Class for handling authentication using the Joomla user database.
 *
 * If you are using this class, you will need to manually add the following
 * entry to the 'Security' section in your config/app_local.php file:
 *	'joomlaRoot' => '/path/to/your/joomla/installation',
 */
class UserJoomlaTable extends UsersTable {
	/**
	 * Column in the table where usernames are stored.
	 */
	public $userField = 'username';

	/**
	 * Column in the table where passwords are stored.
	 */
	public $pwdField = 'password';

	/**
	 * Column in the table where email addresses are stored.
	 */
	public $emailField = 'email';

	/**
	 * Column in the table where actual names are stored.
	 */
	public $nameField = 'name';

	/**
	 * Column in the table where last login is stored.
	 */
	public $loginField = 'lastvisitDate';

	/**
	 * Class to use for hashing passwords.
	 */
	public $hasher = 'App\PasswordHasher\JoomlaPasswordHasher';

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);
		$this->initializeJoomlaConfig();

		$this->setTable(Configure::read('Security.joomlaPrefix') . 'users');
		$this->setPrimaryKey('id');

		$this->hasOne('JoomlaSessions', [
			'foreignKey' => 'userid',
		]);
	}

	public static function initializeJoomlaConfig() {
		if (!defined('JPATH_BASE')) {
			$root = Configure::read('Security.authenticators.JoomlaSession.joomlaRoot') ?: $_SERVER['DOCUMENT_ROOT'];
			define('JPATH_BASE', $root);
		}

		if (!Configure::check('Security.joomlaPrefix')) {
			require_once JPATH_BASE . '/configuration.php';
			$config = new \JConfig;
			Configure::write('Security.joomlaPrefix', $config->dbprefix);
		}
	}

	public function activated(Person $person) {
		return ($person->user && empty($person->user->activation));
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
		// TODOSECOND: Delete j_user_usergroup_map record too
	}

}
