<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\WaiverFactory;
use App\Test\Factory\WaiversPersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\WaiversController Test Case
 */
class WaiversControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.UserGroups',
		'app.Settings',
	];

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();
		$affiliate_waiver = WaiverFactory::make(['affiliate_id' => $affiliates[1]->id, 'expiry_type' => 'never'])
			->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/waivers/edit?waiver=' . $waiver->id);
		$this->assertResponseContains('/waivers/delete?waiver=' . $waiver->id);
		$this->assertResponseContains('/waivers/edit?waiver=' . $affiliate_waiver->id);
		$this->assertResponseContains('/waivers/delete?waiver=' . $affiliate_waiver->id);

		// Managers are allowed to see the index, but don't see waivers in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/waivers/edit?waiver=' . $waiver->id);
		$this->assertResponseContains('/waivers/delete?waiver=' . $waiver->id);
		$this->assertResponseNotContains('/waivers/edit?waiver=' . $affiliate_waiver->id);
		$this->assertResponseNotContains('/waivers/delete?waiver=' . $affiliate_waiver->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'index']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();
		$affiliate_waiver = WaiverFactory::make(['affiliate_id' => $affiliates[1]->id, 'expiry_type' => 'never'])
			->persist();

		// Admins are allowed to view waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'view', '?' => ['waiver' => $waiver->id]], $admin->id);
		$this->assertResponseContains('/waivers/edit?waiver=' . $waiver->id);
		$this->assertResponseContains('/waivers/delete?waiver=' . $waiver->id);

		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'view', '?' => ['waiver' => $affiliate_waiver->id]], $admin->id);
		$this->assertResponseContains('/waivers/edit?waiver=' . $affiliate_waiver->id);
		$this->assertResponseContains('/waivers/delete?waiver=' . $affiliate_waiver->id);

		// Managers are allowed to view waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'view', '?' => ['waiver' => $waiver->id]], $manager->id);
		$this->assertResponseContains('/waivers/edit?waiver=' . $waiver->id);
		$this->assertResponseContains('/waivers/delete?waiver=' . $waiver->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'view', '?' => ['waiver' => $affiliate_waiver->id]], $manager->id);

		// Others are not allowed to view waivers
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'view', '?' => ['waiver' => $waiver->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'view', '?' => ['waiver' => $waiver->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'view', '?' => ['waiver' => $waiver->id]]);
	}

	/**
	 * Test add method
	 */
	public function testAdd(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to add waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'add'], $admin->id);

		// Managers are allowed to add waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'add'], $manager->id);

		// Others are not allowed to add waivers
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();
		$affiliate_waiver = WaiverFactory::make(['affiliate_id' => $affiliates[1]->id, 'expiry_type' => 'never'])
			->persist();

		// Admins are allowed to edit waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'edit', '?' => ['waiver' => $waiver->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'edit', '?' => ['waiver' => $affiliate_waiver->id]], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();
		$affiliate_waiver = WaiverFactory::make(['affiliate_id' => $affiliates[1]->id, 'expiry_type' => 'never'])
			->persist();

		// Managers are allowed to edit waivers
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'edit', '?' => ['waiver' => $waiver->id]], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'edit', '?' => ['waiver' => $affiliate_waiver->id]], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();

		// Others are not allowed to edit waivers
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'edit', '?' => ['waiver' => $waiver->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Waivers', 'action' => 'edit', '?' => ['waiver' => $waiver->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'edit', '?' => ['waiver' => $waiver->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();
		$dependent_waiver = WaiverFactory::make(['affiliate_id' => $affiliates[1]->id, 'expiry_type' => 'never'])
			->persist();

		// Admins are allowed to delete waivers
		$this->assertPostAsAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', '?' => ['waiver' => $waiver->id]],
			$admin->id, [], ['controller' => 'Waivers', 'action' => 'index'],
			'The waiver has been deleted.');

		// But not ones with dependencies
		WaiversPersonFactory::make(['waiver_id' => $dependent_waiver->id, 'person_id' => $admin->id])->persist();
		$this->assertPostAsAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', '?' => ['waiver' => $dependent_waiver->id]],
			$admin->id, [], ['controller' => 'Waivers', 'action' => 'index'],
			'#The following records reference this waiver, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();
		$affiliate_waiver = WaiverFactory::make(['affiliate_id' => $affiliates[1]->id, 'expiry_type' => 'never'])
			->persist();

		// Managers are allowed to delete waivers in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Waivers', 'action' => 'delete', '?' => ['waiver' => $waiver->id]],
			$manager->id, [], ['controller' => 'Waivers', 'action' => 'index'],
			'The waiver has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Waivers', 'action' => 'delete', '?' => ['waiver' => $affiliate_waiver->id]],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();

		// Others are not allowed to delete waivers
		$this->assertPostAsAccessDenied(['controller' => 'Waivers', 'action' => 'delete', '?' => ['waiver' => $waiver->id]],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Waivers', 'action' => 'delete', '?' => ['waiver' => $waiver->id]],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'delete', '?' => ['waiver' => $waiver->id]]);
	}

	/**
	 * Test sign method
	 */
	public function testSign(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();

		// All registered users are allowed to sign
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', '?' => ['waiver' => $waiver->id, 'date' => FrozenDate::now()->toDateString()]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', '?' => ['waiver' => $waiver->id, 'date' => FrozenDate::now()->toDateString()]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', '?' => ['waiver' => $waiver->id, 'date' => FrozenDate::now()->toDateString()]], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'sign', '?' => ['waiver' => $waiver->id, 'date' => FrozenDate::now()->toDateString()]], $player->id);

		// Others are not allowed to sign
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'sign', '?' => ['waiver' => $waiver->id, 'date' => FrozenDate::now()->toDateString()]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test review method
	 */
	public function testReview(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$waiver = WaiverFactory::make(['affiliate_id' => $affiliates[0]->id, 'expiry_type' => 'fixed_dates', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31])
			->persist();

		// All registered users are allowed to review
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', '?' => ['waiver' => $waiver->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', '?' => ['waiver' => $waiver->id]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', '?' => ['waiver' => $waiver->id]], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Waivers', 'action' => 'review', '?' => ['waiver' => $waiver->id]], $player->id);

		// Others are not allowed to review
		$this->assertGetAnonymousAccessDenied(['controller' => 'Waivers', 'action' => 'review', '?' => ['waiver' => $waiver->id]]);
	}

}
