<?php
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
				'id' => 1,
				'name' => __d('seeds', 'Monday'),
				'short_name' => __d('seeds', 'Mon'),
			],
			[
				'id' => 2,
				'name' => __d('seeds', 'Tuesday'),
				'short_name' => __d('seeds', 'Tue'),
			],
			[
				'id' => 3,
				'name' => __d('seeds', 'Wednesday'),
				'short_name' => __d('seeds', 'Wed'),
			],
			[
				'id' => 4,
				'name' => __d('seeds', 'Thursday'),
				'short_name' => __d('seeds', 'Thu'),
			],
			[
				'id' => 5,
				'name' => __d('seeds', 'Friday'),
				'short_name' => __d('seeds', 'Fri'),
			],
			[
				'id' => 6,
				'name' => __d('seeds', 'Saturday'),
				'short_name' => __d('seeds', 'Sat'),
			],
			[
				'id' => 7,
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
