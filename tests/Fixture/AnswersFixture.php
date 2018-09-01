<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AnswersFixture
 *
 */
class AnswersFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'answers'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'question_id' => QUESTION_ID_TEAM_NIGHT,
				'answer' => 'Monday',
				'active' => 1,
				'sort' => 1
			],
			[
				'question_id' => QUESTION_ID_TEAM_NIGHT,
				'answer' => 'Tuesday',
				'active' => 1,
				'sort' => 1
			],
			[
				'question_id' => QUESTION_ID_TEAM_NIGHT,
				'answer' => 'Wednesday',
				'active' => 1,
				'sort' => 1
			],
			[
				'question_id' => QUESTION_ID_TEAM_NIGHT,
				'answer' => 'Thursday',
				'active' => 1,
				'sort' => 1
			],
			[
				'question_id' => QUESTION_ID_TEAM_NIGHT_SUB,
				'answer' => 'Monday',
				'active' => 1,
				'sort' => 1
			],
			[
				'question_id' => QUESTION_ID_TEAM_NIGHT_SUB,
				'answer' => 'Tuesday',
				'active' => 1,
				'sort' => 1
			],
			[
				'question_id' => QUESTION_ID_TEAM_NIGHT_SUB,
				'answer' => 'Wednesday',
				'active' => 1,
				'sort' => 1
			],
			[
				'question_id' => QUESTION_ID_TEAM_NIGHT_SUB,
				'answer' => 'Thursday',
				'active' => 1,
				'sort' => 1
			],
		];

		if (!defined('ANSWER_ID_TEAM_NIGHT_MONDAY')) {
			$i = 0;
			define('ANSWER_ID_TEAM_NIGHT_MONDAY', ++$i);
			define('ANSWER_ID_TEAM_NIGHT_TUESDAY', ++$i);
			define('ANSWER_ID_TEAM_NIGHT_WEDNESDAY', ++$i);
			define('ANSWER_ID_TEAM_NIGHT_THURSDAY', ++$i);
			define('ANSWER_ID_TEAM_NIGHT_MONDAY_SUB', ++$i);
			define('ANSWER_ID_TEAM_NIGHT_TUESDAY_SUB', ++$i);
			define('ANSWER_ID_TEAM_NIGHT_WEDNESDAY_SUB', ++$i);
			define('ANSWER_ID_TEAM_NIGHT_THURSDAY_SUB', ++$i);
		}

		parent::init();
	}

}
