<?php
use Migrations\AbstractSeed;

/**
 * Regions seed.
 */
class RegionsSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'name' => 'North',
			],
			[
				'name' => 'South',
			],
			[
				'name' => 'East',
			],
			[
				'name' => 'West',
			],
		];

		$table = $this->table('regions');
		$table->insert($data)->save();
	}
}
