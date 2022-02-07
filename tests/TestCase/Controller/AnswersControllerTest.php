<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\AnswerFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\QuestionFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\AnswersController Test Case
 */
class AnswersControllerTest extends ControllerTestCase {

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
	 * Test activate method as an admin
	 *
	 * @return void
	 */
	public function testActivateAsAdmin(): void {
		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliate)->persist();
		$answer = AnswerFactory::make(['active' => false])->with('Questions', ['affiliate_id' => $affiliate->id])->persist();

		// Admins are allowed to activate answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Answers', 'action' => 'activate', 'answer' => $answer->id],
			$admin->id);
		$this->assertResponseContains('/answers\\/deactivate?answer=' . $answer->id);
	}

	/**
	 * Test activate method as a manager
	 *
	 * @return void
	 */
	public function testActivateAsManager(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$manager = PersonFactory::makeManager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$questions = QuestionFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->with('Answers', ['active' => false])->persist();

		// Managers are allowed to activate answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Answers', 'action' => 'activate', 'answer' => $questions[0]->answers[0]->id],
			$manager->id);
		$this->assertResponseContains('/answers\\/deactivate?answer=' . $questions[0]->answers[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => $questions[1]->answers[0]->id],
			$manager->id);
	}

	/**
	 * Test activate method as others
	 *
	 * @return void
	 */
	public function testActivateAsOthers(): void {
		$affiliate = AffiliateFactory::make()->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliate)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliate)->persist();
		$answer = AnswerFactory::make(['active' => false])->with('Questions', ['affiliate_id' => $affiliate->id])->persist();

		// Others are not allowed to activate answers
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => $answer->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => $answer->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Answers', 'action' => 'activate', 'answer' => $answer->id]);
	}

	/**
	 * Test deactivate method as an admin
	 *
	 * @return void
	 */
	public function testDeactivateAsAdmin(): void {
		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliate)->persist();
		$answer = AnswerFactory::make()->with('Questions', ['affiliate_id' => $affiliate->id])->persist();

		// Admins are allowed to deactivate answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => $answer->id],
			$admin->id);
		$this->assertResponseContains('/answers\\/activate?answer=' . $answer->id);
	}

	/**
	 * Test deactivate method as a manager
	 *
	 * @return void
	 */
	public function testDeactivateAsManager(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$manager = PersonFactory::makeManager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$questions = QuestionFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->with('Answers')->persist();

		// Managers are allowed to deactivate answers
		$this->assertGetAjaxAsAccessOk(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => $questions[0]->answers[0]->id],
			$manager->id);
		$this->assertResponseContains('/answers\\/activate?answer=' . $questions[0]->answers[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => $questions[1]->answers[0]->id],
			$manager->id);
	}

	/**
	 * Test deactivate method as others
	 *
	 * @return void
	 */
	public function testDeactivateAsOthers(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		$answer = AnswerFactory::make()->with('Questions', ['affiliate_id' => $affiliate->id])->persist();

		// Others are not allowed to deactivate answers
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => $answer->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => $answer->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Answers', 'action' => 'deactivate', 'answer' => $answer->id]);
	}

}
