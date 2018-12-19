<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\AnswersController Test Case
 */
class AnswersControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.divisions_people',
			'app.questions',
				'app.answers',
			'app.settings',
	];

	/**
	 * Test activate method as an admin
	 *
	 * @return void
	 */
	public function testActivateAsAdmin() {
		// Admins are allowed to activate answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/answers\\/deactivate?answer=' . ANSWER_ID_TEAM_NIGHT_MONDAY);
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager() {
		// Managers are allowed to activate answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_TUESDAY],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/answers\\/deactivate?answer=' . ANSWER_ID_TEAM_NIGHT_TUESDAY);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test activate method as others
	 *
	 * @return void
	 */
	public function testActivateAsOthers() {
		// Others are not allowed to activate answers
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY]);
	}

	/**
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin() {
		// Admins are allowed to deactivate answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_ADMIN);
		$this->assertResponseContains('/answers\\/activate?answer=' . ANSWER_ID_TEAM_NIGHT_MONDAY);
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager() {
		// Managers are allowed to deactivate answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_TUESDAY],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/answers\\/activate?answer=' . ANSWER_ID_TEAM_NIGHT_TUESDAY);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test deactivate method as others
	 *
	 * @return void
	 */
	public function testDeactivateAsOthers() {
		// Others are not allowed to deactivate answers
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY]);
	}

}
