<?php
namespace App\Test\Fixture_deprecated;

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
	public function init(): void {
		if (!defined('UPLOAD_ID_CAPTAIN_PHOTO')) {
			$i = 0;
			define('UPLOAD_ID_CAPTAIN_PHOTO', ++$i);
			define('UPLOAD_ID_PLAYER_PHOTO', ++$i);
			define('UPLOAD_ID_CHILD_WAIVER', ++$i);
			define('UPLOAD_ID_DOG_WAIVER', ++$i);
		}

		$this->records = [
			[
				'person_id' => PERSON_ID_CAPTAIN,
				'type_id' => null,
				'valid_from' => null,
				'valid_until' => null,
				'filename' => PERSON_ID_CAPTAIN . '.png',
				'approved' => true,
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'person_id' => PERSON_ID_PLAYER,
				'type_id' => null,
				'valid_from' => null,
				'valid_until' => null,
				'filename' => PERSON_ID_PLAYER . '.png',
				'approved' => false,
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'person_id' => PERSON_ID_CHILD,
				'type_id' => UPLOAD_TYPE_ID_JUNIOR_WAIVER,
				'valid_from' => new FrozenDate('January 1'),
				'valid_until' => new FrozenDate('December 31'),
				'filename' => PERSON_ID_CHILD . '_' . UPLOAD_ID_CHILD_WAIVER . '.png',
				'approved' => true,
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'person_id' => PERSON_ID_CAPTAIN2,
				'type_id' => UPLOAD_TYPE_ID_DOG_WAIVER,
				'valid_from' => new FrozenDate('January 1'),
				'valid_until' => new FrozenDate('December 31'),
				'filename' => PERSON_ID_CAPTAIN2 . '_' . UPLOAD_ID_DOG_WAIVER . '.png',
				'approved' => false,
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
		];

		parent::init();
	}

}
