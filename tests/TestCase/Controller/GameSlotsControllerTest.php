<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\FieldFactory;
use App\Test\Factory\GameSlotFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\GameSlotsController Test Case
 */
class GameSlotsControllerTest extends ControllerTestCase {

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
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();
		$affiliate_slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Admins are allowed to view game slots, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'view', '?' => ['slot' => $slot->id]], $admin->id);
		$this->assertResponseContains('/game_slots/edit?slot=' . $slot->id);
		$this->assertResponseContains('/game_slots/delete?slot=' . $slot->id);

		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'view', '?' => ['slot' => $affiliate_slot->id]], $admin->id);
		$this->assertResponseContains('/game_slots/edit?slot=' . $affiliate_slot->id);
		$this->assertResponseContains('/game_slots/delete?slot=' . $affiliate_slot->id);

		// Managers are allowed to view game slots
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'view', '?' => ['slot' => $slot->id]], $manager->id);
		$this->assertResponseContains('/game_slots/edit?slot=' . $slot->id);
		$this->assertResponseContains('/game_slots/delete?slot=' . $slot->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'view', '?' => ['slot' => $affiliate_slot->id]], $manager->id);

		// Others are not allowed to view game slots
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'view', '?' => ['slot' => $slot->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'view', '?' => ['slot' => $slot->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'view', '?' => ['slot' => $slot->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$field = FieldFactory::make()
			->with('Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		// Admins are allowed to add
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'add', '?' => ['field' => $field->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'add', '?' => ['affiliate' => $affiliates[0]->id]], $admin->id);
		// TODO: Test with affiliates turned off and no affiliate ID in the URL
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$field = FieldFactory::make()
			->with('Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		// Managers are allowed to add
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'add', '?' => ['field' => $field->id]], $manager->id);
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'add', '?' => ['affiliate' => $affiliates[0]->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$field = FieldFactory::make()
			->with('Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		// Others are not allowed to add
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', '?' => ['field' => $field->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', '?' => ['affiliate' => $affiliates[0]->id]], $volunteer->id);

		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', '?' => ['field' => $field->id]], $player->id);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'add', '?' => ['affiliate' => $affiliates[0]->id]], $player->id);

		$this->assertGetAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'add', '?' => ['field' => $field->id]]);
		$this->assertGetAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'add', '?' => ['affiliate' => $affiliates[0]->id]]);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();
		$affiliate_slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Admins are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'edit', '?' => ['slot' => $slot->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'edit', '?' => ['slot' => $affiliate_slot->id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();
		$affiliate_slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Managers are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'GameSlots', 'action' => 'edit', '?' => ['slot' => $slot->id]], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'edit', '?' => ['slot' => $affiliate_slot->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		// Others are not allowed to edit
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'edit', '?' => ['slot' => $slot->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'GameSlots', 'action' => 'edit', '?' => ['slot' => $slot->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'edit', '?' => ['slot' => $slot->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();
		$affiliate_slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[1]->id])
			->persist();
		$slot_with_game = GameSlotFactory::make(['assigned' => true])
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		// Admins are allowed to delete game slots
		$this->assertPostAsAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', '?' => ['slot' => $slot->id]],
			$admin->id, [], '/',
			'The game slot has been deleted.');
		$this->assertPostAsAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', '?' => ['slot' => $affiliate_slot->id]],
			$admin->id, [], '/',
			'The game slot has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', '?' => ['slot' => $slot_with_game->id]],
			$admin->id, [], '/',
			'This game slot has a game assigned to it and cannot be deleted.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();
		$affiliate_slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Managers are allowed to delete game slots in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'GameSlots', 'action' => 'delete', '?' => ['slot' => $slot->id]],
			$manager->id, [], '/',
			'The game slot has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', '?' => ['slot' => $affiliate_slot->id]],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$slot = GameSlotFactory::make()
			->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		// Others are not allowed to delete game slots
		$this->assertPostAsAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', '?' => ['slot' => $slot->id]],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', '?' => ['slot' => $slot->id]],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'GameSlots', 'action' => 'delete', '?' => ['slot' => $slot->id]]);
	}

	/**
	 * Test submit method as an admin
	 */
	public function testSubmitScoreAsAdmin(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test submit method as a manager
	 */
	public function testSubmitScoreAsManager(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test submit method as a coordinator
	 */
	public function testSubmitScoreAsCoordinator(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test submit method as a captain
	 */
	public function testSubmitScoreAsCaptain(): void {
	}

	/**
	 * Test submit method as an official
	 */
	public function testSubmitScoreAsOfficial(): void {
	}

	/**
	 * Test submit method as others
	 */
	public function testSubmitScoreAsOthers(): void {
		$this->markTestIncomplete('Operation not implemented yet.');
	}

}
