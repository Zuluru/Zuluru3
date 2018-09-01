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
				'active' => true,
				'is_player' => true,
				'is_extended_player' => true,
				'is_regular' => true,
				'is_privileged' => true,
				'is_required' => true,
			],
			[
				'name' => 'assistant',
				'description' => __('Assistant captain'),
				'active' => true,
				'is_player' => true,
				'is_extended_player' => true,
				'is_regular' => true,
				'is_privileged' => true,
				'is_required' => false,
			],
			[
				'name' => 'coach',
				'description' => __('Non-playing coach'),
				'active' => true,
				'is_player' => false,
				'is_extended_player' => false,
				'is_regular' => true,
				'is_privileged' => true,
				'is_required' => true,
			],
			[
				'name' => 'social',
				'description' => __('Social rep'),
				'active' => false,
				'is_player' => true,
				'is_extended_player' => true,
				'is_regular' => true,
				'is_privileged' => true,
				'is_required' => false,
			],
			[
				'name' => 'spiritcaptain',
				'description' => __('Spirit captain'),
				'active' => false,
				'is_player' => true,
				'is_extended_player' => true,
				'is_regular' => true,
				'is_privileged' => true,
				'is_required' => false,
			],
			[
				'name' => 'ruleskeeper',
				'description' => __('Rules keeper'),
				'active' => false,
				'is_player' => true,
				'is_extended_player' => true,
				'is_regular' => true,
				'is_privileged' => false,
				'is_required' => false,
			],
			[
				'name' => 'player',
				'description' => __('Regular player'),
				'active' => true,
				'is_player' => true,
				'is_extended_player' => true,
				'is_regular' => true,
				'is_privileged' => false,
				'is_required' => false,
			],
			[
				'name' => 'substitute',
				'description' => __('Substitute player'),
				'active' => true,
				'is_player' => false,
				'is_extended_player' => true,
				'is_regular' => false,
				'is_privileged' => false,
				'is_required' => false,
			],
			[
				'name' => 'cheerleader',
				'description' => __('Cheerleader'),
				'active' => false,
				'is_player' => false,
				'is_extended_player' => true,
				'is_regular' => false,
				'is_privileged' => false,
				'is_required' => false,
			],
			[
				'name' => 'none',
				'description' => __('Not on team'),
				'active' => true,
				'is_player' => false,
				'is_extended_player' => false,
				'is_regular' => false,
				'is_privileged' => false,
				'is_required' => false,
			],
		];

		$table = $this->table('roster_roles');
		$table->insert($data)->save();
	}
}
