<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * HolidaysFixture
 *
 */
class HolidaysFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'holidays'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'date' => new FrozenDate('December 25'),
				'name' => 'Christmas',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'date' => new FrozenDate('December 26'),
				'name' => 'Boxing Day',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'date' => new FrozenDate('January 1'),
				'name' => 'New Year\'s',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'date' => new FrozenDate('December 25'),
				'name' => 'Christmas',
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
		];

		if (!defined('HOLIDAY_ID_CHRISTMAS')) {
			$i = 0;
			define('HOLIDAY_ID_CHRISTMAS', ++$i);
			define('HOLIDAY_ID_BOXING_DAY', ++$i);
			define('HOLIDAY_ID_NEW_YEAR', ++$i);
			define('HOLIDAY_ID_CHRISTMAS_SUB', ++$i);
		}

		parent::init();
	}

}
