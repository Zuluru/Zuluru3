<?php
use Migrations\AbstractSeed;

/**
 * RosterRoles seed.
 */
class RosterRolesSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		$data = [
			[
				'name' => 'captain',
				'description' => __('Captain'),
				'active' => '1',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '1',
			],
			[
				'name' => 'assistant',
				'description' => __('Assistant captain'),
				'active' => '1',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '0',
			],
			[
				'name' => 'coach',
				'description' => __('Non-playing coach'),
				'active' => '1',
				'is_player' => '0',
				'is_extended_player' => '0',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '1',
			],
			[
				'name' => 'social',
				'description' => __('Social rep'),
				'active' => '0',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '0',
			],
			[
				'name' => 'spiritcaptain',
				'description' => __('Spirit captain'),
				'active' => '0',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '0',
			],
			[
				'name' => 'ruleskeeper',
				'description' => __('Rules keeper'),
				'active' => '0',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'player',
				'description' => __('Regular player'),
				'active' => '1',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'substitute',
				'description' => __('Substitute player'),
				'active' => '1',
				'is_player' => '0',
				'is_extended_player' => '1',
				'is_regular' => '0',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'cheerleader',
				'description' => __('Cheerleader'),
				'active' => '0',
				'is_player' => '0',
				'is_extended_player' => '1',
				'is_regular' => '0',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'none',
				'description' => __('Not on team'),
				'active' => '1',
				'is_player' => '0',
				'is_extended_player' => '0',
				'is_regular' => '0',
				'is_privileged' => '0',
				'is_required' => '0',
			],
		];

		$table = $this->table('roster_roles');
		$table->insert($data)->save();
	}
}
