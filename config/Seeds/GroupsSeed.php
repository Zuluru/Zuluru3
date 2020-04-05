<?php
use Migrations\AbstractSeed;

/**
 * Groups seed.
 */
class GroupsSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'name' => __d('seeds', 'Player'),
				'active' => '1',
				'level' => 0,
				'description' => __d('seeds', 'You will be participating as a player.'),
			],
			[
				'name' => __d('seeds', 'Parent/Guardian'),
				'active' => '1',
				'level' => 0,
				'description' => __d('seeds', 'You have one or more children who will be participating as players.'),
			],
			[
				'name' => __d('seeds', 'Coach'),
				'active' => '1',
				'level' => 0,
				'description' => __d('seeds', 'You will be coaching a team that you are not a player on.'),
			],
			[
				'name' => __d('seeds', 'Volunteer'),
				'active' => '1',
				'level' => 1,
				'description' => __d('seeds', 'You plan to volunteer to help organize or run things.'),
			],
			[
				'name' => __d('seeds', 'Official'),
				'active' => '1',
				'level' => 3,
				'description' => __d('seeds', 'You will be acting as an in-game official.'),
			],
			[
				'name' => __d('seeds', 'Manager'),
				'active' => '1',
				'level' => 5,
				'description' => __d('seeds', 'You are an organizational manager with some admin privileges.'),
			],
			[
				'name' => __d('seeds', 'Administrator'),
				'active' => '1',
				'level' => 10,
				'description' => __d('seeds', 'You are an organizational administrator with absolute privileges.'),
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('groups');
		$table->insert($this->data())->save();
	}
}
