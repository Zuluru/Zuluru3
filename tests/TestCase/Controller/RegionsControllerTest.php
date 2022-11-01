<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\RegionFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\RegionsController Test Case
 */
class RegionsControllerTest extends ControllerTestCase {

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

		$regions = RegionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/regions/edit?region=' . $regions[0]->id);
		$this->assertResponseContains('/regions/delete?region=' . $regions[0]->id);
		$this->assertResponseContains('/regions/edit?region=' . $regions[1]->id);
		$this->assertResponseContains('/regions/delete?region=' . $regions[1]->id);

		// Managers are allowed to see the index, but don't see regions in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/regions/edit?region=' . $regions[0]->id);
		$this->assertResponseContains('/regions/delete?region=' . $regions[0]->id);
		$this->assertResponseNotContains('/regions/edit?region=' . $regions[1]->id);
		$this->assertResponseNotContains('/regions/delete?region=' . $regions[1]->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'index']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$regions = RegionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Admins are allowed to view regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'view', 'region' => $regions[0]->id], $admin->id);
		$this->assertResponseContains('/regions/edit?region=' . $regions[0]->id);
		$this->assertResponseContains('/regions/delete?region=' . $regions[0]->id);

		// Managers are allowed to view regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'view', 'region' => $regions[0]->id], $manager->id);
		$this->assertResponseContains('/regions/edit?region=' . $regions[0]->id);
		$this->assertResponseContains('/regions/delete?region=' . $regions[0]->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => $regions[1]->id], $manager->id);

		// Others are not allowed to view regions
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => $regions[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => $regions[0]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'view', 'region' => $regions[0]->id]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to add regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'add'], $admin->id);
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to add regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'add'], $manager->id);
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer']);

		// Others are not allowed to add regions
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'add'], $volunteer->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$regions = RegionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Admins are allowed to edit regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'edit', 'region' => $regions[0]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'edit', 'region' => $regions[1]->id], $admin->id);
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$regions = RegionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Managers are allowed to edit regions
		$this->assertGetAsAccessOk(['controller' => 'Regions', 'action' => 'edit', 'region' => $regions[0]->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => $regions[1]->id], $manager->id);
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$region = RegionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Others are not allowed to edit regions
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => $region->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => $region->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'edit', 'region' => $region->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$region = RegionFactory::make([
			'affiliate_id' => $affiliates[0]->id,
		])->persist();

		$dependent_region = RegionFactory::make([
			'affiliate_id' => $affiliates[1]->id,
		])
			->with('Facilities')
			->persist();

		// Admins are allowed to delete regions
		$this->assertPostAsAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => $region->id],
			$admin->id, [], ['controller' => 'Regions', 'action' => 'index'],
			'The region has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => $dependent_region->id],
			$admin->id, [], ['controller' => 'Regions', 'action' => 'index'],
			'#The following records reference this region, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$regions = RegionFactory::make([
			[
				'affiliate_id' => $affiliates[0]->id,
			],
			[
				'affiliate_id' => $affiliates[1]->id,
			],
		])->persist();

		// Managers are allowed to delete regions in their own affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Regions', 'action' => 'delete', 'region' => $regions[0]->id],
			$manager->id, [], ['controller' => 'Regions', 'action' => 'index'],
			'The region has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => $regions[1]->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$region = RegionFactory::make([
			'affiliate_id' => $admin->affiliates[0]->id,
		])->persist();

		// Others are not allowed to delete regions
		$this->assertPostAsAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => $region->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => $region->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Regions', 'action' => 'delete', 'region' => $region->id]);
	}

}
