<?php
/**
 * Base class for Zuluru authentication. Other variations should extend this
 * and set userField, pwdField, emailField, nameField, loginField, ipField,
 * hashMethod and hasher as appropriate.
 */
namespace App\Model\Table;

use App\Model\Entity\Person;
use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event as CakeEvent;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use App\Core\UserCache;

/**
 * Users Model
 *
 * @property \Cake\ORM\Association\HasMany $People
 */
class UsersTable extends AppTable {
	/**
	 * Column in the table where usernames are stored.
	 */
	public $userField = 'user_name';

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
	public $nameField = null;

	/**
	 * Column in the table where last login is stored.
	 */
	public $loginField = null;

	/**
	 * Column in the table where IP address is stored.
	 */
	public $ipField = null;

	/**
	 * Class to use for hashing passwords.
	 */
	public $hasher = 'Cake\Auth\DefaultPasswordHasher';

	/**
	 * Fallback function to use for hashing old passwords.
	 */
	public $hashMethod = 'sha256';

	/**
	 * Do we even try to manage anything about user records?
	 */
	public $manageUsers = true;

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('users');
		$this->setDisplayField($this->userField);
		$this->setPrimaryKey('id');

		$this->addBehavior('Trim');

		$this->hasOne('People', [
			'foreignKey' => 'user_id',
		]);
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): \Cake\Validation\Validator {
		$validator
			->numeric($this->getPrimaryKey())

			->allowEmptyString($this->getPrimaryKey(), null, 'create')

			->requirePresence($this->userField, 'create', __('Username must not be blank.'))
			->notEmptyString($this->userField, __('Username must not be blank.'))

			->requirePresence($this->emailField, 'create', __('You must supply a valid email address.'))
			->notEmptyString($this->emailField)
			->email($this->emailField, false, __('You must supply a valid email address.'))

			->dateTime('last_login')
			->allowEmptyDateTime('last_login')

			->allowEmptyString('client_ip')

			;

		return $validator;
	}

	/**
	 * Password validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationPassword(Validator $validator) {
		$validator
			->add('old_password', 'valid', [
				'rule' => function ($value, $context) {
					$user = $this->get($context['data'][$this->getPrimaryKey()]);
					if ($user && (new $this->hasher)->check($value, $user->password)) {
						return true;
					}
					return false;
				},
				'message' => __('Old password is not correct.'),
			])
			->notEmptyString('old_password')

			->requirePresence('new_password', 'create', __('Password must be between 6 and 50 characters long.'))
			->add('new_password', [
				'between' => [
					'rule' => ['lengthBetween', 6, 50],
					'message' => __('Password must be between 6 and 50 characters long.'),
					'last' => true,
				],
				'not_user_name' => [
					'rule' => function ($value, $context) {
						if (array_key_exists($this->userField, $context['data'])) {
							$username = $context['data'][$this->userField];
						} else {
							$user = $this->get($context['data'][$this->getPrimaryKey()]);
							$username = $user->{$this->userField};
						}
						if ($value != $username) {
							return true;
						}
						return false;
					},
					'message' => __('You cannot use your username as your password.'),
					'last' => true,
				]
			])
			->notEmptyString('new_password')

			->requirePresence('confirm_password', 'create', __('Password must be between 6 and 50 characters long.'))
			->add('confirm_password', [
				'match'=>[
					'rule'=> ['compareWith', 'new_password'],
					'message' => __('Passwords must match.'),
				]
			])
			->notEmptyString('confirm_password')

		;

		return $validator;
	}

	/**
	 * Account creation validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationCreate(Validator $validator) {
		$validator = $this->validationDefault($validator);
		$validator = $this->validationPassword($validator);

		if (Configure::read('feature.antispam')) {
			$validator
				->allowEmptyString('subject')
				->add('subject', 'antispam', [
					'rule' => function ($value, $context) {
						// The presence of data in a field that should not be filled in triggers anti-spam measures.
						if (!empty($value)) {
							return false;
						}
						return true;
					},
					'message' => false,
				])

				->add('timestamp', 'antispam', [
					'rule' => function ($value, $context) {
						// Also, anyone that fills the form out in under 15 seconds is a spambot.
						if (FrozenTime::now()->toUnixString() - $value < 15) {
							return false;
						}
						return true;
					},
					'message' => false,
				]);
		}

		return $validator;
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker {
		// TODO: Add a system configuration requiring unique email addresses
		//$rules->add($rules->isUnique([$this->emailField]));
		$rules->add($rules->isUnique([$this->userField], __('That username is already taken')));
		return $rules;
	}

	/**
	 * Exclude any locked-out users from logging in. There may be users with no
	 * People record, though, so we also allow NULL status, which will happen
	 * when the LEFT JOIN doesn't find the match.
	 * @param Query $query The query object being used for the authentication query
	 * @param array $options Unused
	 * @return Query
	 */
	public function findAuth(Query $query, array $options) {
		return $query->where(['OR' => ['People.status !=' => 'locked', 'People.status IS' => null]]);
	}

	/**
	 * Perform additional operations after it is saved.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was saved
	 * @param \ArrayObject $options The options passed to the save method
	 * @return void
	 */
	public function afterSave(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// Delete the cached data, so it's reloaded next time it's needed
		$cache = UserCache::getInstance();
		if ($entity->has('person')) {
			$person_id = $entity->person->id;
		} else {
			try {
				$person_id = $this->People->field('id', ['People.user_id' => $entity->id]);
			} catch (RecordNotFoundException|InvalidArgumentException $ex) {
				// If there's no person record, then there's no cache to clear.
				return;
			}
		}
		if (!$entity->isNew()) {
			$cache->clear('Person', $person_id);
			$cache->clear('User', $person_id);
		}

		// Send an event to any callback listeners
		$event = new CakeEvent('Model.User.afterSave', $this, [$entity]);
		$this->getEventManager()->dispatch($event);
	}

	/**
	 * Perform additional operations after it is deleted.
	 *
	 * @param \Cake\Event\Event $cakeEvent The afterDelete event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that was deleted
	 * @param \ArrayObject $options The options passed to the delete method
	 * @return void
	 */
	public function afterDelete(\Cake\Event\EventInterface $cakeEvent, EntityInterface $entity, ArrayObject $options) {
		// Send an event to any callback listeners
		$event = new CakeEvent('Model.User.afterDelete', $this, [$entity]);
		$this->getEventManager()->dispatch($event);
	}

	/**
	 * Do a system-specific test of whether the account has been activated.
	 */
	public function activated(Person $person) {
		// Stand-alone Zuluru has no activation mechanism
		return true;
	}

	/**
	 * Create a simple person record. This will be called in the case where
	 * a third-party authentication system has logged someone in, but they
	 * don't yet have a Zuluru profile.
	 */
	public function createPersonRecord($user) {
		$save = [
			'user_id' => $user->{$this->getPrimaryKey()},
			'status' => Configure::read('feature.auto_approve') ? 'active' : 'new',
			'complete' => false,
			'gender' => '',
			'groups' => ['_ids' => [GROUP_PLAYER]],
			'affiliates' => ['_ids' => [AFFILIATE_DUMMY]],
		];
		if (!empty($this->nameField)) {
			$save['first_name'] = trim($user->{$this->nameField});
		}
		if (!empty($save['first_name'])) {
			if (strpos($save['first_name'], ' ') !== false) {
				[$save['first_name'], $save['last_name']] = explode(' ', $save['first_name'], 2);
			} else if (preg_match('/^([[:upper:]][[:lower:]]+)([[:upper:]][[:lower:]]+)$/', $save['first_name'], $matches)) {
				$save['first_name'] = $matches[1];
				$save['last_name'] = $matches[2];
			}
		}

		// We know that this largely won't pass any validation.
		$person = $this->People->newEntity($save, ['validate' => false]);
		return $this->People->save($person, ['checkRules' => false]);
	}

}
