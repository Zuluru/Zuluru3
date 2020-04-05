<?php
use Migrations\AbstractSeed;

/**
 * RosterRoles seed.
 */
class RosterRolesSeed extends AbstractSeed {
	/**
	 * Data Method.
	 *
	 * @return mixed
	 */
	public function data() {
		return [
			[
				'name' => 'captain',
				'description' => __d('seeds', 'Captain'),
				'active' => '1',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '1',
			],
			[
				'name' => 'assistant',
				'description' => __d('seeds', 'Assistant captain'),
				'active' => '1',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '0',
			],
			[
				'name' => 'coach',
				'description' => __d('seeds', 'Non-playing coach'),
				'active' => '1',
				'is_player' => '0',
				'is_extended_player' => '0',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '1',
			],
			[
				'name' => 'social',
				'description' => __d('seeds', 'Social rep'),
				'active' => '0',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '0',
			],
			[
				'name' => 'spiritcaptain',
				'description' => __d('seeds', 'Spirit captain'),
				'active' => '0',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '0',
			],
			[
				'name' => 'ruleskeeper',
				'description' => __d('seeds', 'Rules keeper'),
				'active' => '0',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'player',
				'description' => __d('seeds', 'Regular player'),
				'active' => '1',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'substitute',
				'description' => __d('seeds', 'Substitute player'),
				'active' => '1',
				'is_player' => '0',
				'is_extended_player' => '1',
				'is_regular' => '0',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'cheerleader',
				'description' => __d('seeds', 'Cheerleader'),
				'active' => '0',
				'is_player' => '0',
				'is_extended_player' => '1',
				'is_regular' => '0',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'none',
				'description' => __d('seeds', 'Not on team'),
				'active' => '1',
				'is_player' => '0',
				'is_extended_player' => '0',
				'is_regular' => '0',
				'is_privileged' => '0',
				'is_required' => '0',
			],
		];
	}

	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$table = $this->table('roster_roles');
		$table->insert($this->data())->save();
	}
}
