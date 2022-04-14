<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\GameSlotFactory;
use App\Test\Scenario\DiverseFacilitiesScenario;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\Http\Client\Message;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\FieldsController Test Case
 */
class FieldsControllerTest extends ControllerTestCase {

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
	 * @throws \PHPUnit\Exception
	 */
	public function testIndex(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		// Anyone that gets the index gets redirected to facilities index
		$this->login($admin->id);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->login($manager->id);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->login($volunteer->id);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->login($player->id);
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);

		$this->logout();
		$this->get(['controller' => 'Fields', 'action' => 'index']);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'index']);
	}

	/**
	 * Test view method
	 * @throws \PHPUnit\Exception
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Anyone that gets the view gets redirected to facility view
		$this->login($admin->id);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => $region->facilities[0]->fields[0]->id]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id]);

		$this->login($manager->id);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => $region->facilities[0]->fields[0]->id]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id]);

		$this->login($volunteer->id);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => $region->facilities[0]->fields[0]->id]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id]);

		$this->login($player->id);
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => $region->facilities[0]->fields[0]->id]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id]);

		$this->logout();
		$this->get(['controller' => 'Fields', 'action' => 'view', 'field' => $region->facilities[0]->fields[0]->id]);
		$this->assertResponseCode(Message::STATUS_MOVED_PERMANENTLY);
		$this->assertRedirectEquals(['controller' => 'Facilities', 'action' => 'view', 'facility' => $region->facilities[0]->id]);
	}

	/**
	 * Test tooltip method
	 */
	public function testTooltip(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Anyone is allowed to view field tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => $region->facilities[0]->fields[0]->id],
			$admin->id);
		$this->assertResponseContains('/maps\\/view?field=' . $region->facilities[0]->fields[0]->id);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Fields', 'action' => 'tooltip', 'field' => 0],
			$admin->id, ['controller' => 'Facilities', 'action' => 'index'],
			'Invalid field.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => $region->facilities[0]->fields[0]->id],
			$manager->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => $region->facilities[0]->fields[0]->id],
			$volunteer->id);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => $region->facilities[0]->fields[0]->id],
			$player->id);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Fields', 'action' => 'tooltip', 'field' => $region->facilities[0]->fields[0]->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test open method as an admin
	 */
	public function testOpenAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Admins are allowed to open fields
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'open', 'field' => $region->facilities[0]->fields[3]->id],
			$admin->id);
		$this->assertResponseContains('/fields\\/close?field=' . $region->facilities[0]->fields[3]->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test open method as a manager
	 */
	public function testOpenAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$other_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Managers are allowed to open fields
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'open', 'field' => $region->facilities[0]->fields[3]->id],
			$manager->id);
		$this->assertResponseContains('/fields\\/close?field=' . $region->facilities[0]->fields[3]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => $other_region->facilities[0]->fields[3]->id],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test open method as others
	 */
	public function testOpenAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Others are not allowed to open fields
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => $region->facilities[0]->fields[3]->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => $region->facilities[0]->fields[3]->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Fields', 'action' => 'open', 'field' => $region->facilities[0]->fields[3]->id]);
	}

	/**
	 * Test close method as an admin
	 */
	public function testCloseAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Admins are allowed to close fields
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'close', 'field' => $region->facilities[0]->fields[0]->id],
			$admin->id);
		$this->assertResponseContains('/fields\\/open?field=' . $region->facilities[0]->fields[0]->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test close method as a manager
	 */
	public function testCloseAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$other_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Managers are allowed to close fields
		$this->assertGetAjaxAsAccessOk(['controller' => 'Fields', 'action' => 'close', 'field' => $region->facilities[0]->fields[0]->id],
			$manager->id);
		$this->assertResponseContains('/fields\\/open?field=' . $region->facilities[0]->fields[0]->id);

		// But not ones in other affiliates
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => $other_region->facilities[0]->fields[0]->id],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test close method as others
	 */
	public function testCloseAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Others are not allowed to close fields
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => $region->facilities[0]->fields[0]->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => $region->facilities[0]->fields[0]->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Fields', 'action' => 'close', 'field' => $region->facilities[0]->fields[0]->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Admins are allowed to delete fields
		$this->assertPostAsAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => $region->facilities[1]->fields[0]->id],
			$admin->id, [], ['controller' => 'Facilities', 'action' => 'index'],
			'The field has been deleted.');

		// But not the last field at a facility (field 2 will be last on this facility, now that field 1 is gone)
		$this->assertPostAsAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => $region->facilities[1]->fields[1]->id],
			$admin->id, [], ['controller' => 'Facilities', 'action' => 'index'],
			'You cannot delete the only field at a facility.');

		// And not ones with dependencies
		GameSlotFactory::make(['field_id' => $region->facilities[0]->fields[0]->id])->persist();
		$this->assertPostAsAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => $region->facilities[0]->fields[0]->id],
			$admin->id, [], ['controller' => 'Facilities', 'action' => 'index'],
			'#The following records reference this field, so it cannot be deleted#');

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$other_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);

		// Managers are allowed to delete fields in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Fields', 'action' => 'delete', 'field' => $region->facilities[1]->fields[0]->id],
			$manager->id, [], ['controller' => 'Facilities', 'action' => 'index'],
			'The field has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => $other_region->facilities[1]->fields[0]->id],
			$manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Others are not allowed to delete fields
		$this->assertPostAsAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => $region->facilities[1]->fields[0]->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => $region->facilities[1]->fields[0]->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Fields', 'action' => 'delete', 'field' => $region->facilities[1]->fields[0]->id]);
	}

	/**
	 * Test bookings method
	 */
	public function testBookings(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);

		// Anyone logged in is allowed to see the bookings list
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => $region->facilities[0]->fields[0]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => $region->facilities[0]->fields[0]->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => $region->facilities[0]->fields[0]->id], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Fields', 'action' => 'bookings', 'field' => $region->facilities[0]->fields[0]->id], $player->id);

		$this->assertGetAnonymousAccessDenied(['controller' => 'Fields', 'action' => 'bookings', 'field' => $region->facilities[0]->fields[0]->id]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

}
