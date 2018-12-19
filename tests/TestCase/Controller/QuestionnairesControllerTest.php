<?php
namespace App\Test\TestCase\Controller;

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
					'app.teams',
					'app.divisions_people',
			'app.questions',
				'app.answers',
			'app.questionnaires',
				'app.questionnaires_questions',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.responses',
			'app.settings',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
		$this->assertResponseContains('/questionnaires/deactivate?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB);
		$this->assertResponseContains('/questionnaires/deactivate?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB);

		// Managers are allowed to see the index, but don't see questionnaires in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
		$this->assertResponseContains('/questionnaires/deactivate?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
		$this->assertResponseNotContains('/questionnaires/edit?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB);
		$this->assertResponseNotContains('/questionnaires/delete?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB);
		$this->assertResponseNotContains('/questionnaires/deactivate?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'index']);
	}

	/**
	 * Test deactivated method
	 *
	 * @return void
	 */
	public function testDeactivated() {
		// Admins are allowed to see the deactivated list
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivated'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . QUESTIONNAIRE_ID_OLD);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . QUESTIONNAIRE_ID_OLD);
		$this->assertResponseContains('/questionnaires/activate?questionnaire=' . QUESTIONNAIRE_ID_OLD);

		// Managers are allowed to see the deactivated list
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivated'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . QUESTIONNAIRE_ID_OLD);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . QUESTIONNAIRE_ID_OLD);
		$this->assertResponseContains('/questionnaires/activate?questionnaire=' . QUESTIONNAIRE_ID_OLD);

		// Others are not allowed to see the deactivated list
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivated'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivated'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivated'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivated'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivated']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . QUESTIONNAIRE_ID_TEAM);

		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . QUESTIONNAIRE_ID_TEAM_SUB);

		// Managers are allowed to view questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_MANAGER);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . QUESTIONNAIRE_ID_TEAM);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_MANAGER);

		// Others are not allowed to view questionnaires
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseContains('<option value="1" selected="selected">Club</option>');
		$this->assertResponseContains('<option value="2">Sub</option>');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseContains('<input type="hidden" name="affiliate_id" value="1"/>');
		$this->assertResponseNotContains('<option value="2">Sub</option>');
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add questionnaires
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit questionnaires
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM]);
	}

	/**
	 * Test activate method as an admin
	 *
	 * @return void
	 */
	public function testActivateAsAdmin() {
		// Admins are allowed to activate questionnaires
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questionnaires\\/deactivate?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager() {
		// Managers are allowed to activate questionnaires
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/questionnaires\\/deactivate?questionnaire=' . QUESTIONNAIRE_ID_TEAM);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test activate method as others
	 *
	 * @return void
	 */
	public function testActivateAsOthers() {
		// Others are not allowed to activate questionnaires
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM]);
	}

	/**
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin() {
		// Admins are allowed to deactivate questionnaires
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questionnaires\\/activate?questionnaire=' . QUESTIONNAIRE_ID_TEAM);
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager() {
		// Managers are allowed to deactivate questionnaires
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM], PERSON_ID_MANAGER);
		$this->assertResponseContains('/questionnaires\\/activate?questionnaire=' . QUESTIONNAIRE_ID_TEAM);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test deactivate method as others
	 *
	 * @return void
	 */
	public function testDeactivateAsOthers() {
		// Others are not allowed to deactivate questionnaires
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', 'questionnaire' => QUESTIONNAIRE_ID_TEAM]);
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
		$this->assertPostAsAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_NEW],
			PERSON_ID_ADMIN, [], ['controller' => 'Questionnaires', 'action' => 'index'],
			'The questionnaire has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_TEAM],
			PERSON_ID_ADMIN, [], ['controller' => 'Questionnaires', 'action' => 'index'],
			'#The following records reference this questionnaire, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete questionnaires in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_NEW],
			PERSON_ID_MANAGER, [], ['controller' => 'Questionnaires', 'action' => 'index'],
			'The questionnaire has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_TEAM_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete questionnaires
		$this->assertPostAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_NEW],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_NEW],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_NEW],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_NEW],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', 'questionnaire' => QUESTIONNAIRE_ID_NEW]);
	}

	/**
	 * Test add_question method as an admin
	 *
	 * @return void
	 */
	public function testAddQuestionAsAdmin() {
		// Admins are allowed to add question
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'add_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_question method as a manager
	 *
	 * @return void
	 */
	public function testAddQuestionAsManager() {
		// Managers are allowed to add question
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'add_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_question method as others
	 *
	 * @return void
	 */
	public function testAddQuestionAsOthers() {
		// Others are not allowed to add questions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'add_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_NIGHT]);
	}

	/**
	 * Test remove_question method as an admin
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsAdmin() {
		// Admins are allowed to remove question
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'remove_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_RETURNING],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_question method as a manager
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsManager() {
		// Managers are allowed to remove question
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'remove_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_RETURNING],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_question method as others
	 *
	 * @return void
	 */
	public function testRemoveQuestionAsOthers() {
		// Others are not allowed to remove questions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'remove_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_RETURNING],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'remove_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_RETURNING],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'remove_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_RETURNING],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'remove_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_RETURNING],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'remove_question', 'questionnaire' =>  QUESTIONNAIRE_ID_TEAM, 'question' => QUESTION_ID_TEAM_RETURNING]);
	}

	/**
	 * Test consolidate method as an admin
	 *
	 * @return void
	 */
	public function testConsolidateAsAdmin() {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Admins are allowed to consolidate
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'consolidate'], PERSON_ID_ADMIN);
	}

	/**
	 * Test consolidate method as a manager
	 *
	 * @return void
	 */
	public function testConsolidateAsManager() {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Managers are allowed to consolidate
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'consolidate'], PERSON_ID_MANAGER);
	}

	/**
	 * Test consolidate method as others
	 *
	 * @return void
	 */
	public function testConsolidateAsOthers() {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Others are not allowed to consolidate
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'consolidate'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'consolidate'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'consolidate'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'consolidate'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'consolidate']);
	}

}
