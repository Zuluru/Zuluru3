<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class DaysFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'days'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'id' => 1,
				'name' => 'Monday',
				'short_name' => 'Mon',
			],
			[
				'id' => 2,
				'name' => 'Tuesday',
				'short_name' => 'Tue',
			],
			[
				'id' => 3,
				'name' => 'Wednesday',
				'short_name' => 'Wed',
			],
			[
				'id' => 4,
				'name' => 'Thursday',
				'short_name' => 'Thu',
			],
			[
				'id' => 5,
				'name' => 'Friday',
				'short_name' => 'Fri',
			],
			[
				'id' => 6,
				'name' => 'Saturday',
				'short_name' => 'Sat',
			],
			[
				'id' => 7,
				'name' => 'Sunday',
				'short_name' => 'Sun',
			],
		];

		parent::init();
	}

}
