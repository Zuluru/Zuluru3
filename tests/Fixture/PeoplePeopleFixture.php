<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * PeoplePeopleFixture
 *
 */
class PeoplePeopleFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $table = 'people_people';

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'people_people'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'person_id' => PERSON_ID_CAPTAIN,
				'relative_id' => PERSON_ID_CAPTAIN2,
				'approved' => true,
				'created' => FrozenDate::now(),
			],
			[
				'person_id' => PERSON_ID_CAPTAIN2,
				'relative_id' => PERSON_ID_CAPTAIN,
				'approved' => false,
				'created' => FrozenDate::now(),
			],
			[
				'person_id' => PERSON_ID_PLAYER,
				'relative_id' => PERSON_ID_CHILD,
				'approved' => true,
				'created' => FrozenDate::now(),
			],
		];

		parent::init();
	}

}
