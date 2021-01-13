<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PoolsFixture
 *
 */
class PoolsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'pools'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'division_id' => DIVISION_ID_MONDAY_PLAYOFF,
				'stage' => 1,
				'name' => 'A',
				'type' => 'seeded'
			],
			[
				'division_id' => DIVISION_ID_MONDAY_PLAYOFF,
				'stage' => 2,
				'name' => 'B',
				'type' => 'crossover'
			],
		];

		if (!defined('POOL_ID_MONDAY_SEEDED')) {
			$i = 0;
			define('POOL_ID_MONDAY_SEEDED', ++$i);
			define('POOL_ID_MONDAY_CROSSOVER', ++$i);
		}

		parent::init();
	}

}
