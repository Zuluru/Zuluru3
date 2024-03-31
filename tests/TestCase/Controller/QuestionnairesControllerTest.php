<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\EventFactory;
use App\Test\Factory\QuestionFactory;
use App\Test\Factory\QuestionnaireFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\QuestionnairesController Test Case
 */
class QuestionnairesControllerTest extends ControllerTestCase {

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

		$questionnaires = QuestionnaireFactory::make([
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
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaires[0]->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaires[0]->id);
		$this->assertResponseContains('/questionnaires/deactivate?questionnaire=' . $questionnaires[0]->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaires[1]->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaires[1]->id);
		$this->assertResponseContains('/questionnaires/deactivate?questionnaire=' . $questionnaires[1]->id);
		$this->assertResponseNotContains('/questionnaires/edit?questionnaire=' . $questionnaires[2]->id);
		$this->assertResponseNotContains('/questionnaires/delete?questionnaire=' . $questionnaires[2]->id);
		$this->assertResponseNotContains('/questionnaires/deactivate?questionnaire=' . $questionnaires[2]->id);

		// Managers are allowed to see the index, but don't see questionnaires in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaires[0]->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaires[0]->id);
		$this->assertResponseContains('/questionnaires/deactivate?questionnaire=' . $questionnaires[0]->id);
		$this->assertResponseNotContains('/questionnaires/edit?questionnaire=' . $questionnaires[1]->id);
		$this->assertResponseNotContains('/questionnaires/delete?questionnaire=' . $questionnaires[1]->id);
		$this->assertResponseNotContains('/questionnaires/deactivate?questionnaire=' . $questionnaires[1]->id);
		$this->assertResponseNotContains('/questionnaires/edit?questionnaire=' . $questionnaires[2]->id);
		$this->assertResponseNotContains('/questionnaires/delete?questionnaire=' . $questionnaires[2]->id);
		$this->assertResponseNotContains('/questionnaires/deactivate?questionnaire=' . $questionnaires[2]->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'index']);
	}

	/**
	 * Test deactivated method
	 */
	public function testDeactivated(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'active' => false,
		])->persist();

		// Admins are allowed to see the deactivated list
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivated'], $admin->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaire->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaire->id);
		$this->assertResponseContains('/questionnaires/activate?questionnaire=' . $questionnaire->id);

		// Managers are allowed to see the deactivated list
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivated'], $manager->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaire->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaire->id);
		$this->assertResponseContains('/questionnaires/activate?questionnaire=' . $questionnaire->id);

		// Others are not allowed to see the deactivated list
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivated'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivated'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivated']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$questionnaires = QuestionnaireFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
			[
				'affiliate_id' => $admin->affiliates[0]->id,
				'active' => false,
			],
		])->persist();

		// Admins are allowed to view questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' =>  $questionnaires[0]->id]], $admin->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaires[0]->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaires[0]->id);

		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' =>  $questionnaires[1]->id]], $admin->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaires[1]->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaires[1]->id);

		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' =>  $questionnaires[2]->id]], $admin->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaires[2]->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaires[2]->id);

		// Managers are allowed to view questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' =>  $questionnaires[0]->id]], $manager->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaires[0]->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaires[0]->id);

		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' =>  $questionnaires[2]->id]], $admin->id);
		$this->assertResponseContains('/questionnaires/edit?questionnaire=' . $questionnaires[2]->id);
		$this->assertResponseContains('/questionnaires/delete?questionnaire=' . $questionnaires[2]->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' =>  $questionnaires[1]->id]], $manager->id);

		// Others are not allowed to view questionnaires
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' =>  $questionnaires[0]->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' =>  $questionnaires[0]->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'view', '?' => ['questionnaire' =>  $questionnaires[0]->id]]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		// Admins are allowed to add questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'add'], $admin->id);
		// TODO: Database has default value of "1" for event affiliate_id, which auto-selects the primary affiliate in normal use.
		// Unit tests get some other ID for the affiliates, #1 doesn't exist, so there is no option selected. Either fix the
		// test or fix the default in the template or get rid of the default in the database. All only applies when there are
		// multiple affiliates anyway, otherwise the form makes the affiliate_id a hidden input.
		$this->assertResponseContains('<option value="' . $affiliates[0]->id . '">' . $affiliates[0]->name . '</option>');
		$this->assertResponseContains('<option value="' . $affiliates[1]->id . '">' . $affiliates[1]->name . '</option>');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		// Managers are allowed to add questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'add'], $manager->id);
		$this->assertResponseContains('<input type="hidden" name="affiliate_id" value="' . $affiliates[0]->id . '"/>');
		$this->assertResponseNotContains('<option value="' . $affiliates[1]->id . '">' . $affiliates[1]->name . '</option>');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer', 'player']);

		// Others are not allowed to add questionnaires
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$questionnaires = QuestionnaireFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
			[
				'affiliate_id' => $admin->affiliates[0]->id,
				'active' => false,
			],
		])->persist();

		// Admins are allowed to edit questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' =>  $questionnaires[0]->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' =>  $questionnaires[1]->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' =>  $questionnaires[2]->id]], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questionnaires = QuestionnaireFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
			[
				'affiliate_id' => $admin->affiliates[0]->id,
				'active' => false,
			],
		])->persist();

		// Managers are allowed to edit questionnaires
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' =>  $questionnaires[0]->id]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' =>  $questionnaires[2]->id]], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' =>  $questionnaires[1]->id]], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Others are not allowed to edit questionnaires
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' =>  $questionnaire->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' =>  $questionnaire->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'edit', '?' => ['questionnaire' =>  $questionnaire->id]]);
	}

	/**
	 * Test activate method as an admin
	 */
	public function testActivateAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'active' => false,
		])->persist();

		// Admins are allowed to activate questionnaires
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'activate', '?' => ['questionnaire' => $questionnaire->id]], $admin->id);
		$this->assertResponseContains('/questionnaires\\/deactivate?questionnaire=' . $questionnaire->id);
	}

	/**
	 * Test activate method as a manager
	 */
	public function testActivateAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questionnaires = QuestionnaireFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
				'active' => false,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
				'active' => false,
			],
		])->persist();

		// Managers are allowed to activate questionnaires
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'activate', '?' => ['questionnaire' => $questionnaires[0]->id]],
			$manager->id);
		$this->assertResponseContains('/questionnaires\\/deactivate?questionnaire=' . $questionnaires[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', '?' => ['questionnaire' => $questionnaires[1]->id]],
			$manager->id);
	}

	/**
	 * Test activate method as others
	 */
	public function testActivateAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
			'active' => false,
		])->persist();

		// Others are not allowed to activate questionnaires
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', '?' => ['questionnaire' => $questionnaire->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', '?' => ['questionnaire' => $questionnaire->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'activate', '?' => ['questionnaire' => $questionnaire->id]]);
	}

	/**
	 * Test deactivate method as an admin
	 */
	public function testDeactivateAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Admins are allowed to deactivate questionnaires
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivate', '?' => ['questionnaire' => $questionnaire->id]], $admin->id);
		$this->assertResponseContains('/questionnaires\\/activate?questionnaire=' . $questionnaire->id);
	}

	/**
	 * Test deactivate method as a manager
	 */
	public function testDeactivateAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questionnaires = QuestionnaireFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Managers are allowed to deactivate questionnaires
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'deactivate', '?' => ['questionnaire' => $questionnaires[0]->id]], $manager->id);
		$this->assertResponseContains('/questionnaires\\/activate?questionnaire=' . $questionnaires[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', '?' => ['questionnaire' => $questionnaires[1]->id]], $manager->id);
	}

	/**
	 * Test deactivate method as others
	 */
	public function testDeactivateAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Others are not allowed to deactivate questionnaires
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', '?' => ['questionnaire' => $questionnaire->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', '?' => ['questionnaire' => $questionnaire->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'deactivate', '?' => ['questionnaire' => $questionnaire->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$questionnaires = QuestionnaireFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[0]->id,
			],
		])->persist();

		// Make an event that references one of them
		EventFactory::make(['questionnaire_id' => $questionnaires[1]->id])->persist();

		// Admins are allowed to delete questionnaires
		$this->assertPostAsAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', '?' => ['questionnaire' => $questionnaires[0]->id]],
			$admin->id, [], ['controller' => 'Questionnaires', 'action' => 'index'],
			'The questionnaire has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', '?' => ['questionnaire' => $questionnaires[1]->id]],
			$admin->id, [], ['controller' => 'Questionnaires', 'action' => 'index'],
			'#The following records reference this questionnaire, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$questionnaires = QuestionnaireFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Managers are allowed to delete questionnaires in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Questionnaires', 'action' => 'delete', '?' => ['questionnaire' => $questionnaires[0]->id]],
			$manager->id, [], ['controller' => 'Questionnaires', 'action' => 'index'],
			'The questionnaire has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', '?' => ['questionnaire' => $questionnaires[1]->id]],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Others are not allowed to delete questionnaires
		$this->assertPostAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', '?' => ['questionnaire' => $questionnaire->id]],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', '?' => ['questionnaire' => $questionnaire->id]],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'delete', '?' => ['questionnaire' => $questionnaire->id]]);
	}

	/**
	 * Test add_question method as an admin
	 */
	public function testAddQuestionAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])
			->persist();

		// Admins are allowed to add question
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'add_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_question method as a manager
	 */
	public function testAddQuestionAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])
			->persist();

		// Managers are allowed to add question
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'add_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_question method as others
	 */
	public function testAddQuestionAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		$question = QuestionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])
			->persist();

		// Others are not allowed to add questions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'add_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'add_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]]);
	}

	/**
	 * Test remove_question method as an admin
	 */
	public function testRemoveQuestionAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])
			->with('Questions', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();
		$question = $questionnaire->questions[0];

		// Admins are allowed to remove question
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'remove_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test remove_question method as a manager
	 */
	public function testRemoveQuestionAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])
			->with('Questions', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();
		$question = $questionnaire->questions[0];

		// Managers are allowed to remove question
		$this->assertGetAjaxAsAccessOk(['controller' => 'Questionnaires', 'action' => 'remove_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test remove_question method as others
	 */
	public function testRemoveQuestionAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$questionnaire = QuestionnaireFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])
			->with('Questions', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();
		$question = $questionnaire->questions[0];

		// Others are not allowed to remove questions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'remove_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'remove_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'remove_question', '?' => ['questionnaire' =>  $questionnaire->id, 'question' => $question->id]]);
	}

	/**
	 * Test consolidate method as an admin
	 */
	public function testConsolidateAsAdmin(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to consolidate
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'consolidate'], $admin->id);
	}

	/**
	 * Test consolidate method as a manager
	 */
	public function testConsolidateAsManager(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);

		// Managers are allowed to consolidate
		$this->assertGetAsAccessOk(['controller' => 'Questionnaires', 'action' => 'consolidate'], $manager->id);
	}

	/**
	 * Test consolidate method as others
	 */
	public function testConsolidateAsOthers(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		// Others are not allowed to consolidate
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'consolidate'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Questionnaires', 'action' => 'consolidate'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Questionnaires', 'action' => 'consolidate']);
	}

}
