<?php
use Migrations\AbstractSeed;

/**
 * Affiliates People seed.
 */
class AffiliatesPeopleSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'affiliate_id' => 1,
				'person_id' => 1,
			],
		];

		$table = $this->table('affiliates_people');
		$table->insert($data)->save();
	}
}
