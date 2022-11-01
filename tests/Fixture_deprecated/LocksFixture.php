<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * LocksFixture
 *
 */
class LocksFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'locks'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Lorem ipsum dolor sit amet',
				'user_id' => USER_ID_ADMIN,
				'created' => FrozenDate::now(),
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
		];

		parent::init();
	}

}
