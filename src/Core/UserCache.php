<?php
/**
 * Component for helping with cached user data.
 * TODO: Make this a trait instead? Some models, etc. now use it through getInstance.
 */
namespace App\Core;

use App\Model\Entity\User;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Controller\AppController;
use Cake\Routing\Router;

class UserCache {

	private static $instance = null;
	private static $identity = null;
	private $my_id = null;
	private $other_id = null;
	private $data = [];

	public static function &getInstance($reset = false): UserCache {
		if ($reset || !self::$instance) {
			self::$instance = new UserCache();
		}
		return self::$instance;
	}

	public static function setIdentity(User $identity = null) {
		self::$identity = $identity;
		self::getInstance()->initializeData();
	}

	public static function identitySet() {
		return self::$identity !== null;
	}

	public function initializeData() {
		$this->my_id = null;
		$this->other_id = null;
		$this->data = [];
	}

	// TODO: Is there a better way to handle all of this?
	public function initializeIdForTests($my_id) {
		if (Router::getRequest()) {
			throw new \Exception('initializeIdForTests is only for use in non-controller tests.');
		}
		$this->my_id = $my_id;
	}

	public function initializeId() {
		if ($this->my_id) {
			return;
		}

		if (self::$identity && self::$identity->person) {
			$this->my_id = self::$identity->person->id;
		} else {
			$this->my_id = null;
		}

		if ($this->my_id) {
			$this->data[$this->my_id] = [];
		}
	}

	public function currentId() {
		$self =& UserCache::getInstance();
		$self->initializeId();
		return $self->my_id;
	}

	public function realId() {
		if (self::$identity) {
			if (self::$identity->real_person) {
				return self::$identity->real_person->id;
			} else {
				return self::$identity->person->id;
			}
		} else {
			return null;
		}
	}

	public function read($key, $id = null, $internal = false) {
		$self =& UserCache::getInstance();
		if (!$id) {
			$self->initializeId();
			$id = $self->my_id;
			if (!$id) {
				return ($internal ? false : []);
			}
		}

		// We always have our own id as a key in the data array, so if
		// the new key doesn't exist, we'll throw away anything we might
		// have had before, so that we only keep one other user's data
		// in the memory cache. This prevents massive memory usage.
		if (!array_key_exists($id, $self->data)) {
			if ($self->other_id) {
				unset($self->data[$self->other_id]);
			}
			$self->other_id = $id;
			$self->data[$id] = [];
		}

		if (strpos($key, '.') !== false) {
			list($key, $subkey) = explode('.', $key);
		} else {
			$subkey = null;
		}

		if (array_key_exists($key, $self->data[$id])) {
			if ($internal) {
				return true;
			} else if ($subkey) {
				return $self->data[$id][$key][$subkey];
			} else {
				return $self->data[$id][$key];
			}
		}

		$self->data[$id] = Cache::read("person_$id", 'long_term');
		if (!$self->data[$id]) {
			$self->data[$id] = [];
		}

		// Find any data that we don't already have cached
		if (!array_key_exists($key, $self->data[$id])) {
			switch ($key) {
				case 'Affiliates':
					$affiliates_table = TableRegistry::getTableLocator()->get('Affiliates');
					$self->data[$id][$key] = $affiliates_table->readByPlayerId($id);

					// If affiliates are disabled, make sure that they are in affiliate 1
					if (empty($self->data[$id][$key]) && !Configure::read('feature.affiliates')) {
						$affiliates_table->AffiliatesPeople->save($affiliates_table->AffiliatesPeople->newEntity(['person_id' => $id, 'affiliate_id' => AFFILIATE_DUMMY]));
						$self->data[$id][$key] = $affiliates_table->readByPlayerId($id);
					}
					break;

				case 'AffiliateIDs':
					if ($self->read('Affiliates', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['Affiliates'])->extract('id')->toArray();
					}
					break;

				case 'AllOwnedTeams':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Teams')->find()
						->contain(['Divisions' => [
							'queryBuilder' => function (Query $q) {
								return $q->find('translations');
							},
							'Leagues' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
							],
						]])
						->matching('People', function (Query $q) use ($id) {
							return $q
								->where(['People.id' => $id]);
						})
						->where([
							'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
							'TeamsPeople.status' => ROSTER_APPROVED,
						])
						->toArray();
					break;

				case 'AllOwnedTeamIDs':
					if ($self->read('AllOwnedTeams', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['AllOwnedTeams'])->extract('id')->toArray();
					}
					break;

				case 'AllRelativeTeamIDs':
					$relatives = $this->read('RelativeIDs', $id);
					if (!empty($relatives)) {
						$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Teams')->find()
							->matching('People', function (Query $q) use ($relatives) {
								return $q
									->where(['People.id IN' => $relatives]);
							})
							->all()
							->combine('id', 'id')
							->toArray();
					}
					break;

				case 'AllTeams':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Teams')->readByPlayerId($id, false);
					break;

				case 'AllTeamIDs':
					if ($self->read('AllTeams', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['AllTeams'])->extract('id')->toArray();
					}
					break;

				case 'AcceptedTeamIDs':
					if ($self->read('Teams', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['Teams'])->filter(function ($team) {
							return ($team->_matchingData['TeamsPeople']->status == ROSTER_APPROVED);
						})->extract('id')->toArray();
					}
					break;

				case 'Credits':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Credits')->find()
						->where([
							'person_id' => $id,
							'amount != amount_used',
						])
						->toArray();
					break;

				case 'Divisions':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Divisions')->readByPlayerId($id, true, true);
					break;

				case 'DivisionIDs':
					if ($self->read('Divisions', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['Divisions'])->extract('id')->toArray();
					}
					break;

				case 'Documents':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Uploads')->find()
						->contain([
							'UploadTypes' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
							]
						])
						->where([
							'person_id' => $id,
							'type_id IS NOT' => null,
						])
						->toArray();
					break;

				case 'Franchises':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Franchises')->readByPlayerId($id);
					break;

				case 'FranchiseIDs':
					if ($self->read('Franchises', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['Franchises'])->extract('id')->toArray();
					}
					break;

				case 'Groups':
					if ($self->read('Person', $id, true)) {
						$self->data[$id][$key] = $self->data[$id]['Person']->groups;
					}
					break;

				case 'GroupIDs':
					if ($self->read('Groups', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['Groups'])->extract('id')->toArray();
					}
					break;

				case 'ManagedAffiliates':
					if ($self->read('Affiliates', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['Affiliates'])->match(['_matchingData.AffiliatesPeople.position' => 'manager'])->toArray();
					}
					break;

				case 'ManagedAffiliateIDs':
					if ($self->read('ManagedAffiliates', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['ManagedAffiliates'])->extract('id')->toArray();
					}
					break;

				case 'OwnedTeams':
					if ($self->read('Teams', $id, true)) {
						$roles = Configure::read('privileged_roster_roles');
						$self->data[$id][$key] = collection($self->data[$id]['Teams'])->filter(function ($team) use ($roles) {
							return ($team->_matchingData['TeamsPeople']->status == ROSTER_APPROVED && in_array($team->_matchingData['TeamsPeople']->role, $roles));
						})->toArray();
					}
					break;

				case 'OwnedTeamIDs':
					if ($self->read('OwnedTeams', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['OwnedTeams'])->extract('id')->toArray();
					}
					break;

				case 'Person':
					try {
						$self->data[$id][$key] = TableRegistry::getTableLocator()->get('People')->get($id, [
							'contain' => [
								Configure::read('Security.authModel'),
								'Groups' => [
									'queryBuilder' => function (Query $q) {
										return $q->find('translations');
									},
								],
							]
						]);
					} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
						$self->data[$id][$key] = [];
					}
					break;

				case 'Preregistrations':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Preregistrations')->find()
						->contain(['Events' => [
							'queryBuilder' => function (Query $q) {
								return $q->find('translations');
							},
						]])
						->where(['person_id' => $id])
						->toArray();
					break;

				case 'Registrations':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Registrations')->find()
						->contain([
							'Events' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
								'EventTypes' => [
									'queryBuilder' => function (Query $q) {
										return $q->find('translations');
									},
								],
								'Prices' => [
									'queryBuilder' => function (Query $q) {
										return $q->find('translations');
									},
								],
							],
							'Prices' => [
								'queryBuilder' => function (Query $q) {
									return $q->find('translations');
								},
							],
							'Payments',
						])
						->where(['person_id' => $id])
						->order('created DESC')
						->toArray();
					break;

				case 'RegistrationsCanPay':
					if ($self->read('Registrations', $id, true)) {
						$payments = Configure::read('registration_delinquent');
						$self->data[$id][$key] = collection($self->data[$id]['Registrations'])->filter(function ($registration) use ($payments) {
							return in_array($registration->payment, $payments);
						})->toArray();
					}
					break;

				case 'RegistrationsPaid':
					if ($self->read('Registrations', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['Registrations'])->filter(function ($registration) {
							return ($registration->payment == 'Paid');
						})->toArray();
					}
					break;

				case 'RegistrationsReserved':
					if ($self->read('Registrations', $id, true)) {
						$payments = Configure::read('registration_reserved');
						$self->data[$id][$key] = collection($self->data[$id]['Registrations'])->filter(function ($registration) use ($payments) {
							return in_array($registration->payment, $payments);
						})->toArray();
					}
					break;

				case 'RegistrationsUnpaid':
					if ($self->read('Registrations', $id, true)) {
						$payments = Configure::read('registration_unpaid');
						$self->data[$id][$key] = collection($self->data[$id]['Registrations'])->filter(function ($registration) use ($payments) {
							return in_array($registration->payment, $payments);
						})->toArray();
					}
					break;

				case 'RelatedTo':
					try {
						$person = TableRegistry::getTableLocator()->get('People')->get($id, [
							'contain' => ['Related'],
						]);
						$self->data[$id][$key] = $person->related;
					} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
						$self->data[$id][$key] = [];
					}
					break;

				case 'RelatedToIDs':
					if ($self->read('RelatedTo', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['RelatedTo'])->match(['_joinData.approved' => true])->extract('id')->toArray();
					}
					break;

				case 'Relatives':
					try {
						$person = TableRegistry::getTableLocator()->get('People')->get($id, [
							'contain' => ['Relatives'],
						]);
						$self->data[$id][$key] = $person->relatives;
					} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
						$self->data[$id][$key] = [];
					}
					break;

				case 'RelativeIDs':
					if ($self->read('Relatives', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['Relatives'])->match(['_joinData.approved' => true])->extract('id')->toArray();
					}
					break;

				case 'RelativeTeamIDs':
					if ($self->read('Relatives', $id, true)) {
						$self->data[$id][$key] = [];
						foreach ($self->data[$id]['Relatives'] as $relative) {
							$self->data[$id][$key] = array_merge($self->data[$id][$key], $self->read('TeamIDs', $relative->id));
						}
						$self->data[$id][$key] = array_unique($self->data[$id][$key]);
					}
					break;

				case 'Skills':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Skills')->find()
						->where(['person_id' => $id])
						->order('Skills.sport')
						->toArray();
					break;

				case 'Tasks':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('TaskSlots')->find('assigned', ['person' => $id])->toArray();
					break;

				case 'Teams':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Teams')->readByPlayerId($id);
					break;

				case 'TeamIDs':
					if ($self->read('Teams', $id, true)) {
						$self->data[$id][$key] = collection($self->data[$id]['Teams'])->extract('id')->toArray();
					}
					break;

				case 'User':
					$user_id = $this->read('Person.user_id');
					try {
						if ($user_id) {
							$self->data[$id][$key] = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . Configure::read('Security.authModel'))->get($user_id, [
								'contain' => ['People']
							]);
						} else {
							$user = TableRegistry::getTableLocator()->get(Configure::read('Security.authPlugin') . Configure::read('Security.authModel'))->newEmptyEntity();
							$user->person = TableRegistry::getTableLocator()->get('People')->get($id);
							$self->data[$id][$key] = $user;
						}
					} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
						$self->data[$id][$key] = [];
					}
					break;

				case 'Waivers':
					$self->data[$id][$key] = TableRegistry::getTableLocator()->get('Waivers')->find('translations')
						->matching('People', function (Query $q) use ($id) {
							return $q
								->where(['People.id' => $id]);
						})
						->toArray();
					break;

				case 'WaiversCurrent':
					if ($self->read('Waivers', $id, true)) {
						$date = FrozenDate::now();
						$self->data[$id][$key] = collection($self->data[$id]['Waivers'])->filter(function ($waiver) use ($date) {
							return $date->between($waiver->_matchingData['WaiversPeople']->valid_from, $waiver->_matchingData['WaiversPeople']->valid_until);
						})->toArray();
					}
					break;

				default:
					trigger_error("Read $key", E_USER_ERROR);
			}

			// Make sure that anything empty is an array, as that's what everything will want.
			if (empty($self->data[$id][$key])) {
				$self->data[$id][$key] = [];
			}
			Cache::write("person_$id", $self->data[$id], 'long_term');
		}

		if (!$self->data[$id][$key]) {
			if ($subkey) {
				return ($internal ? false : null);
			} else {
				return ($internal ? false : []);
			}
		} else if ($internal) {
			return true;
		} else if ($subkey) {
			return $self->data[$id][$key][$subkey];
		} else {
			return $self->data[$id][$key];
		}
	}

	public function clear($key, $id = null) {
		$self =& UserCache::getInstance();
		if (!$id) {
			$self->initializeId();
			$id = $self->my_id;
			if (!$id) {
				return;
			}
		}

		if (empty($self->data[$id])) {
			$self->data[$id] = Cache::read("person_$id", 'long_term');
			if (empty($self->data[$id])) {
				$self->data[$id] = [];
			}
		}

		if (strpos($key, '.') !== false) {
			list($key, $subkey) = explode('.', $key);
		} else {
			$subkey = null;
		}

		if (!array_key_exists($key, $self->data[$id]) || (!empty($subkey) && !array_key_exists($subkey, $self->data[$id][$key]))) {
			return;
		}

		if ($subkey) {
			unset($self->data[$id][$key][$subkey]);
		} else {
			unset($self->data[$id][$key]);
		}

		Cache::write("person_$id", $self->data[$id], 'long_term');
	}

	public static function delete($id) {
		Cache::delete("person_$id", 'long_term');
	}

	public function allActAs($for_menu = false, $field = 'full_name') {
		$act_as = [];
		if (!$this->currentId()) {
			return $act_as;
		}

		$include = [$this->currentId() => true];

		// If we're acting as someone, maybe add the real user and their relatives
		if ($this->currentId() != $this->realId()) {
			if (in_array($this->realId(), $this->read('RelatedToIDs'))) {
				// If the user is a relative, assume it's a parent acting as a child or similar
				$include[$this->realId()] = true;
			} else if (AppController::_isChild($this->read('Person'))) {
				// Otherwise, assume it's an admin, and if it's a youth account, find the first parent
				$related = $this->read('RelatedToIDs');
				if (!empty($related)) {
					$include[min($related)] = true;
				}
			}
		}

		if (!$for_menu) {
			// If this is not for a menu, we want the real user last, if not already in the list.
			// This will put admins last when acting as someone else.
			$include[$this->realId()] = true;
		}

		// Add the included user and their relatives relatives
		foreach (array_keys($include) as $id) {
			$act_as[$id] = $this->read("Person.$field", $id);
			$relatives = $this->read('Relatives', $id);
			foreach ($relatives as $relative) {
				if ($relative['_joinData']['approved']) {
					$act_as[$relative['id']] = $relative[$field];
				}
			}
		}

		// And finally remove the current user, if present; they get special treatment everywhere
		unset($act_as[$this->currentId()]);

		return $act_as;
	}

	/**
	 * Delete all of the cached information related to teams.
	 */
	public function _deleteTeamData($id = null) {
		$this->clear('Teams', $id);
		$this->clear('TeamIDs', $id);
		$this->clear('AcceptedTeamIDs', $id);
		$this->clear('AllTeams', $id);
		$this->clear('AllTeamIDs', $id);
		$this->clear('OwnedTeams', $id);
		$this->clear('OwnedTeamIDs', $id);
		$this->clear('AllOwnedTeams', $id);
		$this->clear('AllOwnedTeamIDs', $id);
	}

	/**
	 * Delete all of the cached information related to franchises.
	 */
	public function _deleteFranchiseData($id = null) {
		$this->clear('Franchises', $id);
		$this->clear('FranchiseIDs', $id);
	}

	/**
	 * Delete all of the cached information related to registrations.
	 */
	public function _deleteRegistrationData($id = null) {
		$this->clear('Preregistrations', $id);
		$this->clear('Registrations', $id);
		$this->clear('RegistrationsCanPay', $id);
		$this->clear('RegistrationsPaid', $id);
		$this->clear('RegistrationsReserved', $id);
		$this->clear('RegistrationsUnpaid', $id);
	}

}
