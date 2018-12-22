<?php
use Migrations\AbstractSeed;

/**
 * Affiliates seed.
 */
class AffiliatesSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'id' => 1,
				'name' => 'Club',
				'active' => '1',
			],
		];

		$table = $this->table('affiliates');
		$table->insert($data)->save();
	}
}
