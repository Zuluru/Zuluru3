<?php
use Migrations\AbstractSeed;

/**
 * Countries seed.
 */
class CountriesSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'name' => 'Canada',
			],
			[
				'name' => 'United States',
			],
		];

		$table = $this->table('countries');
		$table->insert($data)->save();
	}
}
