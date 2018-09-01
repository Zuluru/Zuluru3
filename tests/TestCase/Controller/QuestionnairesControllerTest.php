<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;

/**
 * App\Controller\QuestionnairesController Test Case
 */
class QuestionnairesControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
			'app.questions',
				'app.answers',
			'app.questionnaires',
				'app.questionnaires_questions',
			'app.events',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		// Admins are allowed to get the index
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/questionnaires/edit\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
		$this->assertResponseRegExp('#/questionnaires/delete\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
		$this->assertResponseRegExp('#/questionnaires/deactivate\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
		$this->assertResponseRegExp('#/questionnaires/edit\?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB . '#ms');
		$this->assertResponseRegExp('#/questionnaires/delete\?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB . '#ms');
		$this->assertResponseRegExp('#/questionnaires/deactivate\?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB . '#ms');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		// Managers are allowed to get the index, but don't see questionnaires in other affiliates
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/questionnaires/edit\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
		$this->assertResponseRegExp('#/questionnaires/delete\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
		$this->assertResponseRegExp('#/questionnaires/deactivate\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
		$this->assertResponseNotRegExp('#/questionnaires/edit\?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB . '#ms');
		$this->assertResponseNotRegExp('#/questionnaires/delete\?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB . '#ms');
		$this->assertResponseNotRegExp('#/questionnaires/deactivate\?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB . '#ms');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		// Others are not allowed to get the index
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'index'], PERSON_ID_COORDINATOR);
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
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivated'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/questionnaires/edit\?questionnaire=' . QUESTIONNAIRE_ID_OLD . '#ms');
		$this->assertResponseRegExp('#/questionnaires/delete\?questionnaire=' . QUESTIONNAIRE_ID_OLD . '#ms');
		$this->assertResponseRegExp('#/questionnaires/activate\?questionnaire=' . QUESTIONNAIRE_ID_OLD . '#ms');
	}

	/**
	 * Test deactivated method as a manager
	 *
	 * @return void
	 */
	public function testDeactivatedAsManager() {
		// Managers are allowed to get the deactivated list
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivated'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/questionnaires/edit\?questionnaire=' . QUESTIONNAIRE_ID_OLD . '#ms');
		$this->assertResponseRegExp('#/questionnaires/delete\?questionnaire=' . QUESTIONNAIRE_ID_OLD . '#ms');
		$this->assertResponseRegExp('#/questionnaires/activate\?questionnaire=' . QUESTIONNAIRE_ID_OLD . '#ms');
	}

	/**
	 * Test deactivated method as a coordinator
	 *
	 * @return void
	 */
	public function testDeactivatedAsCoordinator() {
		// Others are not allowed to get the deactivated list
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'deactivated'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to view questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/questionnaires/edit\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
		$this->assertResponseRegExp('#/questionnaires/delete\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');

		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/questionnaires/edit\?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB . '#ms');
		$this->assertResponseRegExp('#/questionnaires/delete\?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/questionnaires/edit\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
		$this->assertResponseRegExp('#/questionnaires/delete\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Others are not allowed to view questionnaires
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to add questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<option value="1" selected="selected">Club</option>#ms');
		$this->assertResponseRegExp('#<option value="2">Sub</option>#ms');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#<input type="hidden" name="affiliate_id" value="1"/>#ms');
		$this->assertResponseNotRegExp('#<option value="2">Sub</option>#ms');
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Others are not allowed to add questionnaires
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'add'], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to edit questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Others are not allowed to edit questionnaires
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_COORDINATOR);
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
		// Admins are allowed to activate questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/questionnaires\\\\/deactivate\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager() {
		// Managers are allowed to activate questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/questionnaires\\\\/deactivate\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_MANAGER, 'getajax');
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
		// Others are not allowed to activate questionnaires
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM], PERSON_ID_PLAYER, 'getajax');
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
		// Admins are allowed to deactivate questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/questionnaires\\\\/activate\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');
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
		// Managers are allowed to deactivate questionnaires
		$this->assertAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/questionnaires\\\\/activate\?questionnaire=' . QUESTIONNAIRE_ID_TEAM . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_MANAGER, 'getajax');
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
		// Others are not allowed to deactivate questionnaires
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM], PERSON_ID_PLAYER, 'getajax');
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

		// Admins are allowed to delete questionnaires
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_NEW],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Questionnaires', 'action' => 'index'],
			'The questionnaire has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Questionnaires', 'action' => 'index'],
			'#The following records reference this questionnaire, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete questionnaires in their affiliate
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_NEW],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Questionnaires', 'action' => 'index'],
			'The questionnaire has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_TEAM_SUB],
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
	 * Test add_question method as an admin
	 *
	 * @return void
	 */
	public function testAddQuestionAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_question method as a manager
	 *
	 * @return void
	 */
	public function testAddQuestionAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_question method as a coordinator
	 *
	 * @return void
	 */
	public function testAddQuestionAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_question method as a captain
	 *
	 * @return void
	 */
	public function testAddQuestionAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_question method as a player
	 *
	 * @return void
	 */
	public function testAddQuestionAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_question method as someone else
	 *
	 * @return void
	 */
	public function testAddQuestionAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_question method without being logged in
	 *
	 * @return void
	 */
	public function testAddQuestionAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_question method as an admin
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_question method as a manager
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_question method as a coordinator
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_question method as a captain
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_question method as a player
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_question method as someone else
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_question method without being logged in
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsAnonymous() {
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
