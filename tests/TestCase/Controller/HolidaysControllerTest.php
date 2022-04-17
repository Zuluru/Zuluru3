<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\HolidayFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\HolidaysController Test Case
 */
class HolidaysControllerTest extends ControllerTestCase {

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

		$holiday = HolidayFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$affiliate_holiday = HolidayFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/holidays/edit?holiday=' . $holiday->id);
		$this->assertResponseContains('/holidays/delete?holiday=' . $holiday->id);
		$this->assertResponseContains('/holidays/edit?holiday=' . $affiliate_holiday->id);
		$this->assertResponseContains('/holidays/delete?holiday=' . $affiliate_holiday->id);

		// Managers are allowed to see the index, but don't see holidays in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/holidays/edit?holiday=' . $holiday->id);
		$this->assertResponseContains('/holidays/delete?holiday=' . $holiday->id);
		$this->assertResponseNotContains('/holidays/edit?holiday=' . $affiliate_holiday->id);
		$this->assertResponseNotContains('/holidays/delete?holiday=' . $affiliate_holiday->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Holidays', 'action' => 'index']);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Admins are allowed to add holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'add'], $admin->id);
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Managers are allowed to add holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'add'], $manager->id);
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Others are not allowed to add holidays
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Holidays', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$holiday = HolidayFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$affiliate_holiday = HolidayFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Admins are allowed to edit holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => $holiday->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => $affiliate_holiday->id], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$holiday = HolidayFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$affiliate_holiday = HolidayFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Managers are allowed to edit holidays
		$this->assertGetAsAccessOk(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => $holiday->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => $affiliate_holiday->id], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$holiday = HolidayFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Others are not allowed to edit holidays
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => $holiday->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => $holiday->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Holidays', 'action' => 'edit', 'holiday' => $holiday->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$holiday = HolidayFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Admins are allowed to delete holidays
		$this->assertPostAsAccessRedirect(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => $holiday->id],
			$admin->id, [], ['controller' => 'Holidays', 'action' => 'index'],
			'The holiday has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$holiday = HolidayFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();
		$affiliate_holiday = HolidayFactory::make(['affiliate_id' => $affiliates[1]->id])->persist();

		// Managers are allowed to delete holidays in their own affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => $holiday->id],
			$manager->id, [], ['controller' => 'Holidays', 'action' => 'index'],
			'The holiday has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => $affiliate_holiday->id],
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

		$holiday = HolidayFactory::make(['affiliate_id' => $affiliates[0]->id])->persist();

		// Others are not allowed to delete holidays
		$this->assertPostAsAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => $holiday->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => $holiday->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Holidays', 'action' => 'delete', 'holiday' => $holiday->id]);
	}

}
