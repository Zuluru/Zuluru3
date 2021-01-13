<?php


namespace App\Test\Fixture_deprecated;


use Cake\TestSuite\Fixture\TestFixture;

class GroupsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'groups'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' 			=> 'Player',
				'active'		=> true,
				'level'			=> 0,
				'description' 	=> 	'You will be participating as a player.'
			],
			[
				'name' 			=> 'Parent/Guardian',
				'active'		=> true,
				'level'			=> 0,
				'description' 	=> 	'You have one or more children who will be participating as players.'
			],
			[
				'name' 			=> 'Coach',
				'active'		=> true,
				'level'			=> 0,
				'description' 	=> 	'You will be coaching a team that you are not a player on.'
			],
			[
				'name' 			=> 'Volunteer',
				'active'		=> true,
				'level'			=> 1,
				'description' 	=> 	'You plan to volunteer to help organize or run things.'
			],
			[
				'name' 			=> 'Official',
				'active'		=> false,
				'level'			=> 3,
				'description' 	=> 	'You will be acting as an in-game official.'
			],
			[
				'name' 			=> 'Manager',
				'active'		=> true,
				'level'			=> 5,
				'description' 	=> 	'You are an organizational manager with some admin privileges.'
			],
			[
				'name' 			=> 'Administrator',
				'active'		=> true,
				'level'			=> 10,
				'description' 	=> 	'You are an organizational administrator with absolute privileges.'
			]
		];

		if (!defined('GROUP_ID_')) {
			$i = 0;
			define('GROUP_ID_PLAYER', ++$i);
			define('GROUP_ID_PARENT', ++$i);
			define('GROUP_ID_COACH', ++$i);
			define('GROUP_ID_VOLUNTEER', ++$i);
			define('GROUP_ID_OFFICIAL', ++$i);
			define('GROUP_ID_MANAGER', ++$i);
			define('GROUP_ID_ADMINISTRATOR', ++$i);
		}

		parent::init();
	}

}
