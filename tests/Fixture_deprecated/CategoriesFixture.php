<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CategoriesFixture
 *
 */
class CategoriesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'categories'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init(): void {
		$this->records = [
			[
				'name' => 'Events',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Clinics',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Events',
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
			[
				'name' => 'Marketing',
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
		];

		if (!defined('CATEGORY_ID_EVENTS')) {
			$i = 0;
			define('CATEGORY_ID_EVENTS', ++$i);
			define('CATEGORY_ID_CLINICS', ++$i);
			define('CATEGORY_ID_EVENTS_SUB', ++$i);
			define('CATEGORY_ID_MARKETING_SUB', ++$i);
		}

		parent::init();
	}

}
