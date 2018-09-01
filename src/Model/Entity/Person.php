<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;

/**
 * Person Entity.
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property bool $publish_email
 * @property string $home_phone
 * @property bool $publish_home_phone
 * @property string $work_phone
 * @property string $work_ext
 * @property bool $publish_work_phone
 * @property string $mobile_phone
 * @property bool $publish_mobile_phone
 * @property string $addr_street
 * @property string $addr_city
 * @property string $addr_prov
 * @property string $addr_country
 * @property string $addr_postalcode
 * @property string $gender
 * @property string $gender_description
 * @property string $roster_designation
 * @property \Cake\I18n\FrozenTime $birthdate
 * @property int $height
 * @property string $shirt_size
 * @property string $status
 * @property bool $has_dog
 * @property bool $contact_for_feedback
 * @property bool $complete
 * @property string $twitter_token
 * @property string $twitter_secret
 * @property int $user_id
 * @property bool $show_gravatar
 * @property string $alternate_first_name
 * @property string $alternate_last_name
 * @property string $alternate_email
 * @property bool $publish_alternate_email
 * @property string $alternate_work_phone
 * @property string $alternate_work_ext
 * @property bool $publish_alternate_work_phone
 * @property string $alternate_mobile_phone
 * @property bool $publish_alternate_mobile_phone
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Allstar[] $allstars
 * @property \App\Model\Entity\Attendance[] $attendances
 * @property \App\Model\Entity\Credit[] $credits
 * @property \App\Model\Entity\Note[] $notes
 * @property \App\Model\Entity\Preregistration[] $preregistrations
 * @property \App\Model\Entity\Registration[] $registrations
 * @property \App\Model\Entity\Setting[] $settings
 * @property \App\Model\Entity\Skill[] $skills
 * @property \App\Model\Entity\Stat[] $stats
 * @property \App\Model\Entity\Subscription[] $subscriptions
 * @property \App\Model\Entity\TaskSlot[] $task_slots
 * @property \App\Model\Entity\Task[] $tasks
 * @property \App\Model\Entity\Upload[] $uploads
 * @property \App\Model\Entity\TeamsPerson[] $teams_people
 * @property \App\Model\Entity\Affiliate[] $affiliates
 * @property \App\Model\Entity\Badge[] $badges
 * @property \App\Model\Entity\Division[] $divisions
 * @property \App\Model\Entity\Franchise[] $franchises
 * @property \App\Model\Entity\Group[] $groups
 * @property \App\Model\Entity\Person[] $relatives
 * @property \App\Model\Entity\Person[] $related
 * @property \App\Model\Entity\Team[] $teams
 * @property \App\Model\Entity\Waiver[] $waivers
 *
 * @property string $user_name
 * @property string $password
 * @property \Cake\I18n\FrozenTime $last_login
 * @property string $client_ip
 * @property string $full_name
 * @property string $alternate_full_name
 * @property string $email
 * @property string $email_formatted
 * @property string $alternate_email_formatted
 * @property string $gender_display
 */
class Person extends Entity {

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

	// Make sure the virtual fields are included when we convert to arrays
	protected $_virtual = [
		'user_name', 'password', 'last_login', 'client_ip', 'email',
		'full_name', 'alternate_full_name',
	];

	protected function _getUser($default = null) {
		if ($default) {
			return $default;
		}

		if (empty($this->user_id)) {
			$this->_properties['user'] = null;
		} else {
			// Convoluted process to get the name of the property where user table data will be found
			$user_model = Configure::read('Security.authModel');
			$people_table = TableRegistry::get('People');
			$property = $people_table->associations()->get($user_model)->property();
			if (!array_key_exists($property, $this->_properties)) {
				$people_table->loadInto($this, [$user_model]);
			}
			if ($property != 'user') {
				$this->_properties['user'] = $this->$property;
			}
		}

		return $this->_properties['user'];
	}

	protected function _getUserName() {
		$user = $this->user;
		if ($user === null) {
			return null;
		}
		return $user->user_name;
	}

	protected function _getPassword() {
		$user = $this->user;
		if ($user === null) {
			return null;
		}
		return $user->password;
	}

	protected function _getLastLogin() {
		$user = $this->user;
		if ($user === null) {
			return null;
		}
		return $user->last_login;
	}

	protected function _getClientIp() {
		$user = $this->user;
		if ($user === null) {
			return null;
		}
		return $user->client_ip;
	}

	protected function _getFullName() {
		return trim("{$this->first_name} {$this->last_name}");
	}

	protected function _getAlternateFullName() {
		return trim("{$this->alternate_first_name} {$this->alternate_last_name}");
	}

	protected function _getEmail() {
		$user = $this->user;
		if ($user === null) {
			return null;
		}

		// Find the name of the email field in the user model
		$email_field = TableRegistry::get(Configure::read('Security.authModel'))->emailField;
		return $user->$email_field;
	}

	protected function _getEmailFormatted() {
		$email = $this->email;
		$name = $this->full_name;
		if (empty($email)) {
			return null;
		} else if (empty($name)) {
			return $email;
		} else {
			return "\"{$name}\" <{$email}>";
		}
	}

	protected function _getAlternateEmailFormatted() {
		$email = $this->alternate_email;
		$name = $this->full_name . ' (' . __('alternate') . ')';
		if (empty($email)) {
			return null;
		} else if (empty($name)) {
			return $email;
		} else {
			return "\"{$name}\" <{$email}>";
		}
	}

	protected function _getGenderDisplay() {
		$display = __($this->gender);
		if ($this->gender == 'Self-defined') {
			$display .= ' (' . h($this->gender_description) . ')';
		}
		if (!in_array($this->gender, Configure::read('options.gender_binary'))) {
			$display .= ' (' . __('Roster designation: {0}', __($this->roster_designation)) . ')';
		}

		return $display;
	}

	/**
	 * Merge two profiles into one, preserving all the info that it makes sense to preserve.
	 *
	 * @param Person $new
	 */
	public function merge(Person $new) {
		$preserve = ['id', 'status', 'user_id'];
		// These are player fields, which might not be present if the one being merged is a parent
		$preserve_if_new_is_empty = ['gender', 'gender_description', 'roster_designation', 'birthdate', 'height', 'shirt_size'];

		foreach ($new->visibleProperties() as $prop) {
			if ($this->accessible($prop) && !in_array($prop, $preserve)) {
				if (is_array($new->$prop)) {
					if (!empty($new->$prop)) {
						$model_table = TableRegistry::get($new->{$prop}[0]->source());
						$this->$prop = $model_table->mergeList($this->$prop, $new->$prop);
					}
				} else if (!empty($new->$prop) || !in_array($prop, $preserve_if_new_is_empty)) {
					if (!is_object($new->$prop) || is_a($new->$prop, 'Cake\Chronos\ChronosInterface')) {
						$this->$prop = $new->$prop;
					} else if (is_a($new->$prop, 'App\Model\Entity\User')) {
						if ($this->has($prop)) {
							$this->$prop->merge($new->$prop);
							$this->dirty($prop, true);
						} else {
							// The old record has no user associated with it: it's just a profile.
							// Move the new user to it.
							$this->$prop = $new->$prop;
							$this->user_id = $new->user_id;
							unset($new->$prop);
							unset($new->user_id);
						}
					} else {
						pr($prop);
						pr(get_class($new->$prop));
						trigger_error('TODOTESTING', E_USER_WARNING);
						exit;
					}
				}
			}
		}
	}

}
