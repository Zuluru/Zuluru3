<?php
namespace App\Test\TestCase\Controller;

use App\Test\Scenario\DiverseFacilitiesScenario;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\MapsController Test Case
 */
class MapsControllerTest extends ControllerTestCase {

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

		// Anyone is allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], $volunteer->id);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessOk(['controller' => 'Maps', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$fields = $region->facilities[0]->fields;

		$affiliate_region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[1]]);
		$affiliate_fields = $affiliate_region->facilities[0]->fields;

		// Anyone is allowed to view maps
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => $fields[0]->id], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => $fields[0]->id], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => $fields[0]->id], $volunteer->id);

		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => $fields[0]->id], $player->id);
		$this->assertResponseContains('fields[' . $fields[0]->id . '] = {');
		$this->assertResponseContains('fields[' . $fields[1]->id . '] = {');
		$this->assertResponseNotContains('fields[' . $fields[2]->id . '] = {');
		$this->assertResponseNotContains('fields[' . $fields[3]->id . '] = {');

		// From any affiliate
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => $affiliate_fields[0]->id], $player->id);

		// But not maps that haven't been created yet
		$this->assertGetAsAccessRedirect(['controller' => 'Maps', 'action' => 'view', 'field' => $fields[2]->id],
			$player->id, ['controller' => 'Facilities', 'action' => 'index'],
			'That field has not yet been laid out.');

		// When viewing closed fields, we get shown all fields at that facility, not just open ones
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => $fields[3]->id], $player->id);
		$this->assertResponseContains('fields[' . $fields[0]->id . '] = {');
		$this->assertResponseContains('fields[' . $fields[1]->id . '] = {');
		$this->assertResponseNotContains('fields[' . $fields[2]->id . '] = {');
		$this->assertResponseContains('fields[' . $fields[3]->id . '] = {');

		$this->assertGetAnonymousAccessOk(['controller' => 'Maps', 'action' => 'view', 'field' => $fields[0]->id]);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$fields = $region->facilities[0]->fields;

		// Admins are allowed to edit maps
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'edit', 'field' => $fields[0]->id], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$fields = $region->facilities[0]->fields;

		// Managers are allowed to edit maps
		$this->assertGetAsAccessOk(['controller' => 'Maps', 'action' => 'edit', 'field' => $fields[0]->id], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$region = $this->loadFixtureScenario(DiverseFacilitiesScenario::class, ['affiliate' => $affiliates[0]]);
		$fields = $region->facilities[0]->fields;

		// Others are not allowed to edit maps
		$this->assertGetAsAccessDenied(['controller' => 'Maps', 'action' => 'edit', 'field' => $fields[0]->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Maps', 'action' => 'edit', 'field' => $fields[0]->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Maps', 'action' => 'edit', 'field' => $fields[0]->id]);
	}

}
