<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FranchisesFixture
 *
 */
class FranchisesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'franchises'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Team Red',
				'website' => '',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Also Team Red',
				'website' => '',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Big Blue',
				'website' => '',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Maples',
				'website' => '',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Lions',
				'website' => '',
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
		];

		if (!defined('FRANCHISE_ID_RED')) {
			$i = 0;
			define('FRANCHISE_ID_RED', ++$i);
			define('FRANCHISE_ID_RED2', ++$i);
			define('FRANCHISE_ID_BLUE', ++$i);
			define('FRANCHISE_ID_MAPLES', ++$i);
			define('FRANCHISE_ID_LIONS', ++$i);
		}

		parent::init();
	}

}
