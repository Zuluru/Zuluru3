<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UploadTypesFixture
 *
 */
class UploadTypesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'upload_types'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Junior waiver',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Dog waiver',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Unused waiver',
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Junior waiver',
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
		];

		if (!defined('UPLOAD_TYPE_ID_JUNIOR_WAIVER')) {
			$i = 0;
			define('UPLOAD_TYPE_ID_JUNIOR_WAIVER', ++$i);
			define('UPLOAD_TYPE_ID_DOG_WAIVER', ++$i);
			define('UPLOAD_TYPE_ID_UNUSED_WAIVER', ++$i);
			define('UPLOAD_TYPE_ID_JUNIOR_WAIVER_SUB', ++$i);
		}

		parent::init();
	}

}
