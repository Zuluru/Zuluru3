<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class RosterRolesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'roster_roles'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'captain',
				'description' => 'Captain',
				'active' => '1',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '1',
			],
			[
				'name' => 'assistant',
				'description' => 'Assistant captain',
				'active' => '1',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '0',
			],
			[
				'name' => 'coach',
				'description' => 'Non-playing coach',
				'active' => '1',
				'is_player' => '0',
				'is_extended_player' => '0',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '1',
			],
			[
				'name' => 'social',
				'description' => 'Social rep',
				'active' => '0',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '0',
			],
			[
				'name' => 'spiritcaptain',
				'description' => 'Spirit captain',
				'active' => '0',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '1',
				'is_required' => '0',
			],
			[
				'name' => 'ruleskeeper',
				'description' => 'Rules keeper',
				'active' => '0',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'player',
				'description' => 'Regular player',
				'active' => '1',
				'is_player' => '1',
				'is_extended_player' => '1',
				'is_regular' => '1',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'substitute',
				'description' => 'Substitute player',
				'active' => '1',
				'is_player' => '0',
				'is_extended_player' => '1',
				'is_regular' => '0',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'cheerleader',
				'description' => 'Cheerleader',
				'active' => '0',
				'is_player' => '0',
				'is_extended_player' => '1',
				'is_regular' => '0',
				'is_privileged' => '0',
				'is_required' => '0',
			],
			[
				'name' => 'none',
				'description' => 'Not on team',
				'active' => '1',
				'is_player' => '0',
				'is_extended_player' => '0',
				'is_regular' => '0',
				'is_privileged' => '0',
				'is_required' => '0',
			],
		];

		parent::init();
	}

}
