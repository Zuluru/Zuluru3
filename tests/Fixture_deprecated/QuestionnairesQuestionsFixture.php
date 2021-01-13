<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QuestionnairesQuestionsFixture
 *
 */
class QuestionnairesQuestionsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'questionnaires_questions'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'questionnaire_id' => QUESTIONNAIRE_ID_TEAM,
				'question_id' => QUESTION_ID_TEAM_RETURNING,
				'sort' => 1,
				'required' => true,
			],
		];

		parent::init();
	}

}
