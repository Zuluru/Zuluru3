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
 *	'jpathBase' => '/path/to/your/joomla/installation',
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
	 * Accounts (add, delete, passwords) are managed by Joomla, not Zuluru.
	 */
	public $manageAccounts = false;
	public $manageName = 'Joomla';
	public $loginComponent = 'LoginJoomla';

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config) {
		parent::initialize($config);
		$this->initializeJoomlaConfig();

		$this->table(Configure::read('Security.joomlaPrefix') . 'users');
		$this->primaryKey('id');

		$this->hasOne('JoomlaSessions', [
			'foreignKey' => 'userid',
		]);
	}

	public static function initializeJoomlaConfig() {
		if (!defined('JPATH_BASE')) {
			define('JPATH_BASE', Configure::read('Security.jpathBase'));
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
