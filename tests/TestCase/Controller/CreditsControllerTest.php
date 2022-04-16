<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\CreditFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\TestSuite\EmailTrait;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\CreditsController Test Case
 */
class CreditsControllerTest extends ControllerTestCase {

	use EmailTrait;
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

	public function tearDown(): void {
		// Cleanup any emails that were sent
		$this->cleanupEmailTrait();

		parent::tearDown();
	}

	/**
	 * Test index method
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
	 */
	public function testView(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method
	 */
	public function testAdd(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method
	 */
	public function testEdit(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer method as an admin
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

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test transfer method as a manager
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
	 */
	public function testTransferAsOwner(): void {
		[$admin, , $source, $target] = $this->loadFixtureScenario(DiverseUsersScenario::class);
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

		$this->assertMailCount(1);
		$this->assertMailSentFrom('admin@zuluru.org');
		$this->assertMailSentTo($target->email);
		$this->assertMailSentWith('Test Zuluru Affiliate Credit transferred', 'Subject');
		$this->assertMailContains('A credit with a balance of CA$1.00 has been transferred to you.');
		$this->assertMailContains('This credit can be redeemed towards any future purchase on the Test Zuluru Affiliate site');
	}

	/**
	 * Test transfer method as a relative of the credit owner
	 */
	public function testTransferAsRelative(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer method as others
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
	 */
	public function testDeleteAsAdmin(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a coordinator
	 */
	public function testDeleteAsOthers(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
