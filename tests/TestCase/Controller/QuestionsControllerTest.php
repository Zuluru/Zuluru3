<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;

/**
 * App\Controller\QuestionsController Test Case
 */
class QuestionsControllerTest extends ControllerTestCase {

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
			'app.questionnaires',
				'app.questionnaires_questions',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseRegExp('#/questions/deactivate\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseNotRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseNotRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseNotRegExp('#/questions/activate\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_RETURNING_SUB . '#ms');
		$this->assertResponseRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_RETURNING_SUB . '#ms');
		$this->assertResponseRegExp('#/questions/deactivate\?question=' . QUESTION_ID_TEAM_RETURNING_SUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see questions in other affiliates
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseRegExp('#/questions/deactivate\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseNotRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseNotRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseNotRegExp('#/questions/activate\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseNotRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_RETURNING_SUB . '#ms');
		$this->assertResponseNotRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_RETURNING_SUB . '#ms');
		$this->assertResponseNotRegExp('#/questions/deactivate\?question=' . QUESTION_ID_TEAM_RETURNING_SUB . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'index'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test index method as a captain
	 *
	 * @return void
	 */
	public function testIndexAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a player
	 *
	 * @return void
	 */
	public function testIndexAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as someone else
	 *
	 * @return void
	 */
	public function testIndexAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivated method as an admin
	 *
	 * @return void
	 */
	public function testDeactivatedAsAdmin() {
		// Admins are allowed to get the deactivated list
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'deactivated'], PERSON_ID_ADMIN);
		$this->assertResponseNotRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseNotRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseNotRegExp('#/questions/deactivate\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseRegExp('#/questions/activate\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
	}

	/**
	 * Test deactivated method as a manager
	 *
	 * @return void
	 */
	public function testDeactivatedAsManager() {
		// Managers are allowed to get the deactivated list
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'deactivated'], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseNotRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseNotRegExp('#/questions/deactivate\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
		$this->assertResponseRegExp('#/questions/activate\?question=' . QUESTION_ID_TEAM_OBSOLETE . '#ms');
	}

	/**
	 * Test deactivated method as a coordinator
	 *
	 * @return void
	 */
	public function testDeactivatedAsCoordinator() {
		// Others are not allowed to get the deactivated list
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'deactivated'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test deactivated method as a captain
	 *
	 * @return void
	 */
	public function testDeactivatedAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivated method as a player
	 *
	 * @return void
	 */
	public function testDeactivatedAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivated method as someone else
	 *
	 * @return void
	 */
	public function testDeactivatedAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test deactivated method without being logged in
	 *
	 * @return void
	 */
	public function testDeactivatedAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');

		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_RETURNING_SUB . '#ms');
		$this->assertResponseRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_RETURNING_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/questions/edit\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');
		$this->assertResponseRegExp('#/questions/delete\?question=' . QUESTION_ID_TEAM_RETURNING . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Others are not allowed to view questions
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test view method as a captain
	 *
	 * @return void
	 */
	public function testViewAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as someone else
	 *
	 * @return void
	 */
	public function testViewAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method without being logged in
	 *
	 * @return void
	 */
	public function testViewAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add questions
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'add'], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test add method as a captain
	 *
	 * @return void
	 */
	public function testAddAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a player
	 *
	 * @return void
	 */
	public function testAddAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as someone else
	 *
	 * @return void
	 */
	public function testAddAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method without being logged in
	 *
	 * @return void
	 */
	public function testAddAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit questions
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test edit method as a captain
	 *
	 * @return void
	 */
	public function testEditAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a player
	 *
	 * @return void
	 */
	public function testEditAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as someone else
	 *
	 * @return void
	 */
	public function testEditAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method without being logged in
	 *
	 * @return void
	 */
	public function testEditAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activate method as an admin
	 *
	 * @return void
	 */
	public function testActivateAsAdmin() {
		// Admins are allowed to activate questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_NIGHT], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/questions\\\\/deactivate\?question=' . QUESTION_ID_TEAM_NIGHT . '#ms');
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager() {
		// Managers are allowed to activate questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_NIGHT], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/questions\\\\/deactivate\?question=' . QUESTION_ID_TEAM_NIGHT . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_NIGHT_SUB], PERSON_ID_MANAGER, 'getajax');
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
		// Others are not allowed to activate questions
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_NIGHT], PERSON_ID_PLAYER, 'getajax');
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
		// Admins are allowed to deactivate questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/questions\\\\/activate\?question=' . QUESTION_ID_TEAM_NIGHT . '#ms');
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
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager() {
		// Managers are allowed to deactivate questions
		$this->assertAccessOk(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/questions\\\\/activate\?question=' . QUESTION_ID_TEAM_NIGHT . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT_SUB], PERSON_ID_MANAGER, 'getajax');
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
		// Others are not allowed to deactivate questions
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT], PERSON_ID_PLAYER, 'getajax');
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

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete questions
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Questions', 'action' => 'index'],
			'The question has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_RETURNING],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Questions', 'action' => 'index'],
			'#The following records reference this question, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete questions in their affiliate
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Questions', 'action' => 'index'],
			'The question has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_RETURNING_SUB],
			PERSON_ID_MANAGER, 'post');
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a captain
	 *
	 * @return void
	 */
	public function testDeleteAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a player
	 *
	 * @return void
	 */
	public function testDeleteAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as someone else
	 *
	 * @return void
	 */
	public function testDeleteAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_answer method as an admin
	 *
	 * @return void
	 */
	public function testAddAnswerAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_answer method as a manager
	 *
	 * @return void
	 */
	public function testAddAnswerAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_answer method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAnswerAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_answer method as a captain
	 *
	 * @return void
	 */
	public function testAddAnswerAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_answer method as a player
	 *
	 * @return void
	 */
	public function testAddAnswerAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_answer method as someone else
	 *
	 * @return void
	 */
	public function testAddAnswerAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_answer method without being logged in
	 *
	 * @return void
	 */
	public function testAddAnswerAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_answer method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_answer method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_answer method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_answer method as a captain
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_answer method as a player
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_answer method as someone else
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_answer method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test autocomplete method as an admin
	 *
	 * @return void
	 */
	public function testAutocompleteAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test autocomplete method as a manager
	 *
	 * @return void
	 */
	public function testAutocompleteAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test autocomplete method as a coordinator
	 *
	 * @return void
	 */
	public function testAutocompleteAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test autocomplete method as a captain
	 *
	 * @return void
	 */
	public function testAutocompleteAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test autocomplete method as a player
	 *
	 * @return void
	 */
	public function testAutocompleteAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test autocomplete method as someone else
	 *
	 * @return void
	 */
	public function testAutocompleteAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test autocomplete method without being logged in
	 *
	 * @return void
	 */
	public function testAutocompleteAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidate method as an admin
	 *
	 * @return void
	 */
	public function testConsolidateAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidate method as a manager
	 *
	 * @return void
	 */
	public function testConsolidateAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidate method as a coordinator
	 *
	 * @return void
	 */
	public function testConsolidateAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidate method as a captain
	 *
	 * @return void
	 */
	public function testConsolidateAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidate method as a player
	 *
	 * @return void
	 */
	public function testConsolidateAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidate method as someone else
	 *
	 * @return void
	 */
	public function testConsolidateAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test consolidate method without being logged in
	 *
	 * @return void
	 */
	public function testConsolidateAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
