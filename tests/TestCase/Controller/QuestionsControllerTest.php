<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\QuestionFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\QuestionsController Test Case
 */
class QuestionsControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Groups',
		'app.Settings',
	];

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
			[
				'affiliate_id' => $affiliates[0]->id,
				'active' => false,
			],
		])->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[0]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[0]->id);
		$this->assertResponseContains('/questions/deactivate?question=' . $questions[0]->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[1]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[1]->id);
		$this->assertResponseContains('/questions/deactivate?question=' . $questions[1]->id);
		$this->assertResponseNotContains('/questions/edit?question=' . $questions[2]->id);
		$this->assertResponseNotContains('/questions/delete?question=' . $questions[2]->id);
		$this->assertResponseNotContains('/questions/activate?question=' . $questions[2]->id);

		// Managers are allowed to see the index, but don't see questions in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[0]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[0]->id);
		$this->assertResponseContains('/questions/deactivate?question=' . $questions[0]->id);
		$this->assertResponseNotContains('/questions/edit?question=' . $questions[1]->id);
		$this->assertResponseNotContains('/questions/delete?question=' . $questions[1]->id);
		$this->assertResponseNotContains('/questions/deactivate?question=' . $questions[1]->id);
		$this->assertResponseNotContains('/questions/edit?question=' . $questions[2]->id);
		$this->assertResponseNotContains('/questions/delete?question=' . $questions[2]->id);
		$this->assertResponseNotContains('/questions/activate?question=' . $questions[2]->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'index']);
	}

	/**
	 * Test deactivated method
	 */
	public function testDeactivated(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[0]->id,
				'active' => false,
			],
		])->persist();

		// Admins are allowed to see the deactivated list
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'deactivated'], $admin->id);
		$this->assertResponseNotContains('/questions/edit?question=' . $questions[0]->id);
		$this->assertResponseNotContains('/questions/delete?question=' . $questions[0]->id);
		$this->assertResponseNotContains('/questions/deactivate?question=' . $questions[0]->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[1]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[1]->id);
		$this->assertResponseContains('/questions/activate?question=' . $questions[1]->id);

		// Managers are allowed to see the deactivated list
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'deactivated'], $manager->id);
		$this->assertResponseNotContains('/questions/edit?question=' . $questions[0]->id);
		$this->assertResponseNotContains('/questions/delete?question=' . $questions[0]->id);
		$this->assertResponseNotContains('/questions/deactivate?question=' . $questions[0]->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[1]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[1]->id);
		$this->assertResponseContains('/questions/activate?question=' . $questions[1]->id);

		// Others are not allowed to see the deactivated list
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivated'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivated'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'deactivated']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
			[
				'affiliate_id' => $affiliates[0]->id,
				'active' => false,
			],
		])->persist();

		// Admins are allowed to view questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'view', '?' => ['question' => $questions[0]->id]], $admin->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[0]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[0]->id);

		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'view', '?' => ['question' => $questions[1]->id]], $admin->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[1]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[1]->id);

		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'view', '?' => ['question' => $questions[2]->id]], $admin->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[2]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[2]->id);

		// Managers are allowed to view questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'view', '?' => ['question' => $questions[0]->id]], $manager->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[0]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[0]->id);

		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'view', '?' => ['question' => $questions[2]->id]], $manager->id);
		$this->assertResponseContains('/questions/edit?question=' . $questions[2]->id);
		$this->assertResponseContains('/questions/delete?question=' . $questions[2]->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'view', '?' => ['question' => $questions[1]->id]], $manager->id);

		// Others are not allowed to view questions
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'view', '?' => ['question' => $questions[0]->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'view', '?' => ['question' => $questions[0]->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'view', '?' => ['question' => $questions[0]->id]]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to add questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'add'], $admin->id);
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to add questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'add'], $manager->id);
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer', 'player']);

		// Others are not allowed to add questions
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
			[
				'affiliate_id' => $affiliates[0]->id,
				'active' => false,
			],
		])->persist();

		// Admins are allowed to edit questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'edit', '?' => ['question' => $questions[0]->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'edit', '?' => ['question' => $questions[1]->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'edit', '?' => ['question' => $questions[2]->id]], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
			[
				'affiliate_id' => $affiliates[0]->id,
				'active' => false,
			],
		])->persist();

		// Managers are allowed to edit questions
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'edit', '?' => ['question' => $questions[0]->id]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'edit', '?' => ['question' => $questions[2]->id]], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'edit', '?' => ['question' => $questions[1]->id]], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Others are not allowed to edit questions
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'edit', '?' => ['question' => $question->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'edit', '?' => ['question' => $question->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'edit', '?' => ['question' => $question->id]]);
	}

	/**
	 * Test activate method as an admin
	 */
	public function testActivateAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'active' => false,
		])->persist();

		// Admins are allowed to activate questions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'activate', '?' => ['question' => $question->id]], $admin->id);
		$this->assertResponseContains('/questions\\/deactivate?question=' . $question->id);
	}

	/**
	 * Test activate method as a manager
	 */
	public function testActivateAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
				'active' => false,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
				'active' => false,
			],
		])->persist();

		// Managers are allowed to activate questions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'activate', '?' => ['question' => $questions[0]->id]], $manager->id);
		$this->assertResponseContains('/questions\\/deactivate?question=' . $questions[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'activate', '?' => ['question' => $questions[1]->id]], $manager->id);
	}

	/**
	 * Test activate method as others
	 */
	public function testActivateAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'active' => false,
		])->persist();

		// Others are not allowed to activate questions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'activate', '?' => ['question' => $question->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'activate', '?' => ['question' => $question->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'activate', '?' => ['question' => $question->id]]);
	}

	/**
	 * Test deactivate method as an admin
	 */
	public function testDeactivateAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Admins are allowed to deactivate questions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'deactivate', '?' => ['question' => $question->id]], $admin->id);
		$this->assertResponseContains('/questions\\/activate?question=' . $question->id);
	}

	/**
	 * Test deactivate method as a manager
	 */
	public function testDeactivateAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Managers are allowed to deactivate questions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'deactivate', '?' => ['question' => $questions[0]->id]], $manager->id);
		$this->assertResponseContains('/questions\\/activate?question=' . $questions[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', '?' => ['question' => $questions[1]->id]], $manager->id);
	}

	/**
	 * Test deactivate method as others
	 */
	public function testDeactivateAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Others are not allowed to deactivate questions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', '?' => ['question' => $question->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', '?' => ['question' => $question->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'deactivate', '?' => ['question' => $question->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$question = QuestionFactory::make([
			'affiliate_id' => $affiliates[0]->id,
		])->persist();

		// A question with a questionnaire that references it
		$dependent_question = QuestionFactory::make([
			'affiliate_id' => $affiliates[0]->id,
		])
			->with('Questionnaires', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		// Admins are allowed to delete questions
		$this->assertPostAsAccessRedirect(['controller' => 'Questions', 'action' => 'delete', '?' => ['question' => $question->id]],
			$admin->id, [], ['controller' => 'Questions', 'action' => 'index'],
			'The question has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Questions', 'action' => 'delete', '?' => ['question' => $dependent_question->id]],
			$admin->id, [], ['controller' => 'Questions', 'action' => 'index'],
			'#The following records reference this question, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Managers are allowed to delete questions in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Questions', 'action' => 'delete', '?' => ['question' => $questions[0]->id]],
			$manager->id, [], ['controller' => 'Questions', 'action' => 'index'],
			'The question has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Questions', 'action' => 'delete', '?' => ['question' => $questions[1]->id]],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Others are not allowed to delete questions
		$this->assertPostAsAccessDenied(['controller' => 'Questions', 'action' => 'delete', '?' => ['question' => $question->id]],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Questions', 'action' => 'delete', '?' => ['question' => $question->id]],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'delete', '?' => ['question' => $question->id]]);
	}

	/**
	 * Test add_answer method as an admin
	 */
	public function testAddAnswerAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Admins are allowed to add answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'add_answer', '?' => ['question' => $question->id]],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_answer method as a manager
	 */
	public function testAddAnswerAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Managers are allowed to add answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'add_answer', '?' => ['question' => $questions[0]->id]],
			$manager->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'add_answer', '?' => ['question' => $questions[1]->id]],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_answer method as others
	 */
	public function testAddAnswerAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Others are not allowed to add answers
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'add_answer', '?' => ['question' => $question->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'add_answer', '?' => ['question' => $question->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'add_answer', '?' => ['question' => $question->id]]);
	}

	/**
	 * Test delete_answer method as an admin
	 */
	public function testDeleteAnswerAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])
			->with('Answers')
			->persist();
		$answer = $question->answers[0];

		// Admins are allowed to delete answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'delete_answer', '?' => ['answer' => $answer->id]],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_answer method as a manager
	 */
	public function testDeleteAnswerAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questions = QuestionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])
			->with('Answers')
			->persist();

		// Managers are allowed to delete answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'delete_answer', '?' => ['answer' => $questions[0]->answers[0]->id]],
			$manager->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'delete_answer', '?' => ['answer' => $questions[1]->answers[0]->id]],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete_answer method as others
	 */
	public function testDeleteAnswerAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])
			->with('Answers')
			->persist();
		$answer = $question->answers[0];

		// Others are not allowed to delete answers
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'delete_answer', '?' => ['answer' => $answer->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'delete_answer', '?' => ['answer' => $answer->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'delete_answer', '?' => ['answer' => $answer->id]]);
	}

	/**
	 * Test autocomplete method
	 */
	public function testAutocomplete(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];

		QuestionFactory::make([
			'affiliate_id' => $affiliate->id,
			'question' => 'autocomplete test',
		])->persist();

		// Admins are allowed to autocomplete
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'autocomplete', '?' => ['affiliate' => $affiliate->id], 'term' => 'test'],
			$admin->id);

		// Managers are allowed to autocomplete
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questions', 'action' => 'autocomplete', '?' => ['affiliate' => $affiliate->id], 'term' => 'test'],
			$manager->id);

		// Others are not allowed to autocomplete
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'autocomplete', '?' => ['affiliate' => $affiliate->id], 'term' => 'test'],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questions', 'action' => 'autocomplete', '?' => ['affiliate' => $affiliate->id], 'term' => 'test'],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'autocomplete', '?' => ['affiliate' => $affiliate->id], 'term' => 'test']);
	}

	/**
	 * Test consolidate method as an admin
	 */
	public function testConsolidateAsAdmin(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to consolidate
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'consolidate'], $admin->id);
	}

	/**
	 * Test consolidate method as a manager
	 */
	public function testConsolidateAsManager(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Managers are allowed to consolidate
		$this->assertGetAsAccessOk(['controller' => 'Questions', 'action' => 'consolidate'], $manager->id);
	}

	/**
	 * Test consolidate method as others
	 */
	public function testConsolidateAsOthers(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Others are now allowed to consolidate
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'consolidate'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questions', 'action' => 'consolidate'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questions', 'action' => 'consolidate']);
	}

}
