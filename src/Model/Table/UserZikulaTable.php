<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;

/**
 * Class for handling authentication using the Zikula user database.
 */
class UserZikulaTable extends UsersTable {
	/**
	 * Column in the table where usernames are stored.
	 */
	public $userField = 'pn_uname';

	/**
	 * Column in the table where passwords are stored.
	 */
	public $pwdField = 'pn_pass';

	/**
	 * Column in the table where email addresses are stored.
	 */
	public $emailField = 'pn_email';

	/**
	 * Column in the table where actual names are stored.
	 */
	public $nameField = 'pn_name';

	/**
	 * Column in the table where last login is stored.
	 */
	public $loginField = 'pn_lastlogin';

	/**
	 * function to use for hashing passwords.
	 */
	public $hashMethod = 'sha256';

	public function initialize(array $config) {
		parent::initialize($config);

		$this->setTable('nuke_users');
		$this->setPrimaryKey('pn_uid');
	}

	public function TODOLATER_activated($data) {
		return (array_key_exists($this->name, $data) && $data[$this->name]['pn_activated']);
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
		// TODOLATER: Delete nuke_group_membership record too
	}

	public function TODOLATER_merge(EntityInterface $old, EntityInterface $new, $copy_empty = true) {
		$this->delete($old_id);
		// TODO: Update nuke_group_membership record too
		$this->updateAll([$this->primaryKey() => $old_id], [$this->primaryKey() => $new_id]);
	}
}
