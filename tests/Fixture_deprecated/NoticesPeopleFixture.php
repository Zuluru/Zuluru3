<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * NoticesPeopleFixture
 *
 */
class NoticesPeopleFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $table = 'notices_people';

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'notices_people'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'notice_id' => 1,
				'person_id' => PERSON_ID_ADMIN,
				'remind' => true,
				'created' => FrozenDate::now(),
			],
		];

		parent::init();
	}

}
