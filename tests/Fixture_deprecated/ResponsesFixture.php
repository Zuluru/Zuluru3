<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ResponsesFixture
 *
 */
class ResponsesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'responses'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'event_id' => EVENT_ID_MEMBERSHIP,
				'registration_id' => REGISTRATION_ID_CAPTAIN2_TEAM,
				'question_id' => TEAM_NAME,
				'answer_id' => null,
				'answer_text' => 'Blue',
			],
			[
				'event_id' => EVENT_ID_MEMBERSHIP,
				'registration_id' => REGISTRATION_ID_CAPTAIN2_TEAM,
				'question_id' => SHIRT_COLOUR,
				'answer_id' => null,
				'answer_text' => 'Blue',
			],
			[
				'event_id' => EVENT_ID_MEMBERSHIP,
				'registration_id' => REGISTRATION_ID_CAPTAIN2_TEAM,
				'question_id' => TEAM_ID_CREATED,
				'answer_id' => null,
				'answer_text' => TEAM_ID_BLUE,
			],
			[
				'event_id' => EVENT_ID_MEMBERSHIP,
				'registration_id' => REGISTRATION_ID_CAPTAIN2_TEAM,
				'question_id' => TRACK_ATTENDANCE,
				'answer_id' => 1,
				'answer_text' => null,
			],
		];

		parent::init();
	}

}
