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
		if (!defined('DAY_ID_MONDAY')) {
			$i = 0;
			define('DAY_ID_MONDAY', ++$i);
			define('DAY_ID_TUESDAY', ++$i);
			define('DAY_ID_WEDNESDAY', ++$i);
			define('DAY_ID_THURSDAY', ++$i);
			define('DAY_ID_FRIDAY', ++$i);
			define('DAY_ID_SATURDAY', ++$i);
			define('DAY_ID_SUNDAY', ++$i);
		}

		$this->records = [
			[
				'id' => DAY_ID_MONDAY,
				'name' => 'Monday',
				'short_name' => 'Mon',
			],
			[
				'id' => DAY_ID_TUESDAY,
				'name' => 'Tuesday',
				'short_name' => 'Tue',
			],
			[
				'id' => DAY_ID_WEDNESDAY,
				'name' => 'Wednesday',
				'short_name' => 'Wed',
			],
			[
				'id' => DAY_ID_THURSDAY,
				'name' => 'Thursday',
				'short_name' => 'Thu',
			],
			[
				'id' => DAY_ID_FRIDAY,
				'name' => 'Friday',
				'short_name' => 'Fri',
			],
			[
				'id' => DAY_ID_SATURDAY,
				'name' => 'Saturday',
				'short_name' => 'Sat',
			],
			[
				'id' => DAY_ID_SUNDAY,
				'name' => 'Sunday',
				'short_name' => 'Sun',
			],
		];

		parent::init();
	}

}
