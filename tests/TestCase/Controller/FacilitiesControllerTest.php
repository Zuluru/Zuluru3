<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\RegionFactory;
use App\Test\Factory\TeamsFacilityFactory;
use App\Test\Scenario\DiverseFacilitiesScenario;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\FacilitiesController Test Case
 */
class FacilitiesControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Countries',
		'app.Groups',
		'app.Provinces',
		'app.Settings',
	];

	/**
	 * Test index method
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$affiliate_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/facilities/view?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/facilities/close?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/facilities/view?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseContains('/facilities/close?facility=' . $affiliate_region->facilities[0]->id);

		// This field doesn't have a layout defined yet, so no map link
		$this->assertResponseNotContains('/maps/view?field=' . $region->facilities[0]->fields[2]->id);

		// This facility is closed, so no link on the main index
		$this->assertResponseNotContains('/facilities/view?facility=' . $region->facilities[1]->id);

		// Managers are allowed to see the index, but don't see facilities in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/facilities/view?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/facilities/close?facility=' . $region->facilities[0]->id);
		$this->assertResponseNotContains('/facilities/view?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseNotContains('/maps/view?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/facilities/edit?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseNotContains('/facilities/delete?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseNotContains('/facilities/close?facility=' . $affiliate_region->facilities[0]->id);

		// Coordinators are allowed to see the index, but no edit options
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], $volunteer->id);
		$this->assertResponseContains('/facilities/view?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/facilities/edit?facility=');
		$this->assertResponseNotContains('/facilities/delete?facility=');
		$this->assertResponseNotContains('/facilities/close?facility=');

		// Players are allowed to see the index, but no edit options
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'index'], $player->id);
		$this->assertResponseContains('/facilities/view?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/facilities/edit?facility=');
		$this->assertResponseNotContains('/facilities/delete?facility=');
		$this->assertResponseNotContains('/facilities/close?facility=');

		// Others are allowed to see the index
		$this->assertGetAnonymousAccessOk(['controller' => 'Facilities', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test closed method
	 */
	public function testClosed(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Admins are allowed to see the closed index
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'closed'], $admin->id);
		$this->assertResponseContains('/facilities/view?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/facilities/open?facility=' . $region->facilities[1]->id);

		// Managers are allowed to see the closed index
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'closed'], $manager->id);
		$this->assertResponseContains('/facilities/view?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/facilities/open?facility=' . $region->facilities[1]->id);

		// Others are not allowed to see the closed index
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'closed'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'closed'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'closed']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$affiliate_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Admins are allowed to view facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id], $admin->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/maps/edit?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/delete?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/close?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/bookings?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/game_slots/add?field=' . $region->facilities[0]->fields[0]->id);

		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => $affiliate_region->facilities[0]->id], $admin->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/maps/edit?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/delete?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/close?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/bookings?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/game_slots/add?field=' . $affiliate_region->facilities[0]->fields[0]->id);

		// Admins are allowed to view closed facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[1]->id], $admin->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/maps/edit?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/fields/delete?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/fields/open?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/fields/bookings?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/game_slots/add?field=' . $region->facilities[1]->fields[0]->id);

		// Managers are allowed to view facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id], $manager->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/maps/edit?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/delete?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/close?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/bookings?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/game_slots/add?field=' . $region->facilities[0]->fields[0]->id);

		// Managers are allowed to view facilities from other affiliates, but have no edit options
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => $affiliate_region->facilities[0]->id], $manager->id);
		$this->assertResponseNotContains('/facilities/edit?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseNotContains('/facilities/delete?facility=' . $affiliate_region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/maps/edit?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/fields/delete?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/fields/close?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/bookings?field=' . $affiliate_region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/game_slots/add?field=' . $affiliate_region->facilities[0]->fields[0]->id);

		// Managers are allowed to view closed facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[1]->id], $manager->id);
		$this->assertResponseContains('/facilities/edit?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/facilities/delete?facility=' . $region->facilities[1]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/maps/edit?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/fields/delete?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/fields/open?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/fields/bookings?field=' . $region->facilities[1]->fields[0]->id);
		$this->assertResponseContains('/game_slots/add?field=' . $region->facilities[1]->fields[0]->id);

		// Others are allowed to view facilities, but no edit options
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id], $volunteer->id);
		$this->assertResponseNotContains('/facilities/edit?facility=' . $region->facilities[0]->id);
		$this->assertResponseNotContains('/facilities/delete?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/maps/edit?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/fields/delete?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/fields/close?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/bookings?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/game_slots/add?field=' . $region->facilities[0]->fields[0]->id);

		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id], $player->id);
		$this->assertResponseNotContains('/facilities/edit?facility=' . $region->facilities[0]->id);
		$this->assertResponseNotContains('/facilities/delete?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/maps/edit?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/fields/delete?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/fields/close?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/bookings?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/game_slots/add?field=' . $region->facilities[0]->fields[0]->id);

		$this->assertGetAnonymousAccessOk(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id]);
		$this->assertResponseNotContains('/facilities/edit?facility=' . $region->facilities[0]->id);
		$this->assertResponseNotContains('/facilities/delete?facility=' . $region->facilities[0]->id);
		$this->assertResponseContains('/maps/view?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/maps/edit?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/fields/delete?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/fields/close?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseContains('/fields/bookings?field=' . $region->facilities[0]->fields[0]->id);
		$this->assertResponseNotContains('/game_slots/add?field=' . $region->facilities[0]->fields[0]->id);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		RegionFactory::make(['affiliate_id' => $affiliates[0]->id], 2)
			->persist();

		// Admins are allowed to add facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'add'], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		RegionFactory::make(['affiliate_id' => $affiliates[0]->id], 2)
			->persist();

		// Managers are allowed to add facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'add'], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		RegionFactory::make(['affiliate_id' => $affiliates[0]->id], 2)
			->persist();

		// Others are not allowed to add facilities
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$affiliate_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Admins are allowed to edit facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'edit', 'facility' => $region->facilities[0]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'edit', 'facility' => $affiliate_region->facilities[0]->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$affiliate_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Managers are allowed to edit facilities
		$this->assertGetAsAccessOk(['controller' => 'Facilities', 'action' => 'edit', 'facility' => $region->facilities[0]->id], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => $affiliate_region->facilities[0]->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Others are not allowed to edit facilities
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => $region->facilities[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => $region->facilities[0]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'edit', 'facility' => $region->facilities[0]->id]);
	}

	/**
	 * Test add_field method as an admin
	 */
	public function testAddFieldAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to add field
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'add_field'],
			$admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_field method as a manager
	 */
	public function testAddFieldAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to add field
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'add_field'],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add_field method as others
	 */
	public function testAddFieldAsOthers(): void {
		[$volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer', 'player']);

		// Others are not allowed to add fields
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add_field'],
			$volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Facilities', 'action' => 'add_field'],
			$player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'add_field']);
	}

	/**
	 * Test open method as an admin
	 */
	public function testOpenAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Admins are allowed to open facilities
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'open', 'facility' => $region->facilities[1]->id],
			$admin->id);
		$this->assertResponseContains('/facilities\\/close?facility=' . $region->facilities[1]->id);
		// Confirm that all related fields remain closed
		$fields = TableRegistry::getTableLocator()->get('Fields');
		$query = $fields->find()->where(['facility_id' => $region->facilities[1]->id, 'is_open' => true]);
		$this->assertEquals(0, $query->count());
	}

	/**
	 * Test open method as a manager
	 */
	public function testOpenAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$affiliate_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Managers are allowed to open facilities
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'open', 'facility' => $region->facilities[1]->id],
			$manager->id);
		$this->assertResponseContains('/facilities\\/close?facility=' . $region->facilities[1]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => $affiliate_region->facilities[0]->id],
			$manager->id);
	}

	/**
	 * Test open method as others
	 */
	public function testOpenAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Others are not allowed to open facilities
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => $region->facilities[1]->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => $region->facilities[1]->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'open', 'facility' => $region->facilities[1]->id]);
	}

	/**
	 * Test close method as an admin
	 */
	public function testCloseAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Admins are allowed to close facilities
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'close', 'facility' => $region->facilities[0]->id],
			$admin->id);
		$this->assertResponseContains('/facilities\\/open?facility=' . $region->facilities[0]->id);
		// Confirm that all related fields were also closed
		$fields = TableRegistry::getTableLocator()->get('Fields');
		$query = $fields->find()->where(['facility_id' => $region->facilities[0]->id, 'is_open' => true]);
		$this->assertEquals(0, $query->count());
	}

	/**
	 * Test close method as a manager
	 */
	public function testCloseAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$affiliate_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Managers are allowed to close facilities
		$this->assertGetAjaxAsAccessOk(['controller' => 'Facilities', 'action' => 'close', 'facility' => $region->facilities[0]->id],
			$manager->id);
		$this->assertResponseContains('/facilities\\/open?facility=' . $region->facilities[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => $affiliate_region->facilities[0]->id],
			$manager->id);
	}

	/**
	 * Test close method as others
	 */
	public function testCloseAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Others are not allowed to close facilities
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => $region->facilities[0]->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => $region->facilities[0]->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'close', 'facility' => $region->facilities[0]->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$affiliate_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Admins are allowed to delete facilities
		$this->assertPostAsAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => $region->facilities[1]->id],
			$admin->id, [], ['controller' => 'Facilities', 'action' => 'index'],
			'The facility has been deleted.');
		$this->assertPostAsAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => $affiliate_region->facilities[1]->id],
			$admin->id, [], ['controller' => 'Facilities', 'action' => 'index'],
			'The facility has been deleted.');

		// But not ones with dependencies
		TeamsFacilityFactory::make(['facility_id' => $region->facilities[0]->id, 'team_id' => 1])->persist();
		$this->assertPostAsAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => $region->facilities[0]->id],
			$admin->id, [], ['controller' => 'Facilities', 'action' => 'index'],
			'#The following records reference this facility, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$affiliate_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Managers are allowed to delete facilities in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Facilities', 'action' => 'delete', 'facility' => $region->facilities[1]->id],
			$manager->id, [], ['controller' => 'Facilities', 'action' => 'index'],
			'The facility has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => $affiliate_region->facilities[0]->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Others are not allowed to delete facilities
		$this->assertPostAsAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => $region->facilities[1]->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => $region->facilities[1]->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Facilities', 'action' => 'delete', 'facility' => $region->facilities[1]->id]);
	}

}
