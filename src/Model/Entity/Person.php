<?php
namespace App\Model\Entity;

use App\Core\UserCache;
use Authorization\IdentityInterface;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * Person Entity.
 *
 * @property int $id
 * @property string $first_name
 * @property string $legal_name
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
 * @property bool $publish_gender
 * @property string $roster_designation
 * @property string $pronouns
 * @property bool $publish_pronouns
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
 * @property \App\Model\Entity\ActivityLog[] $activity_logs
 * @property \App\Model\Entity\AffiliatesPerson[] $affiliates_people
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
 * @property \App\Model\Entity\Credit[] $created_credits
 * @property \App\Model\Entity\Note[] $created_notes
 * @property \App\Model\Entity\Payment[] $created_payments
 * @property \App\Model\Entity\Payment[] $updated_payments
 * @property \App\Model\Entity\ScoreDetailStat[] $score_detail_stats
 * @property \App\Model\Entity\ScoreEntry[] $score_entries
 * @property \App\Model\Entity\SpiritEntry[] $spirit_entries
 * @property \App\Model\Entity\Affiliate[] $affiliates
 * @property \App\Model\Entity\Badge[] $badges
 * @property \App\Model\Entity\Division[] $divisions
 * @property \App\Model\Entity\Franchise[] $franchises
 * @property \App\Model\Entity\Group[] $groups
 * @property \App\Model\Entity\Person[] $relatives Profiles that this person controls
 * @property \App\Model\Entity\Person[] $related Profiles that control this person
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

	/**
	 * Fields that are excluded from JSON an array versions of the entity.
	 * By default, we exclude almost everything. Some will be added back
	 * in for admin views.
	 *
	 * @var array
	 */
	protected $_hidden = [
		'legal_name',
		'publish_email',
		'alternate_email',
		'publish_alternate_email',
		'home_phone',
		'publish_home_phone',
		'work_phone',
		'work_ext',
		'publish_work_phone',
		'mobile_phone',
		'publish_mobile_phone',
		'addr_street',
		'addr_city',
		'addr_prov',
		'addr_country',
		'addr_postalcode',
		'gender',
		'gender_display',
		'gender_description',
		'roster_designation',
		'pronouns',
		'birthdate',
		'height',
		'shirt_size',
		'status',
		'has_dog',
		'contact_for_feedback',
		'complete',
		'twitter_token',
		'twitter_secret',
		'user_id',
		'show_gravatar',
		'modified',
		'created',
		'alternate_first_name',
		'alternate_last_name',
		'alternate_full_name',
		'alternate_work_phone',
		'alternate_work_ext',
		'publish_alternate_work_phone',
		'alternate_mobile_phone',
		'publish_alternate_mobile_phone',
		// Some associations
		'allstars',
		'badges',
		'credits',
		'groups',
		'notes',
		'preregistrations',
		'registrations',
		'related_to',
		'relatives',
		'tasks',
		'uploads',
		'waivers',
		// Some virtual fields carried forward from user records
		'user_name',
		'password',
		'last_login',
		'client_ip',
		'email',
	];

	// Make sure the virtual fields are included when we convert to arrays
	protected $_virtual = [
		'user_name', 'password', 'last_login', 'client_ip', 'email',
		'full_name', 'alternate_full_name', 'gender_display',
	];

	protected function _getUser($default = null) {
		if ($default) {
			return $default;
		}

		if (empty($this->user_id)) {
			$this->_fields['user'] = null;
		} else {
			// Convoluted process to get the name of the property where user table data will be found
			$user_model = Configure::read('Security.authModel');
			$people_table = TableRegistry::getTableLocator()->get('People');
			$property = $people_table->associations()->get($user_model)->getProperty();
			if (!array_key_exists($property, $this->_fields)) {
				$people_table->loadInto($this, [$user_model]);
			}
			if ($property != 'user') {
				$this->_fields['user'] = $this->$property;
			}
		}

		return $this->_fields['user'];
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
		$email_field = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . Configure::read('Security.authModel'))->emailField;
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
		$name = $this->full_name . __(' ({0})', __('alternate'));
		if (empty($email)) {
			return null;
		} else if (empty($name)) {
			return $email;
		} else {
			return "\"{$name}\" <{$email}>";
		}
	}

	protected function _getGenderDisplay() {
		if (empty($this->gender)) {
			return '';
		}

		if ($this->gender === 'Prefer to specify') {
			$display = h($this->gender_description);
		} else {
			$display = __($this->gender);
		}
		if (Configure::read('gender.column') === 'roster_designation') {
			$display .= __(' ({0}: {1})', __('Roster Designation'), __($this->roster_designation));
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
		$preserve_if_new_is_empty = [
			'gender', 'gender_description', 'publish_gender', 'roster_designation', 'pronouns', 'publish_pronouns',
			'birthdate', 'height', 'shirt_size', 'user',
		];

		if (empty($new->user_id)) {
			$preserve_if_new_is_empty = array_merge($preserve_if_new_is_empty, ['home_phone', 'work_phone', 'mobile_phone', 'addr_street', 'addr_city', 'addr_prov', 'addr_country', 'addr_postalcode']);
		}
		foreach (array_keys($new->_fields) as $prop) {
			if ($this->isAccessible($prop) && !in_array($prop, $preserve)) {
				if (is_array($new->$prop)) {
					if (!empty($new->$prop)) {
						$model_table = TableRegistry::getTableLocator()->get($new->{$prop}[0]->getSource());
						$this->$prop = $model_table->mergeList($this->$prop, $new->$prop);
					}
				} else if (!empty($new->$prop) || !in_array($prop, $preserve_if_new_is_empty)) {
					if (!is_object($new->$prop) || is_a($new->$prop, \Cake\Chronos\ChronosInterface::class)) {
						$this->$prop = $new->$prop;
					} else if ($new->$prop instanceof \App\Model\Entity\User) {
						if ($this->has($prop)) {
							$this->$prop->merge($new->$prop);
							$this->setDirty($prop, true);
						} else {
							// The old record has no user associated with it: it's just a profile.
							// Move the new user to it.
							$this->$prop = $new->$prop;
							$this->user_id = $new->user_id;
							unset($new->$prop);
							unset($new->user_id);
						}
					}
				}
			}
		}
	}

	/*
	 * Update the list of hidden fields, based on permissions and connections of the person viewing the record.
	 */
	public function updateHidden(IdentityInterface $identity = null) {
		if ($this->status === 'inactive') {
			$visible = ['first_name', 'last_name'];
		} else {
			if ($identity) {
				$is_me = $identity->isMe($this);
				$is_relative = $identity->isRelative($this);
				$is_manager = $identity->isManagerOf($this);
				$is_logged_in = $identity->isLoggedIn();
				$my_owned_division_ids = $identity->coordinatedDivisionIds();
			} else {
				$is_me = $is_relative = $is_manager = $is_logged_in = false;
				$my_owned_division_ids = [];
			}

			$is_player = collection($this->groups)->some(function ($group) {
				return $group->id == GROUP_PLAYER;
			});
			$is_parent = collection($this->groups)->some(function ($group) {
				return $group->id == GROUP_PARENT;
			});

			// Pull some lists of team and division IDs for later comparisons
			$user_cache = UserCache::getInstance();
			$my_team_ids = $user_cache->read('AcceptedTeamIDs');
			$my_owned_team_ids = $user_cache->read('OwnedTeamIDs');
			$my_captain_division_ids = collection($user_cache->read('OwnedTeams'))->extract('division_id')->toArray();
			$their_team_ids = $user_cache->read('AcceptedTeamIDs', $this->id);
			$their_owned_team_ids = $user_cache->read('OwnedTeamIDs', $this->id);
			$their_owned_division_ids = $user_cache->read('DivisionIDs', $this->id);
			$their_captain_division_ids = collection($user_cache->read('OwnedTeams', $this->id))->extract('division_id')->toArray();

			// Check if the current user is a captain of a team the viewed player is on
			$is_captain = !empty(array_intersect($my_owned_team_ids, $their_team_ids));

			// Check if the current user is on a team the viewed player is a captain of
			$is_my_captain = !empty(array_intersect($my_team_ids, $their_owned_team_ids));

			// Check if the current user is a coordinator of a division the viewed player is a captain in
			$is_coordinator = !empty(array_intersect($my_owned_division_ids, $their_captain_division_ids));

			// Check if the current user is a captain in a division the viewed player is a coordinator of
			$is_my_coordinator = !empty(array_intersect($my_captain_division_ids, $their_owned_division_ids));

			// Check if the current user is a captain in a division the viewed player is a captain in
			$is_division_captain = !empty(array_intersect($my_captain_division_ids, $their_captain_division_ids));

			$visible = [];

			if ($is_me || $is_relative || $is_manager ||
				$is_coordinator || $is_my_coordinator || $is_captain || $is_my_captain || $is_division_captain
			) {
				$visible += [
					'email' => true,
					'publish_email' => true,
					'alternate_email' => true,
					'publish_alternate_email' => true,
					'home_phone' => true,
					'publish_home_phone' => true,
					'work_phone' => true,
					'work_ext' => true,
					'publish_work_phone' => true,
					'mobile_phone' => true,
					'publish_mobile_phone' => true,
					'alternate_work_phone' => true,
					'alternate_work_ext' => true,
					'publish_alternate_work_phone' => true,
					'alternate_mobile_phone' => true,
					'publish_alternate_mobile_phone' => true,
				];
			}

			if ($is_manager) {
				$visible += [
					'last_login' => true,
					'client_ip' => true,
				];
			}

			if ($is_manager || $is_me || $is_relative) {
				$visible += [
					'user_name' => true,
					'user_id' => true,
					'last_login' => true,
					'client_ip' => true,
					'addr_street' => true,
					'addr_city' => true,
					'addr_prov' => true,
					'addr_country' => true,
					'addr_postalcode' => true,
					'birthdate' => true,
					'height' => true,
					'shirt_size' => true,
					'groups' => true,
					'status' => true,
					'has_dog' => true,
					'contact_for_feedback' => true,
					'relatives' => true,
					'related_to' => true,
					'credits' => true,
					'preregistrations' => true,
					'registrations' => true,
					'tasks' => true,
					'uploads' => true,
					'waivers' => true,
				];
			}

			if ($is_manager || $is_me) {
				$visible += [
					'gender_display' => true,
					'publish_gender' => true,
					'legal_name' => true,
					'pronouns' => true,
					'publish_pronouns' => true,
				];
			}

			if ($is_coordinator || $is_captain) {
				$visible += [
					'height' => true,
					'shirt_size' => true,
				];
			}

			if ($is_manager || $is_coordinator) {
				$visible += [
					'allstars' => true,
				];
			}

			// Check the things that can be published for people who are logged in
			if ($is_logged_in) {
				$visible += [
					'badges' => true,
					'skills' => true,
					'teams' => true,
				];

				if ($this->publish_email) {
					$visible += [
						'email' => true,
						'publish_email' => true,
					];
				}
				if ($this->publish_home_phone) {
					$visible += [
						'home_phone' => true,
						'publish_home_phone' => true,
					];
				}
				if ($this->publish_work_phone) {
					$visible += [
						'work_phone' => true,
						'work_ext' => true,
						'publish_work_phone' => true,
					];
				}
				if ($this->publish_mobile_phone) {
					$visible += [
						'mobile_phone' => true,
						'publish_mobile_phone' => true,
					];
				}
				if ($this->publish_alternate_email) {
					$visible += [
						'alternate_email' => true,
						'publish_alternate_email' => true,
					];
				}
				if ($this->publish_alternate_work_phone) {
					$visible += [
						'alternate_work_phone' => true,
						'alternate_work_ext' => true,
						'publish_alternate_work_phone' => true,
					];
				}
				if ($this->publish_alternate_mobile_phone) {
					$visible += [
						'alternate_mobile_phone' => true,
						'publish_alternate_mobile_phone' => true,
					];
				}
				if ($this->publish_gender) {
					$visible += [
						'gender_display' => true,
					];
				}
				if ($this->publish_pronouns) {
					$visible += [
						'pronouns' => true,
					];
				}
			}

			// Remove things based on disabled features
			if (!Configure::read('feature.registration')) {
				unset($visible['preregistrations']);
				unset($visible['registrations']);
				unset($visible['credits']);
			}
			if (!Configure::read('feature.badges')) {
				unset($visible['badges']);
			}
			if (Configure::read('feature.authenticate_through') == 'Zuluru') {
				unset($visible['user_id']);
			}
			if (!Configure::read('feature.documents')) {
				unset($visible['uploads']);
			}
			if (!Configure::read('feature.dog_questions')) {
				unset($visible['has_dog']);
			}
			if (!Configure::read('scoring.allstars') || !$is_player) {
				unset($visible['allstars']);
			}
			if (!Configure::read('profile.legal_name')) {
				unset($visible['legal_name']);
			}
			if (!Configure::read('profile.home_phone')) {
				unset($visible['home_phone']);
				unset($visible['publish_home_phone']);
			}
			if (!Configure::read('profile.work_phone')) {
				unset($visible['work_phone']);
				unset($visible['work_ext']);
				unset($visible['publish_work_phone']);
				unset($visible['alternate_work_phone']);
				unset($visible['alternate_work_ext']);
				unset($visible['publish_alternate_work_phone']);
			}
			if (!Configure::read('profile.mobile_phone')) {
				unset($visible['mobile_phone']);
				unset($visible['publish_mobile_phone']);
				unset($visible['alternate_mobile_phone']);
				unset($visible['publish_alternate_mobile_phone']);
			}
			if (!Configure::read('profile.addr_street')) {
				unset($visible['addr_street']);
			}
			if (!Configure::read('profile.addr_city')) {
				unset($visible['addr_city']);
			}
			if (!Configure::read('profile.addr_prov')) {
				unset($visible['addr_prov']);
			}
			if (!Configure::read('profile.addr_country')) {
				unset($visible['addr_country']);
			}
			if (!Configure::read('profile.addr_postalcode')) {
				unset($visible['addr_postalcode']);
			}
			if (!Configure::read('profile.pronouns')) {
				unset($visible['pronouns']);
				unset($visible['publish_pronouns']);
			}
			if (!Configure::read('profile.birthdate')) {
				unset($visible['birthdate']);
			}
			if (!Configure::read('profile.height')) {
				unset($visible['height']);
			}
			if (!Configure::read('profile.shirt_size')) {
				unset($visible['shirt_size']);
			}
			if (!Configure::read('profile.contact_for_feedback')) {
				unset($visible['contact_for_feedback']);
			}
			if (!Configure::read('profile.year_started') && !Configure::read('profile.skill_level')) {
				unset($visible['skills']);
			}

			// Remove fields that aren't applicable to certain types of accounts
			if (!$is_parent) {
				unset($visible['alternate_first_name']);
				unset($visible['alternate_last_name']);
				unset($visible['alternate_full_name']);
				unset($visible['alternate_work_phone']);
				unset($visible['alternate_work_ext']);
				unset($visible['publish_alternate_work_phone']);
				unset($visible['alternate_mobile_phone']);
				unset($visible['publish_alternate_mobile_phone']);
			}

			if (!$is_player) {
				unset($visible['birthdate']);
				unset($visible['gender_display']);
				unset($visible['publish_gender']);
				unset($visible['pronouns']);
				unset($visible['publish_pronouns']);
				unset($visible['height']);
				unset($visible['shirt_size']);
				unset($visible['skills']);
			}
		}

		$this->setHidden(array_diff($this->_hidden, array_keys($visible)));
	}

	public function photoUrl($photo) {
		if (!empty($photo)) {
			$upload_dir = Configure::read('App.paths.uploads');
			if (file_exists($upload_dir . DS . $photo->filename)) {
				return Router::url(['controller' => 'People', 'action' => 'photo', '?' => ['person' => $photo->person_id]], true);
			}
		} else if (Configure::read('feature.gravatar')) {
			$url = 'https://www.gravatar.com/avatar/';
			if ($this->show_gravatar) {
				$url .= md5(strtolower($this->email));
			} else {
				$url .= '00000000000000000000000000000000';
			}
			$url .= "?s=150&d=mm&r=pg";

			return $url;
		}
	}
}
