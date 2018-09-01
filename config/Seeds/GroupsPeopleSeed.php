<?php
use Migrations\AbstractSeed;

/**
 * Groups People seed.
 */
class GroupsPeopleSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'person_id' => 1,
				'group_id' => 7,
			],
		];

		$table = $this->table('groups_people');
		$table->insert($data)->save();
	}
}
