<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PreregistrationsFixture
 *
 */
class PreregistrationsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'preregistrations'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'person_id' => PERSON_ID_ADMIN,
				'event_id' => EVENT_ID_MEMBERSHIP,
			],
			[
				'person_id' => PERSON_ID_DUPLICATE,
				'event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB,
			],
		];

		if (!defined('PREREGISTRATION_ID_ADMIN_MEMBERSHIP')) {
			$i = 0;
			define('PREREGISTRATION_ID_ADMIN_MEMBERSHIP', ++$i);
			define('PREREGISTRATION_ID_DUPLICATE_LEAGUE_INDIVIDUAL_SUB', ++$i);
		}

		parent::init();
	}

}
