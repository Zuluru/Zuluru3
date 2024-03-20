<?php
namespace App\Model\Entity;

use App\PasswordHasher\DrupalPasswordHasher;
use Cake\I18n\FrozenTime;

/**
 * UserDrupal Entity.
 * Mainly just provides accessors for fields with different names than the standard User entity.
 *
 * @property int $uid
 * @property string $name
 * @property string $pass
 * @property string $mail
 * @property int $created
 * @property int $access
 * @property int $login
 * @property int $status
 *
 * @property int $id
 * @property string $user_name
 * @property string $password
 * @property string $email
 * @property \Cake\I18n\FrozenTime $last_login
 *
 * @property \App\Model\Entity\Person $person
 */
class UserDrupal extends User {

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'*' => true,
		'id' => false,
		'uid' => false,
	];

	/**
	 * Fields that are excluded from JSON an array versions of the entity.
	 *
	 * @var array
	 */
	protected $_hidden = [
		'pass',
		'password',
		'name',
		'mail',
		'login',
	];

	protected function _getId() {
		return $this->uid;
	}

	protected function _getUserName() {
		return $this->name;
	}

	protected function _getPassword() {
		return $this->pass;
	}

	protected function _getEmail() {
		return $this->mail;
	}

	protected function _getLastLogin() {
		return new FrozenTime($this->login);
	}

	protected function _setPass($value) {
		$hasher = new DrupalPasswordHasher();
		return $hasher->hash($value);
	}

	/**
	 * A set of setters to make the entity accept fields like the base User class does
	 */

	protected function _setId($value) {
		$this->uid = $value;
		return $this->uid;
	}

	protected function _setUserName($value) {
		$this->name = $value;
		return $this->name;
	}

	protected function _setPassword($value) {
		$this->pass = $value;
		return $this->pass;
	}

	protected function _setEmail($value) {
		$this->mail = $value;
		return $this->mail;
	}

	public function merge(User $new) {
		parent::merge($new);

		// We also need to copy over the password, but we can't set it directly, as it will be re-hashed
		$this->_fields['pass'] = $new->pass;
		$this->setDirty('pass', true);

		// TODOSECOND: Update users_roles record too
	}

}
