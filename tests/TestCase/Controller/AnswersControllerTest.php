<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;

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
		$this->assertAccessOk(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/answers\\\\/deactivate\?answer=' . ANSWER_ID_TEAM_NIGHT_MONDAY . '#ms');
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager() {
		// Managers are allowed to activate answers
		$this->assertAccessOk(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_TUESDAY], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/answers\\\\/deactivate\?answer=' . ANSWER_ID_TEAM_NIGHT_TUESDAY . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY_SUB], PERSON_ID_MANAGER, 'getajax');
	}

	/**
	 * Test activate method as a coordinator
	 *
	 * @return void
	 */
	public function testActivateAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as a captain
	 *
	 * @return void
	 */
	public function testActivateAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as a player
	 *
	 * @return void
	 */
	public function testActivateAsPlayer() {
		// Others are not allowed to activate answers
		$this->assertAccessRedirect(['controller' => 'Answers', 'action' => 'activate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY], PERSON_ID_PLAYER, 'getajax');
	}

	/**
	 * Test activate method as someone else
	 *
	 * @return void
	 */
	public function testActivateAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method without being logged in
	 *
	 * @return void
	 */
	public function testActivateAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin() {
		// Admins are allowed to deactivate answers
		$this->assertAccessOk(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/answers\\\\/activate\?answer=' . ANSWER_ID_TEAM_NIGHT_MONDAY . '#ms');
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager() {
		// Managers are allowed to deactivate answers
		$this->assertAccessOk(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_TUESDAY], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/answers\\\\/activate\?answer=' . ANSWER_ID_TEAM_NIGHT_TUESDAY . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY_SUB], PERSON_ID_MANAGER, 'getajax');
	}

	/**
	 * Test deactivate method as a coordinator
	 *
	 * @return void
	 */
	public function testDeactivateAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a captain
	 *
	 * @return void
	 */
	public function testDeactivateAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method as a player
	 *
	 * @return void
	 */
	public function testDeactivateAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => ANSWER_ID_TEAM_NIGHT_MONDAY], PERSON_ID_PLAYER, 'getajax');
	}

	/**
	 * Test deactivate method as someone else
	 *
	 * @return void
	 */
	public function testDeactivateAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivate method without being logged in
	 *
	 * @return void
	 */
	public function testDeactivateAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
