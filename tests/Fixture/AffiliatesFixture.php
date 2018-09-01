<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AffiliatesFixture
 *
 */
class AffiliatesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'affiliates'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Club',
				'active' => 1,
			],
			[
				'name' => 'Sub',
				'active' => 1,
			],
			[
				'name' => 'Empty',
				'active' => 1,
			],
		];

		if (!defined('AFFILIATE_ID_CLUB')) {
			$i = 0;
			define('AFFILIATE_ID_CLUB', ++$i);
			define('AFFILIATE_ID_SUB', ++$i);
			define('AFFILIATE_ID_EMPTY', ++$i);
		}

		parent::init();
	}

}
