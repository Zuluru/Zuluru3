<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\TaskFactory;
use App\Test\Factory\TaskSlotFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\TaskSlotsController Test Case
 */
class TaskSlotsControllerTest extends ControllerTestCase {

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
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			)
			->persist();

		$other_slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $affiliates[1]->id])
			)
			->persist();

		// Admins are allowed to view task slots, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => $slot->id], $admin->id);
		$this->assertResponseContains('/task_slots/edit?slot=' . $slot->id);
		$this->assertResponseContains('/task_slots/delete?slot=' . $slot->id);

		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => $other_slot->id], $admin->id);
		$this->assertResponseContains('/task_slots/edit?slot=' . $other_slot->id);
		$this->assertResponseContains('/task_slots/delete?slot=' . $other_slot->id);

		// Managers are allowed to view task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => $slot->id], $manager->id);
		$this->assertResponseContains('/task_slots/edit?slot=' . $slot->id);
		$this->assertResponseContains('/task_slots/delete?slot=' . $slot->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => $other_slot->id], $manager->id);

		// Others are not allowed to view task slots
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => $slot->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => $slot->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => $slot->id]);
	}

	/**
	 * Test ical method
	 */
	public function testIcal(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make(['approved' => true])
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		$this->assertGetAnonymousAccessOk(['controller' => 'TaskSlots', 'action' => 'ical', '_ext' => 'ics', $slot->id]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$task = TaskFactory::make([
			'person_id' => $admin->id,
			'allow_signup' => true,
		])
			->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Admins are allowed to add task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'add', 'task' => $task->id], $admin->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$task = TaskFactory::make([
			'person_id' => $admin->id,
			'allow_signup' => true,
		])
			->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Managers are allowed to add task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'add', 'task' => $task->id], $manager->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$task = TaskFactory::make([
			'person_id' => $admin->id,
			'allow_signup' => true,
		])
			->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Others are not allowed to add task slots
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'add', 'task' => $task->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'add', 'task' => $task->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'add', 'task' => $task->id]);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Admins are allowed to edit task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => $slot->id], $admin->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Managers are allowed to edit task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => $slot->id], $manager->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Others are not allowed to edit task slots
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => $slot->id], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => $slot->id], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => $slot->id]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Admins are allowed to delete task slots
		$this->assertPostAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => $slot->id],
			$admin->id, [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task slot has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			)
			->persist();

		$other_slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $affiliates[1]->id])
			)
			->persist();

		// Managers are allowed to delete task slots in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => $slot->id],
			$manager->id, [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task slot has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => $other_slot->id],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Others are not allowed to delete task slots
		$this->assertPostAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => $slot->id],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => $slot->id],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => $slot->id]);
	}

	/**
	 * Test assign method as an admin
	 */
	public function testAssignAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Admins are allowed to assign task slots
		$this->assertPostAjaxAsAccessOk(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => $slot->id],
			$admin->id, ['person' => $player->id]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as a manager
	 */
	public function testAssignAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Managers are allowed to assign task slots
		$this->assertPostAjaxAsAccessOk(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => $slot->id],
			$manager->id, ['person' => $player->id]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as others
	 */
	public function testAssignAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make()
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Others are not allowed to assign task slots
		$this->assertPostAjaxAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => $slot->id],
			$volunteer->id, ['person' => $player->id], ['controller' => 'Tasks', 'action' => 'index'], 'Invalid task slot.');
		$this->assertPostAjaxAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => $slot->id],
			$player->id, ['person' => $player->id], ['controller' => 'Tasks', 'action' => 'index'], 'Invalid task slot.');
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => $slot->id],
			['person' => $player->id]);
	}

	/**
	 * Test approve method as an admin
	 */
	public function testApproveAsAdmin(): void {
		[$admin, , , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make(['person_id' => $player->id])
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Admins are allowed to approve task slots
		$this->assertGetAjaxAsAccessOk(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => $slot->id],
			$admin->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a manager
	 */
	public function testApproveAsManager(): void {
		[$admin, $manager, , $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make(['person_id' => $player->id])
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Managers are allowed to approve task slots
		$this->assertGetAjaxAsAccessOk(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => $slot->id],
			$manager->id);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as others
	 */
	public function testApproveAsOthers(): void {
		[$admin, , $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);

		$slot = TaskSlotFactory::make(['person_id' => $player->id])
			->with('Tasks',
				TaskFactory::make([
					'person_id' => $admin->id
				])->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			)
			->persist();

		// Others are not allowed to approve task slots
		$this->assertGetAjaxAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => $slot->id],
			$volunteer->id);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => $slot->id],
			$player->id);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => $slot->id]);
	}

}
