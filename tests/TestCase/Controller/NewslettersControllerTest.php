<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\MailingList;
use App\Test\Factory\ActivityLogFactory;
use App\Test\Factory\MailingListFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\NewslettersController Test Case
 */
class NewslettersControllerTest extends ControllerTestCase {

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

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters', [
				['target' => FrozenDate::now()->subMonths(2)],
				[],
			])
			->persist();

		/** @var MailingList $affiliate_list */
		$affiliate_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('Newsletters')
			->persist();

		// Admins are allowed to see the index, and all future newsletters will be on it
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'index'], $admin->id);
		$this->assertResponseNotContains('/newsletters/edit?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseNotContains('/newsletters/delete?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $list->newsletters[1]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $list->newsletters[1]->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $affiliate_list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $affiliate_list->newsletters[0]->id);

		// Managers are allowed to see the index, but don't see newsletters in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'index'], $manager->id);
		$this->assertResponseNotContains('/newsletters/edit?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseNotContains('/newsletters/delete?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $list->newsletters[1]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $list->newsletters[1]->id);
		$this->assertResponseNotContains('/newsletters/edit?newsletter=' . $affiliate_list->newsletters[0]->id);
		$this->assertResponseNotContains('/newsletters/delete?newsletter=' . $affiliate_list->newsletters[0]->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test past method
	 */
	public function testPast(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters', [
				['target' => FrozenDate::now()->subMonths(2)],
				[],
			])
			->persist();

		/** @var MailingList $affiliate_list */
		$affiliate_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('Newsletters')
			->persist();

		// Admins are allowed to see the past index
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'past'], $admin->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $list->newsletters[1]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $list->newsletters[1]->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $affiliate_list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $affiliate_list->newsletters[0]->id);

		// Managers are allowed to see the past index
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'past'], $manager->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $list->newsletters[1]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $list->newsletters[1]->id);
		$this->assertResponseNotContains('/newsletters/edit?newsletter=' . $affiliate_list->newsletters[0]->id);
		$this->assertResponseNotContains('/newsletters/delete?newsletter=' . $affiliate_list->newsletters[0]->id);

		// Others are not allowed to see the past index
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'past'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'past'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'past']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		/** @var MailingList $affiliate_list */
		$affiliate_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('Newsletters')
			->persist();

		// Admins are allowed to view newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => $list->newsletters[0]->id], $admin->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $list->newsletters[0]->id);

		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => $affiliate_list->newsletters[0]->id], $admin->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $affiliate_list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $affiliate_list->newsletters[0]->id);

		// Managers are allowed to view newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => $list->newsletters[0]->id], $manager->id);
		$this->assertResponseContains('/newsletters/edit?newsletter=' . $list->newsletters[0]->id);
		$this->assertResponseContains('/newsletters/delete?newsletter=' . $list->newsletters[0]->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => $affiliate_list->newsletters[0]->id], $manager->id);

		// Others are not allowed to view newsletters
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => $list->newsletters[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => $list->newsletters[0]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'view', 'newsletter' => $list->newsletters[0]->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->persist();

		/** @var MailingList $affiliate_list */
		$affiliate_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Admins are allowed to add newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'add'], $admin->id);
		$this->assertResponseContains('<option value="' . $list->id . '">' . $list->name . '</option>');
		$this->assertResponseContains('<option value="' . $affiliate_list->id . '">' . $affiliate_list->name . '</option>');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->persist();

		/** @var MailingList $affiliate_list */
		$affiliate_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Managers are allowed to add newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'add'], $manager->id);
		$this->assertResponseContains('<option value="' . $list->id . '">' . $list->name . '</option>');
		$this->assertResponseNotContains('<option value="' . $affiliate_list->id . '">' . $affiliate_list->name . '</option>');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Others are not allowed to add newsletters
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		/** @var MailingList $affiliate_list */
		$affiliate_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('Newsletters')
			->persist();

		// Admins are allowed to edit newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => $list->newsletters[0]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => $affiliate_list->newsletters[0]->id], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		/** @var MailingList $affiliate_list */
		$affiliate_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('Newsletters')
			->persist();

		// Managers are allowed to edit newsletters
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => $list->newsletters[0]->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => $affiliate_list->newsletters[0]->id], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		// Others are not allowed to edit newsletters
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => $list->newsletters[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => $list->newsletters[0]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'edit', 'newsletter' => $list->newsletters[0]->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters', [
				['target' => FrozenDate::now()->subMonths(2)],
				[],
			])
			->persist();

		// Admins are allowed to delete newsletters
		$this->assertPostAsAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => $list->newsletters[0]->id],
			$admin->id, [], ['controller' => 'Newsletters', 'action' => 'index'],
			'The newsletter has been deleted.');

		// But not ones with dependencies
		ActivityLogFactory::make(['type' => 'newsletter', 'newsletter_id' => $list->newsletters[1]->id, 'person_id' => $admin->id])->persist();
		$this->assertPostAsAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => $list->newsletters[1]->id],
			$admin->id, [], ['controller' => 'Newsletters', 'action' => 'index'],
			'#The following records reference this newsletter, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		/** @var MailingList $affiliate_list */
		$affiliate_list = MailingListFactory::make(['affiliate_id' => $affiliates[1]->id])
			->with('Newsletters')
			->persist();

		// Managers are allowed to delete newsletters in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => $list->newsletters[0]->id],
			$manager->id, [], ['controller' => 'Newsletters', 'action' => 'index'],
			'The newsletter has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => $affiliate_list->newsletters[0]->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		// Others are not allowed to delete newsletters
		$this->assertPostAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => $list->newsletters[0]->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => $list->newsletters[0]->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'delete', 'newsletter' => $list->newsletters[0]->id]);
	}

	/**
	 * Test delivery method
	 */
	public function testDelivery(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		// Admins are allowed to see the delivery report
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => $list->newsletters[0]->id], $admin->id);

		// Managers are allowed to see the delivery report
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => $list->newsletters[0]->id], $manager->id);

		// Others are not allowed to see the delivery report
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => $list->newsletters[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => $list->newsletters[0]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'delivery', 'newsletter' => $list->newsletters[0]->id]);
	}

	/**
	 * Test send method as an admin
	 */
	public function testSendAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id, 'rule' => 'COMPARE("1" = "1")'])
			->with('Newsletters')
			->persist();

		// Admins are allowed to send
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => $list->newsletters[0]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => $list->newsletters[0]->id, 'execute' => true, 'test' => true], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test send method as a manager
	 */
	public function testSendAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		// Managers are allowed to send
		$this->assertGetAsAccessOk(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => $list->newsletters[0]->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test send method as others
	 */
	public function testSendAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		/** @var MailingList $list */
		$list = MailingListFactory::make(['affiliate_id' => $affiliates[0]->id])
			->with('Newsletters')
			->persist();

		// Others are not allowed to send
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => $list->newsletters[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => $list->newsletters[0]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Newsletters', 'action' => 'send', 'newsletter' => $list->newsletters[0]->id]);
	}

}
