<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;

/**
 * App\Controller\QuestionsController Test Case
 */
class QuestionsControllerTest extends ControllerTestCase {

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex(): void {
		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questions/edit?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseContains('/questions/delete?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseContains('/questions/deactivate?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseNotContains('/questions/edit?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseNotContains('/questions/delete?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseNotContains('/questions/activate?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseContains('/questions/edit?question=' . QUESTION_ID_TEAM_RETURNING_SUB);
		$this->assertResponseContains('/questions/delete?question=' . QUESTION_ID_TEAM_RETURNING_SUB);
		$this->assertResponseContains('/questions/deactivate?question=' . QUESTION_ID_TEAM_RETURNING_SUB);

		// Managers are allowed to see the index, but don't see questions in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/questions/edit?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseContains('/questions/delete?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseContains('/questions/deactivate?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseNotContains('/questions/edit?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseNotContains('/questions/delete?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseNotContains('/questions/activate?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseNotContains('/questions/edit?question=' . QUESTION_ID_TEAM_RETURNING_SUB);
		$this->assertResponseNotContains('/questions/delete?question=' . QUESTION_ID_TEAM_RETURNING_SUB);
		$this->assertResponseNotContains('/questions/deactivate?question=' . QUESTION_ID_TEAM_RETURNING_SUB);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'index']);
	}

	/**
	 * Test deactivated method
	 *
	 * @return void
	 */
	public function testDeactivated(): void {
		// Admins are allowed to see the deactivated list
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'deactivated'], PERSON_ID_ADMIN);
		$this->assertResponseNotContains('/questions/edit?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseNotContains('/questions/delete?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseNotContains('/questions/deactivate?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseContains('/questions/edit?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseContains('/questions/delete?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseContains('/questions/activate?question=' . QUESTION_ID_TEAM_OBSOLETE);

		// Managers are allowed to see the deactivated list
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'deactivated'], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('/questions/edit?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseNotContains('/questions/delete?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseNotContains('/questions/deactivate?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseContains('/questions/edit?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseContains('/questions/delete?question=' . QUESTION_ID_TEAM_OBSOLETE);
		$this->assertResponseContains('/questions/activate?question=' . QUESTION_ID_TEAM_OBSOLETE);

		// Others are not allowed to see the deactivated list
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivated'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivated'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivated'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivated'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'deactivated']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView(): void {
		// Admins are allowed to view questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questions/edit?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseContains('/questions/delete?question=' . QUESTION_ID_TEAM_RETURNING);

		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questions/edit?question=' . QUESTION_ID_TEAM_RETURNING_SUB);
		$this->assertResponseContains('/questions/delete?question=' . QUESTION_ID_TEAM_RETURNING_SUB);

		// Managers are allowed to view questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_MANAGER);
		$this->assertResponseContains('/questions/edit?question=' . QUESTION_ID_TEAM_RETURNING);
		$this->assertResponseContains('/questions/delete?question=' . QUESTION_ID_TEAM_RETURNING);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING_SUB], PERSON_ID_MANAGER);

		// Others are not allowed to view questions
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'view', 'question' => QUESTION_ID_TEAM_RETURNING]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin(): void {
		// Admins are allowed to add questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'add'], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager(): void {
		// Managers are allowed to add questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'add'], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers(): void {
		// Others are not allowed to add questions
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin(): void {
		// Admins are allowed to edit questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager(): void {
		// Managers are allowed to edit questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers(): void {
		// Others are not allowed to edit questions
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'edit', 'question' => QUESTION_ID_TEAM_RETURNING]);
	}

	/**
	 * Test activate method as an admin
	 *
	 * @return void
	 */
	public function testActivateAsAdmin(): void {
		// Admins are allowed to activate questions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_OBSOLETE], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questions\\/deactivate?question=' . QUESTION_ID_TEAM_OBSOLETE);
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager(): void {
		// Managers are allowed to activate questions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_OBSOLETE], PERSON_ID_MANAGER);
		$this->assertResponseContains('/questions\\/deactivate?question=' . QUESTION_ID_TEAM_OBSOLETE);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_NIGHT_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test activate method as others
	 *
	 * @return void
	 */
	public function testActivateAsOthers(): void {
		// Others are not allowed to activate questions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_OBSOLETE],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_OBSOLETE],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_OBSOLETE],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_OBSOLETE],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'activate', 'question' => QUESTION_ID_TEAM_OBSOLETE]);
	}

	/**
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin(): void {
		// Admins are allowed to deactivate questions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT], PERSON_ID_ADMIN);
		$this->assertResponseContains('/questions\\/activate?question=' . QUESTION_ID_TEAM_NIGHT);
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager(): void {
		// Managers are allowed to deactivate questions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT], PERSON_ID_MANAGER);
		$this->assertResponseContains('/questions\\/activate?question=' . QUESTION_ID_TEAM_NIGHT);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test deactivate method as others
	 *
	 * @return void
	 */
	public function testDeactivateAsOthers(): void {
		// Others are not allowed to deactivate questions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', 'question' => QUESTION_ID_TEAM_NIGHT]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete questions
		$this->assertPostAsAccessRedirect(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_ADMIN, [], ['controller' => 'Questions', 'action' => 'index'],
			'The question has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_RETURNING],
			PERSON_ID_ADMIN, [], ['controller' => 'Questions', 'action' => 'index'],
			'#The following records reference this question, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete questions in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_MANAGER, [], ['controller' => 'Questions', 'action' => 'index'],
			'The question has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_RETURNING_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete questions
		$this->assertPostAsAccessDenied(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'delete', 'question' => QUESTION_ID_TEAM_PREVIOUS]);
	}

	/**
	 * Test add_answer method as an admin
	 *
	 * @return void
	 */
	public function testAddAnswerAsAdmin(): void {
		// Admins are allowed to add answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'add_answer', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_answer method as a manager
	 *
	 * @return void
	 */
	public function testAddAnswerAsManager(): void {
		// Managers are allowed to add answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'add_answer', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_answer method as others
	 *
	 * @return void
	 */
	public function testAddAnswerAsOthers(): void {
		// Others are not allowed to add answers
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'add_answer', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'add_answer', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'add_answer', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'add_answer', 'question' => QUESTION_ID_TEAM_PREVIOUS],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'add_answer', 'question' => QUESTION_ID_TEAM_PREVIOUS]);
	}

	/**
	 * Test delete_answer method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'delete_answer', 'answer' => ANSWER_ID_TEAM_NIGHT_THURSDAY],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_answer method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'delete_answer', 'answer' => ANSWER_ID_TEAM_NIGHT_THURSDAY],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_answer method as others
	 *
	 * @return void
	 */
	public function testDeleteAnswerAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete answers
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'delete_answer', 'answer' => ANSWER_ID_TEAM_NIGHT_THURSDAY],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'delete_answer', 'answer' => ANSWER_ID_TEAM_NIGHT_THURSDAY],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'delete_answer', 'answer' => ANSWER_ID_TEAM_NIGHT_THURSDAY],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'delete_answer', 'answer' => ANSWER_ID_TEAM_NIGHT_THURSDAY],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'delete_answer', 'answer' => ANSWER_ID_TEAM_NIGHT_THURSDAY]);
	}

	/**
	 * Test autocomplete method
	 *
	 * @return void
	 */
	public function testAutocomplete(): void {
		// Admins are allowed to autocomplete
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'autocomplete', 'affiliate' => AFFILIATE_ID_CLUB, 'term' => 'night'],
			PERSON_ID_ADMIN);

		// Managers are allowed to autocomplete
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'autocomplete', 'affiliate' => AFFILIATE_ID_CLUB, 'term' => 'night'],
			PERSON_ID_MANAGER);

		// Others are not allowed to autocomplete
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'autocomplete', 'affiliate' => AFFILIATE_ID_CLUB, 'term' => 'night'],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'autocomplete', 'affiliate' => AFFILIATE_ID_CLUB, 'term' => 'night'],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'autocomplete', 'affiliate' => AFFILIATE_ID_CLUB, 'term' => 'night'],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'autocomplete', 'affiliate' => AFFILIATE_ID_CLUB, 'term' => 'night'],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'autocomplete', 'affiliate' => AFFILIATE_ID_CLUB, 'term' => 'night']);
	}

	/**
	 * Test consolidate method as an admin
	 *
	 * @return void
	 */
	public function testConsolidateAsAdmin(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Admins are allowed to consolidate
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'consolidate'], PERSON_ID_ADMIN);
	}

	/**
	 * Test consolidate method as a manager
	 *
	 * @return void
	 */
	public function testConsolidateAsManager(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Managers are allowed to consolidate
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'consolidate'], PERSON_ID_MANAGER);
	}

	/**
	 * Test consolidate method as others
	 *
	 * @return void
	 */
	public function testConsolidateAsOthers(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Others are now allowed to consolidate
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'consolidate'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'consolidate'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'consolidate'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'consolidate'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'consolidate']);
	}

}
