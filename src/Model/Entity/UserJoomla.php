<?php
namespace App\Model\Entity;

use App\Auth\JoomlaPasswordHasher;
use Cake\I18n\FrozenTime;

/**
 * UserJoomla Entity.
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
class UserJoomla extends User {

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
	];

	/**
	 * Fields that are excluded from JSON an array versions of the entity.
	 *
	 * @var array
	 */
	protected $_hidden = [
		'password',
		'username',
		'email',
		'registerDate',
		'lastvisitDate',
	];

	protected function _getUserName() {
		return $this->username;
	}

	protected function _getLastLogin() {
		return $this->lastvisitDate;
	}

	protected function _setPassword($value) {
		$hasher = new JoomlaPasswordHasher();
		return $hasher->hash($value);
	}

	/**
	 * A set of setters to make the entity accept fields like the base User class does
	 */

	protected function _setUserName($value) {
		$this->username = $value;
		return $this->username;
	}

	public function merge(User $new) {
		parent::merge($new);

		// We also need to copy over the password, but we can't set it directly, as it will be re-hashed
		$this->_properties['password'] = $new->password;
		$this->dirty('password', true);

		// TODOSECOND: Update users_roles record too
	}

}
