<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PoolsTeamsFixture
 *
 */
class PoolsTeamsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'pools_teams'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'pool_id' => POOL_ID_MONDAY_SEEDED,
				'alias' => 'Lo',
				'dependency_type' => 'Lorem ipsum dolor sit amet',
				'dependency_ordinal' => 1,
				'dependency_pool_id' => 1,
				'dependency_id' => 1,
				'team_id' => TEAM_ID_RED,
			],
			[
				'pool_id' => POOL_ID_MONDAY_SEEDED,
				'alias' => 'A1',
				'dependency_type' => 'seed',
				'dependency_ordinal' => null,
				'dependency_pool_id' => 2,
				'dependency_id' => 1,
				'team_id' => TEAM_ID_RED,
			],
			[
				'pool_id' => POOL_ID_MONDAY_SEEDED,
				'alias' => 'A2',
				'dependency_type' => 'seed',
				'dependency_ordinal' => null,
				'dependency_pool_id' => 2,
				'dependency_id' => 0,
				'team_id' => TEAM_ID_BLUE,
			],
			[
				'pool_id' => POOL_ID_MONDAY_SEEDED,
				'alias' => 'A3',
				'dependency_type' => 'seed',
				'dependency_ordinal' => 50,
				'dependency_pool_id' => null,
				'dependency_id' => 1,
				'team_id' => TEAM_ID_YELLOW,
			],
			[
				'pool_id' => POOL_ID_MONDAY_SEEDED,
				'alias' => 'A4',
				'dependency_type' => 'seed',
				'dependency_ordinal' => null,
				'dependency_pool_id' => null,
				'dependency_id' => 1,
				'team_id' => TEAM_ID_GREEN,
			]
		];

		if (!defined('POOL_TEAM_ID_MONDAY_SEEDED_1')) {
			$i = 0;
			define('POOL_TEAM_ID_MONDAY_SEEDED_TODO', ++$i);
			define('POOL_TEAM_ID_MONDAY_SEEDED_1', ++$i);
			define('POOL_TEAM_ID_MONDAY_SEEDED_2', ++$i);
			define('POOL_TEAM_ID_MONDAY_SEEDED_3', ++$i);
			define('POOL_TEAM_ID_MONDAY_SEEDED_4', ++$i);
		}

		parent::init();
	}

}
