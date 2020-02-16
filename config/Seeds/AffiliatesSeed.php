<?php
use Migrations\AbstractSeed;

/**
 * Affiliates seed.
 */
class AffiliatesSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'id' => 1,
				'name' => __d('seeds', 'Club'),
				'active' => '1',
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('affiliates');
		$table->insert($this->data())->save();
	}
}
