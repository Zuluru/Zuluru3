<?php
use Migrations\AbstractSeed;

/**
 * Groups seed.
 */
class GroupsSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'name' => 'Player',
				'active' => 1,
				'level' => 0,
				'description' => 'You will be participating as a player.',
			],
			[
				'name' => 'Parent/Guardian',
				'active' => 1,
				'level' => 0,
				'description' => 'You have one or more children who will be participating as players.',
			],
			[
				'name' => 'Coach',
				'active' => 1,
				'level' => 0,
				'description' => 'You will be coaching a team that you are not a player on.',
			],
			[
				'name' => 'Volunteer',
				'active' => 1,
				'level' => 1,
				'description' => 'You plan to volunteer to help organize or run things.',
			],
			[
				'name' => 'Official',
				'active' => 1,
				'level' => 3,
				'description' => 'You will be acting as an in-game official.',
			],
			[
				'name' => 'Manager',
				'active' => 1,
				'level' => 5,
				'description' => 'You are an organizational manager with some admin privileges.',
			],
			[
				'name' => 'Administrator',
				'active' => 1,
				'level' => 10,
				'description' => 'You are an organizational administrator with absolute privileges.',
			],
		];

		$table = $this->table('groups');
		$table->insert($data)->save();
	}
}
