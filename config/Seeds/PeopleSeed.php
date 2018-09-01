<?php
use Migrations\AbstractSeed;

/**
 * People seed.
 */
class PeopleSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'id' => 1,
				'user_id' => 1,
				'first_name' => 'Administrator',
				'last_name' => '',
				'publish_email' => 1,
				'status' => 'active',
				'created' => \Cake\I18n\FrozenDate::now(),
			],
		];

		$table = $this->table('people');
		$table->insert($data)->save();
	}
}
