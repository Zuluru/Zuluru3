<?php
use Migrations\AbstractSeed;

/**
 * Regions seed.
 */
class RegionsSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'name' => __d('seeds', 'North'),
			],
			[
				'name' => __d('seeds', 'South'),
			],
			[
				'name' => __d('seeds', 'East'),
			],
			[
				'name' => __d('seeds', 'West'),
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('regions');
		$table->insert($this->data())->save();
	}
}
