<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * RegistrationsFixture
 *
 */
class RegistrationsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'registrations'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'person_id' => PERSON_ID_PLAYER,
				'event_id' => EVENT_ID_MEMBERSHIP,
				'created' => FrozenTime::now()->startOfYear(),
				'modified' => FrozenTime::now()->startOfYear(),
				'payment' => 'Paid',
				'notes' => null,
				'total_amount' => 10,
				'price_id' => PRICE_ID_MEMBERSHIP,
				'deposit_amount' => null,
				'reservation_expires' => null,
				'delete_on_expiry' => false,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN,
				'event_id' => EVENT_ID_MEMBERSHIP,
				'created' => FrozenTime::now()->startOfYear(),
				'modified' => FrozenTime::now()->startOfYear(),
				'payment' => 'Partial',
				'notes' => null,
				'total_amount' => 11.50,
				'price_id' => PRICE_ID_MEMBERSHIP,
				'deposit_amount' => null,
				'reservation_expires' => null,
				'delete_on_expiry' => false,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN2,
				'event_id' => EVENT_ID_MEMBERSHIP,
				'created' => FrozenTime::now()->startOfYear(),
				'modified' => FrozenTime::now()->startOfYear(),
				'payment' => 'Paid',
				'notes' => null,
				'total_amount' => 11.50,
				'price_id' => PRICE_ID_MEMBERSHIP,
				'deposit_amount' => null,
				'reservation_expires' => null,
				'delete_on_expiry' => false,
			],
			[
				'person_id' => PERSON_ID_COORDINATOR,
				'event_id' => EVENT_ID_MEMBERSHIP,
				'created' => FrozenTime::now()->startOfYear(),
				'modified' => FrozenTime::now()->startOfYear(),
				'payment' => 'Unpaid',
				'notes' => null,
				'total_amount' => 57.50,
				'price_id' => PRICE_ID_MEMBERSHIP2,
				'deposit_amount' => 0,
				'reservation_expires' => null,
				'delete_on_expiry' => false,
			],
			[
				'person_id' => PERSON_ID_MANAGER,
				'event_id' => EVENT_ID_MEMBERSHIP,
				'created' => FrozenTime::now()->startOfYear(),
				'modified' => FrozenTime::now()->startOfYear(),
				'payment' => 'Unpaid',
				'notes' => null,
				'total_amount' => 11.50,
				'price_id' => PRICE_ID_MEMBERSHIP,
				'deposit_amount' => 2,
				'reservation_expires' => null,
				'delete_on_expiry' => false,
			],
			[
				'person_id' => PERSON_ID_CHILD,
				'event_id' => EVENT_ID_MEMBERSHIP,
				'created' => FrozenTime::now()->startOfYear(),
				'modified' => FrozenTime::now()->startOfYear(),
				'payment' => 'Pending',
				'notes' => null,
				'total_amount' => 11.50,
				'price_id' => PRICE_ID_MEMBERSHIP,
				'deposit_amount' => 0,
				'reservation_expires' => FrozenTime::now()->addMinutes(10),
				'delete_on_expiry' => false,
			],
			[
				'person_id' => PERSON_ID_PLAYER,
				'event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY,
				'created' => FrozenTime::now()->startOfYear(),
				'modified' => FrozenTime::now()->startOfYear(),
				'payment' => 'Paid',
				'notes' => null,
				'total_amount' => 57.50,
				'price_id' => PRICE_ID_LEAGUE_INDIVIDUAL_THURSDAY,
				'deposit_amount' => null,
				'reservation_expires' => null,
				'delete_on_expiry' => false,
			],
			[
				'person_id' => PERSON_ID_ANDY_SUB,
				'event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB,
				'created' => FrozenTime::now()->startOfYear(),
				'modified' => FrozenTime::now()->startOfYear(),
				'payment' => 'Unpaid',
				'notes' => null,
				'total_amount' => 57.50,
				'price_id' => PRICE_ID_LEAGUE_INDIVIDUAL_SUB,
				'deposit_amount' => 2,
				'reservation_expires' => null,
				'delete_on_expiry' => false,
			],
			[
				'person_id' => PERSON_ID_CAPTAIN2,
				'event_id' => EVENT_ID_LEAGUE_TEAM,
				'created' => FrozenTime::now()->startOfYear(),
				'modified' => FrozenTime::now()->startOfYear(),
				'payment' => 'Paid',
				'notes' => null,
				'total_amount' => 575,
				'price_id' => PRICE_ID_LEAGUE_TEAM,
				'deposit_amount' => null,
				'reservation_expires' => null,
				'delete_on_expiry' => false,
			],
		];

		if (!defined('REGISTRATION_ID_PLAYER_MEMBERSHIP')) {
			$i = 0;
			define('REGISTRATION_ID_PLAYER_MEMBERSHIP', ++$i);
			define('REGISTRATION_ID_CAPTAIN_MEMBERSHIP', ++$i);
			define('REGISTRATION_ID_CAPTAIN2_MEMBERSHIP', ++$i);
			define('REGISTRATION_ID_COORDINATOR_MEMBERSHIP', ++$i);
			define('REGISTRATION_ID_MANAGER_MEMBERSHIP', ++$i);
			define('REGISTRATION_ID_CHILD_MEMBERSHIP', ++$i);
			define('REGISTRATION_ID_PLAYER_INDIVIDUAL_THURSDAY', ++$i);
			define('REGISTRATION_ID_ANDY_SUB_INDIVIDUAL', ++$i);
			define('REGISTRATION_ID_CAPTAIN2_TEAM', ++$i);
			// This must always be the last one in the list: it is for new
			// records created in registration tests
			define('REGISTRATION_ID_NEW', ++$i);
		}

		parent::init();
	}

}
