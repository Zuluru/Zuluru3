<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * PricesFixture
 *
 */
class PricesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'prices'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init(): void {
		$this->records = [
			[
				'event_id' => EVENT_ID_MEMBERSHIP,
				'name' => '',
				'description' => '',
				'cost' => 10,
				'tax1' => 0.70,
				'tax2' => 0.80,
				'open' => FrozenTime::now()->startOfYear(),
				'close' => FrozenTime::now()->endOfYear(),
				'register_rule' => '',
				'minimum_deposit' => 0,
				'allow_late_payment' => false,
				'online_payment_option' => ONLINE_FULL_PAYMENT,
				'allow_reservations' => false,
				'reservation_duration' => 0,
			],
			[
				'event_id' => EVENT_ID_MEMBERSHIP,
				'name' => 'price2',
				'description' => '',
				'cost' => 50,
				'tax1' => 3.50,
				'tax2' => 4.00,
				'open' => FrozenTime::now()->startOfYear(),
				'close' => FrozenTime::now()->endOfYear(),
				'register_rule' => '',
				'minimum_deposit' => 0,
				'allow_late_payment' => false,
				'online_payment_option' => ONLINE_NO_PAYMENT,
				'allow_reservations' => false,
				'reservation_duration' => 0,
			],
			[
				'event_id' => EVENT_ID_LEAGUE_TEAM,
				'name' => 'team',
				'description' => '',
				'cost' => 500,
				'tax1' => 35,
				'tax2' => 40,
				'open' => new FrozenTime('first Monday of April'),
				'close' => new FrozenTime('last Friday of April'),
				'register_rule' => '',
				'minimum_deposit' => 50,
				'allow_late_payment' => false,
				'online_payment_option' => ONLINE_MINIMUM_DEPOSIT,
				'allow_reservations' => true,
				'reservation_duration' => 1515,
			],
			[
				'event_id' => EVENT_ID_LEAGUE_TEAM,
				'name' => 'late team',
				'description' => '',
				'cost' => 550,
				'tax1' => 38.50,
				'tax2' => 44.00,
				'open' => (new FrozenTime('last Friday of April'))->addWeekday(),
				'close' => new FrozenTime('second Friday of May'),
				'register_rule' => '',
				'minimum_deposit' => 150,
				'allow_late_payment' => false,
				'online_payment_option' => ONLINE_MINIMUM_DEPOSIT,
				'allow_reservations' => true,
				'reservation_duration' => 1515,
			],
			[
				'event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY,
				'name' => 'individual',
				'description' => '',
				'cost' => 50,
				'tax1' => 3.50,
				'tax2' => 4.00,
				'open' => (new FrozenTime('last Friday of April'))->addWeekday(),
				'close' => new FrozenTime('second Friday of May'),
				'register_rule' => '',
				'minimum_deposit' => 0,
				'allow_late_payment' => false,
				'online_payment_option' => ONLINE_FULL_PAYMENT,
				'allow_reservations' => false,
				'reservation_duration' => 0,
			],
			[
				'event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_TUESDAY,
				'name' => 'individual',
				'description' => '',
				'cost' => 50,
				'tax1' => 3.50,
				'tax2' => 4.00,
				'open' => (new FrozenTime('last Friday of April'))->addWeekday(),
				'close' => new FrozenTime('second Friday of May'),
				'register_rule' => '',
				'minimum_deposit' => 0,
				'allow_late_payment' => false,
				'online_payment_option' => ONLINE_FULL_PAYMENT,
				'allow_reservations' => false,
				'reservation_duration' => 0,
			],
			[
				'event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY,
				'name' => 'individual',
				'description' => '',
				'cost' => 50,
				'tax1' => 3.50,
				'tax2' => 4.00,
				'open' => (new FrozenTime('last Friday of April'))->addWeekday(),
				'close' => new FrozenDate('second Friday of May'),
				'register_rule' => '',
				'minimum_deposit' => 0,
				'allow_late_payment' => false,
				'online_payment_option' => ONLINE_FULL_PAYMENT,
				'allow_reservations' => false,
				'reservation_duration' => 0,
			],
			[
				'event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB,
				'name' => 'league individual',
				'description' => '',
				'cost' => 50,
				'tax1' => 3.50,
				'tax2' => 4.00,
				'open' => new FrozenTime('first Monday of April'),
				'close' => new FrozenTime('last Friday of April'),
				'register_rule' => '',
				'minimum_deposit' => 0,
				'allow_late_payment' => false,
				'online_payment_option' => ONLINE_FULL_PAYMENT,
				'allow_reservations' => false,
				'reservation_duration' => 0,
			],
		];

		if (!defined('PRICE_ID_MEMBERSHIP')) {
			$i = 0;
			define('PRICE_ID_MEMBERSHIP', ++$i);
			define('PRICE_ID_MEMBERSHIP2', ++$i);
			define('PRICE_ID_LEAGUE_TEAM', ++$i);
			define('PRICE_ID_LEAGUE_TEAM2', ++$i);
			define('PRICE_ID_LEAGUE_INDIVIDUAL_MONDAY', ++$i);
			define('PRICE_ID_LEAGUE_INDIVIDUAL_TUESDAY', ++$i);
			define('PRICE_ID_LEAGUE_INDIVIDUAL_THURSDAY', ++$i);
			define('PRICE_ID_LEAGUE_INDIVIDUAL_SUB', ++$i);
		}

		parent::init();
	}

}
