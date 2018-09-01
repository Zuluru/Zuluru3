<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * UploadsFixture
 *
 */
class UploadsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'uploads'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		if (!defined('UPLOAD_ID_CAPTAIN_PHOTO')) {
			$i = 0;
			define('UPLOAD_ID_CAPTAIN_PHOTO', ++$i);
			define('UPLOAD_ID_CHILD_WAIVER', ++$i);
		}

		$this->records = [
			[
				'person_id' => PERSON_ID_CAPTAIN,
				'type_id' => null,
				'valid_from' => null,
				'valid_until' => null,
				'filename' => PERSON_ID_CAPTAIN . '.jpg',
				'approved' => true,
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'person_id' => PERSON_ID_CHILD,
				'type_id' => UPLOAD_TYPE_ID_JUNIOR_WAIVER,
				'valid_from' => new FrozenDate('January 1'),
				'valid_until' => new FrozenDate('December 31'),
				'filename' => PERSON_ID_CHILD . '_' . UPLOAD_ID_CHILD_WAIVER . '.pdf',
				'approved' => true,
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
		];

		parent::init();
	}

}
