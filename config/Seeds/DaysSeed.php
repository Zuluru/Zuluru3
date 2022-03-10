<?php

use Cake\Chronos\ChronosInterface;
use Migrations\AbstractSeed;

/**
 * Days seed.
 */
class DaysSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'id' => ChronosInterface::MONDAY,
				'name' => __d('seeds', 'Monday'),
				'short_name' => __d('seeds', 'Mon'),
			],
			[
				'id' => ChronosInterface::TUESDAY,
				'name' => __d('seeds', 'Tuesday'),
				'short_name' => __d('seeds', 'Tue'),
			],
			[
				'id' => ChronosInterface::WEDNESDAY,
				'name' => __d('seeds', 'Wednesday'),
				'short_name' => __d('seeds', 'Wed'),
			],
			[
				'id' => ChronosInterface::THURSDAY,
				'name' => __d('seeds', 'Thursday'),
				'short_name' => __d('seeds', 'Thu'),
			],
			[
				'id' => ChronosInterface::FRIDAY,
				'name' => __d('seeds', 'Friday'),
				'short_name' => __d('seeds', 'Fri'),
			],
			[
				'id' => ChronosInterface::SATURDAY,
				'name' => __d('seeds', 'Saturday'),
				'short_name' => __d('seeds', 'Sat'),
			],
			[
				'id' => ChronosInterface::SUNDAY,
				'name' => __d('seeds', 'Sunday'),
				'short_name' => __d('seeds', 'Sun'),
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('days');
		$table->insert($this->data())->save();
	}
}
