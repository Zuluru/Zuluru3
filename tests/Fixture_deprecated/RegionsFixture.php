<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RegionsFixture
 *
 */
class RegionsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'regions'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init(): void {
		$this->records = [
			[
				'name' => 'East',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'West',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'North',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'South',
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
		];

		if (!defined('REGION_ID_EAST')) {
			$i = 0;
			define('REGION_ID_EAST', ++$i);
			define('REGION_ID_WEST', ++$i);
			define('REGION_ID_NORTH', ++$i);
			define('REGION_ID_SOUTH', ++$i);
		}

		parent::init();
	}

}
