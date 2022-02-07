<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\CreditFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\Core\Configure;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\CreditsController Test Case
 */
class CreditsControllerTest extends ControllerTestCase {

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
	 *
	 * @return void
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;
		$credits = CreditFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->with('People')->persist();

		// Admins are allowed to list credits
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/credits/edit?credit=' . $credits[0]->id);
		$this->assertResponseContains('/credits/delete?credit=' . $credits[0]->id);
		$this->assertResponseContains('/credits/edit?credit=' . $credits[1]->id);
		$this->assertResponseContains('/credits/delete?credit=' . $credits[1]->id);

		// Managers are allowed to see the index, but don't see credits in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/credits/edit?credit=' . $credits[0]->id);
		$this->assertResponseContains('/credits/delete?credit=' . $credits[0]->id);
		$this->assertResponseNotContains('/credits/edit?credit=' . $credits[1]->id);
		$this->assertResponseNotContains('/credits/delete?credit=' . $credits[1]->id);

		// Others are not allowed to list credits
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Credits', 'action' => 'index']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method
	 *
	 * @return void
	 */
	public function testAdd(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method
	 *
	 * @return void
	 */
	public function testEdit(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer method as an admin
	 *
	 * @return void
	 */
	public function testTransferAsAdmin(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliates)->persist();
		$credits = CreditFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->with('People')->persist();

		// Admins are allowed to transfer credits
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credits[0]->id], $admin->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer method as a manager
	 *
	 * @return void
	 */
	public function testTransferAsManager(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$manager = PersonFactory::makeManager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$credits = CreditFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->with('People')->persist();

		// Managers are allowed to transfer credits
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credits[0]->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credits[1]->id], $manager->id);
	}

	/**
	 * Test transfer method as the credit owner
	 *
	 * @return void
	 */
	public function testTransferAsOwner(): void {
		[$admin, $manager, $source, $target] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		$credit = CreditFactory::make(['amount' => 11, 'amount_used' => 10, 'notes' => 'Credit note.'])
			->with('People', $source)
			->with('Affiliates', $affiliate)
			->persist();

		// People are allowed to transfer their own credits
		$this->assertGetAsAccessOk(['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credit->id], $source->id);
		$this->assertGetAsAccessRedirect(['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credit->id, 'person' => $target->id],
			$source->id,
			'/', 'The credit has been transferred.'
		);

		$credit = CreditFactory::get($credit->id);
		$this->assertEquals("Credit note.\nTransferred from {$source->full_name}.", $credit->notes);
		$this->assertEquals($target->id, $credit->person_id);

		$messages = Configure::consume('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertTextContains('A credit with a balance of CA$1.00 has been transferred to you.', $messages[0]);
		$this->assertTextContains('This credit can be redeemed towards any future purchase on the Test Zuluru Affiliate site', $messages[0]);
	}

	/**
	 * Test transfer method as a relative of the credit owner
	 *
	 * @return void
	 */
	public function testTransferAsRelative(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer method as others
	 *
	 * @return void
	 */
	public function testTransferAsOthers(): void {
		$affiliates = AffiliateFactory::make(2)->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliates[0])->persist();
		$credits = CreditFactory::make([
			['affiliate_id' => $affiliates[0]->id],
			['affiliate_id' => $affiliates[1]->id],
		])->with('People')->persist();

		// Others are not allowed to transfer credits
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credits[0]->id], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credits[1]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credits[0]->id]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credits[1]->id]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsOthers(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
