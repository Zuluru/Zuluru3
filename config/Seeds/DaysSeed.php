<?php
use Migrations\AbstractSeed;

/**
 * Days seed.
 */
class DaysSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
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

		$table = $this->table('days');
		$table->insert($data)->save();
	}
}
