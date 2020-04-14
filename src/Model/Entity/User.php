<?php
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity.
 *
 * @property int $id
 * @property string $user_name
 * @property string $password
 * @property string $email
 * @property \Cake\I18n\FrozenTime $last_login
 * @property string $client_ip
 *
 * @property \App\Model\Entity\Person $person
 */
class User extends Entity {

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
		'user_name',
		'email',
		'last_login',
		'client_ip',
	];

	protected function _setPassword($value) {
		$hasher = new DefaultPasswordHasher();
		return $hasher->hash($value);
	}

	public function merge(User $new) {
		foreach ($new->visibleProperties() as $prop) {
			// We never want to copy empty properties in the user record;
			// it would only be things like last login date
			if ($this->isAccessible($prop) && !empty($new->$prop)) {
				$this->$prop = $new->$prop;
			}
		}

		// We also need to copy over the password, but we can't set it directly, as it will be re-hashed
		$this->_properties['password'] = $new->password;
		$this->setDirty('password', true);
	}

}
