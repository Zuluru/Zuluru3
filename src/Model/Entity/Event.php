<?php
namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Core\ModuleRegistry;

/**
 * Event Entity.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $event_type_id
 * @property \Cake\I18n\FrozenTime $open
 * @property \Cake\I18n\FrozenTime $close
 * @property int $open_cap
 * @property int $women_cap
 * @property bool $multiple
 * @property int $questionnaire_id
 * @property string $custom
 * @property int $division_id
 * @property int $affiliate_id
 *
 * @property \App\Model\Entity\EventType $event_type
 * @property \App\Model\Entity\Questionnaire $questionnaire
 * @property \App\Model\Entity\Division $division
 * @property \App\Model\Entity\Affiliate $affiliate
 * @property \App\Model\Entity\Preregistration[] $preregistrations
 * @property \App\Model\Entity\Price[] $prices
 * @property \App\Model\Entity\Registration[] $registrations
 * @property \App\Model\Entity\Event[] $predecessor
 * @property \App\Model\Entity\Event[] $successor
 * @property \App\Model\Entity\Event[] $alternate
 * @property \App\Model\Entity\Event[] $predecessor_to
 * @property \App\Model\Entity\Event[] $successor_to
 * @property \App\Model\Entity\Event[] $alternate_to
 *
 * @property \Cake\I18n\FrozenDate $membership_begins
 * @property \Cake\I18n\FrozenDate $membership_ends
 * @property string $membership_type
 * @property string $level_of_play
 * @property bool $ask_status
 * @property bool $ask_attendance
 * @property \App\Model\Entity\Person $people
 */
class Event extends Entity {

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
	 * Custom data fields extracted from the encoded "custom" field.
	 *
	 * @var array
	 */
	//private $_custom = [];

	public function __construct(array $properties = [], array $options = []) {
		parent::__construct($properties, $options);

		// Make sure whatever custom virtual fields we have are included when we convert to arrays
		// TODO: Use hints at http://book.cakephp.org/3.0/en/orm/saving-data.html#saving-complex-types instead
		if (!empty($this->custom)) {
			$this->_custom = unserialize($this->custom);
			if (!empty($this->_custom)) {
				$this->_virtual = array_keys($this->_custom);
			}
		}
	}

	public function count($roster_designation, $conditions = [], $status = []) {
		if (empty($status)) {
			$status = Configure::read('registration_reserved');
		}

		$paid = TableRegistry::get('Registrations')->find()
			->where([
				'Registrations.event_id' => $this->id,
				'Registrations.payment IN' => $status,
			]);
		if (!empty($conditions)) {
			$paid->andWhere($conditions);
		}
		if ($this->women_cap != CAP_COMBINED) {
			if ($roster_designation === null) {
				trigger_error('TODOTESTING', E_USER_WARNING);
				exit;
			}
			$paid->contain(['People'])
				->andWhere(['People.roster_designation' => $roster_designation]);
		}

		return $paid->count();
	}

	public function cap($roster_designation) {
		if ($this->women_cap == CAP_COMBINED) {
			return $this->open_cap;
		}
		if ($roster_designation === null) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}
		return ($roster_designation == 'Open' ? $this->open_cap : $this->women_cap);
	}

	// TODO: Simpler method, like Game entity?
	private function _getCustomField($field) {
		// Can't use "has" or getters on custom fields, as it will go recursive
		if (array_key_exists($field, $this->_properties)) {
			return $this->_properties[$field];
		}
		if ($this->has('_custom')) {
			$custom = $this->_custom;
			if (array_key_exists($field, $custom)) {
				return $custom[$field];
			}
		}
		return null;
	}

	protected function _getMembershipBegins() {
		$date = new \Cake\Database\Type\DateType();
		return $date->marshal($this->_getCustomField('membership_begins'));
	}

	protected function _getMembershipEnds() {
		$date = new \Cake\Database\Type\DateType();
		return $date->marshal($this->_getCustomField('membership_ends'));
	}

	protected function _getMembershipType() {
		return $this->_getCustomField('membership_type');
	}

	protected function _getLevelOfPlay() {
		return $this->_getCustomField('level_of_play');
	}

	protected function _getAskStatus() {
		return $this->_getCustomField('ask_status');
	}

	protected function _getAskAttendance() {
		return $this->_getCustomField('ask_attendance');
	}

	/**
	 * @return Query List of people registered and paid for the event
	 */
	protected function _getPeople() {
		return TableRegistry::get('People')->find()
			->matching('Registrations', function (Query $q) {
				return $q->where([
					'Registrations.event_id' => $this->id,
					'Registrations.payment IN' => Configure::read('registration_paid'),
				]);
			});
	}

	public function mergeAutoQuestions($event_obj, $user_id = null, $for_output = false) {
		if ($this->questionnaire === null) {
			$this->questionnaire = new Questionnaire();
			$this->questionnaire->questions = [];
		}
		$this->questionnaire->questions = array_merge(
			$this->questionnaire->questions, $event_obj->registrationFields($this, $user_id, $for_output)
		);
	}

	/**
	 * @param null $skip_registration_id Registration id that was changed to unpaid and thus triggered this operation;
	 *          we don't want to put them at the front of the waiting list!
	 * @return bool
	 */
	public function processWaitingList($skip_registration_id = null) {
		if (!Configure::read('feature.waiting_list')) {
			return true;
		}

		if (!$this->has('event_type')) {
			TableRegistry::get('Events')->loadInto($this, ['EventTypes']);
		}
		$event_obj = ModuleRegistry::getInstance()->load("EventType:{$this->event_type->type}");

		if ($this->women_cap == CAP_COMBINED) {
			$this->processGenderWaitingList(null, $event_obj, $skip_registration_id);
		} else {
			$this->processGenderWaitingList('Open', $event_obj, $skip_registration_id);
			$this->processGenderWaitingList('Woman', $event_obj, $skip_registration_id);
		}
	}

	private function processGenderWaitingList($roster_designation, $event_obj, $skip_registration_id) {
		$registrations_table = TableRegistry::get('Registrations');

		$cap = $this->cap($roster_designation);
		if ($cap != CAP_UNLIMITED && $this->count($roster_designation) >= $cap ) {
			$unpaid = $registrations_table->find()
				->contain([
					'Events' => ['EventTypes'],
					'Prices',
					'People' => [Configure::read('Security.authModel')],
					'Responses',
				])
				->where([
					'Registrations.event_id' => $this->id,
					'Registrations.payment' => 'Unpaid',
				]);
			if ($skip_registration_id) {
				$unpaid->andWhere(['Registrations.id !=' => $skip_registration_id]);
			}
			if ($roster_designation) {
				$unpaid->andWhere(['People.roster_designation' => $roster_designation]);
			}

			foreach ($unpaid as $registration) {
				if (Configure::read('registration.delete_unpaid')) {
					// Remove any unpaid registrations for this event
					$registrations_table->delete($registration, ['from_waiting_list' => true]);

					$event = new CakeEvent('Model.Registration.registrationRemoved', $this, [$registration]);
					EventManager::instance()->dispatch($event);
				} else {
					// Move any unpaid registrations for this event to the waiting list
					$registration->payment = 'Waiting';
					$registrations_table->save($registration, ['event' => $registration->event, 'from_waiting_list' => true]);

					$event = new CakeEvent('Model.Registration.registrationWaitlisted', $this, [$registration]);
					EventManager::instance()->dispatch($event);
				}
			}
		}

		if ($cap == CAP_UNLIMITED || $this->count($roster_designation) < $cap) {
			$waiting = $registrations_table->find()
				->contain([
					'Events' => ['EventTypes'],
					'Prices',
					'People' => [Configure::read('Security.authModel')],
					'Responses',
				])
				->where([
					'Registrations.event_id' => $this->id,
					'Registrations.payment' => 'Waiting',
				])
				->order(['Registrations.id']);
			if ($skip_registration_id) {
				$waiting->andWhere(['Registrations.id !=' => $skip_registration_id]);
			}
			if ($roster_designation) {
				$waiting->andWhere(['People.roster_designation' => $roster_designation]);
			}
			if ($cap != CAP_UNLIMITED) {
				$waiting->limit($cap - $this->count($roster_designation)); // number of open spots
			}

			foreach ($waiting as $registration) {
				if (Configure::read('registration.reservation_time') > 0) {
					$expiry = FrozenTime::now()->addHours(Configure::read('registration.reservation_time'));
				} else {
					$expiry = null;
				}
				$registration = $registrations_table->patchEntity($registration, [
					'payment' => 'Reserved',
					'reservation_expires' => $expiry,
					'delete_on_expiry' => true,
				]);
				$registrations_table->save($registration, ['event' => $registration->event, 'from_waiting_list' => true]);

				$event = new CakeEvent('Model.Registration.registrationOpened', $this, [$registration]);
				EventManager::instance()->dispatch($event);
			}
		}
	}

}
