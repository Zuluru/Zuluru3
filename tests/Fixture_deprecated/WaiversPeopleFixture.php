<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * WaiversPeopleFixture
 *
 */
class WaiversPeopleFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'waivers_people'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'person_id' => PERSON_ID_ADMIN,
				'created' => FrozenDate::now(),
				'waiver_id' => WAIVER_ID_ANNUAL,
				'valid_from' => FrozenTime::now()->startOfYear(),
				'valid_until' => FrozenTime::now()->endOfYear(),
			],
		];

		parent::init();
	}

}
