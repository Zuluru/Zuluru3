<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * PaymentsFixture
 *
 */
class PaymentsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'payments'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'registration_id' => REGISTRATION_ID_PLAYER_MEMBERSHIP,
				'registration_audit_id' => 1,
				'payment_type' => 'Full',
				'payment_amount' => 11.50,
				'refunded_amount' => 0,
				'notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'created' => FrozenDate::now(),
				'created_person_id' => PERSON_ID_CAPTAIN,
				'updated_person_id' => PERSON_ID_CAPTAIN,
				'payment_method' => 'Online',
			],
			[
				'registration_id' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
				'registration_audit_id' => 1,
				'payment_type' => 'Deposit',
				'payment_amount' => 5,
				'refunded_amount' => 0,
				'notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'created' => FrozenDate::now(),
				'created_person_id' => PERSON_ID_PLAYER,
				'updated_person_id' => PERSON_ID_PLAYER,
				'payment_method' => 'Online',
			],
			[
				'registration_id' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
				'registration_audit_id' => 1,
				'payment_type' => 'Installment',
				'payment_amount' => 5,
				'refunded_amount' => 0,
				'notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'created' => FrozenDate::now(),
				'created_person_id' => PERSON_ID_PLAYER,
				'updated_person_id' => PERSON_ID_PLAYER,
				'payment_method' => 'Other',
			],
		];

		if (!defined('PAYMENT_ID_PLAYER_MEMBERSHIP')) {
			$i = 0;
			define('PAYMENT_ID_PLAYER_MEMBERSHIP', ++$i);
			define('PAYMENT_ID_CAPTAIN_MEMBERSHIP_1', ++$i);
			define('PAYMENT_ID_CAPTAIN_MEMBERSHIP_2', ++$i);
		}

		parent::init();
	}

}
