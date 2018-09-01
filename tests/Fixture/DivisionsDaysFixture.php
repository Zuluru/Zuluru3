<?php
namespace App\Test\Fixture;

use Cake\Chronos\ChronosInterface;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * DivisionsDaysFixture
 *
 */
class DivisionsDaysFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'divisions_days'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'division_id' => DIVISION_ID_MONDAY_LADDER_PAST,
				'day_id' => ChronosInterface::MONDAY,
			],
			[
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'day_id' => ChronosInterface::MONDAY,
			],
			[
				'division_id' => DIVISION_ID_MONDAY_LADDER2,
				'day_id' => ChronosInterface::MONDAY,
			],
			[
				'division_id' => DIVISION_ID_MONDAY_PLAYOFF,
				'day_id' => ChronosInterface::SATURDAY,
			],
			[
				'division_id' => DIVISION_ID_MONDAY_PLAYOFF,
				'day_id' => ChronosInterface::SUNDAY,
			],
			[
				'division_id' => DIVISION_ID_TUESDAY_ROUND_ROBIN,
				'day_id' => ChronosInterface::TUESDAY,
			],
			[
				'division_id' => DIVISION_ID_THURSDAY_ROUND_ROBIN,
				'day_id' => ChronosInterface::THURSDAY,
			],
			[
				'division_id' => DIVISION_ID_FRIDAY,
				'day_id' => ChronosInterface::FRIDAY,
			],
			[
				'division_id' => DIVISION_ID_SUNDAY_SUB,
				'day_id' => ChronosInterface::SUNDAY,
			],
		];

		parent::init();
	}

}
