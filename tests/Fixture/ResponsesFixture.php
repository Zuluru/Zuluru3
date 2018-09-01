<?php
namespace App\Test\Fixture;

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
				'registration_id' => 1,
				'question_id' => 1,
				'answer_id' => 1,
				'answer_text' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.'
			],
		];

		parent::init();
	}

}
