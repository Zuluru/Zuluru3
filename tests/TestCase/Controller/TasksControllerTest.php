<?php
namespace App\Test\TestCase\Controller;

use App\Test\Factory\TaskFactory;
use App\Test\Scenario\DiverseUsersScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Controller\TasksController Test Case
 */
class TasksControllerTest extends ControllerTestCase {

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

		$task = TaskFactory::make([
			'person_id' => $admin->id,
			'allow_signup' => true,
		])
			->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		$assigned_task = TaskFactory::make([
			'person_id' => $admin->id,
		])
			->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		$affiliate_task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'index'], $admin->id);
		$this->assertResponseContains('/tasks/view?task=' . $task->id);
		$this->assertResponseContains('/tasks/edit?task=' . $task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $task->id);
		$this->assertResponseContains('/tasks/view?task=' . $assigned_task->id);
		$this->assertResponseContains('/tasks/edit?task=' . $assigned_task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $assigned_task->id);
		$this->assertResponseContains('/tasks/view?task=' . $affiliate_task->id);
		$this->assertResponseContains('/tasks/edit?task=' . $affiliate_task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $affiliate_task->id);

		// Managers are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'index'], $manager->id);
		$this->assertResponseContains('/tasks/view?task=' . $task->id);
		$this->assertResponseContains('/tasks/edit?task=' . $task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $task->id);
		$this->assertResponseContains('/tasks/view?task=' . $assigned_task->id);
		$this->assertResponseContains('/tasks/edit?task=' . $assigned_task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $assigned_task->id);
		$this->assertResponseNotContains('/tasks/view?task=' . $affiliate_task->id);
		$this->assertResponseNotContains('/tasks/edit?task=' . $affiliate_task->id);
		$this->assertResponseNotContains('/tasks/delete?task=' . $affiliate_task->id);

		// Volunteers are allowed to see the index, but only view options, and only signup tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'index'], $volunteer->id);
		$this->assertResponseContains('/tasks/view?task=' . $task->id);
		$this->assertResponseNotContains('/tasks/edit?task=' . $task->id);
		$this->assertResponseNotContains('/tasks/delete?task=' . $task->id);
		$this->assertResponseNotContains('/tasks/view?task=' . $assigned_task->id);
		$this->assertResponseNotContains('/tasks/edit?task=' . $assigned_task->id);
		$this->assertResponseNotContains('/tasks/delete?task=' . $assigned_task->id);
		$this->assertResponseNotContains('/tasks/view?task=' . $affiliate_task->id);
		$this->assertResponseNotContains('/tasks/edit?task=' . $affiliate_task->id);
		$this->assertResponseNotContains('/tasks/delete?task=' . $affiliate_task->id);

		// Others are not allowed to see the index
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'index'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Tasks', 'action' => 'index']);
	}

	/**
	 * Test view method
	 */
	public function testView(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliates = $admin->affiliates;

		$task = TaskFactory::make([
			'person_id' => $admin->id,
			'allow_signup' => true,
		])
			->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		$assigned_task = TaskFactory::make([
			'person_id' => $admin->id,
		])
			->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		$affiliate_task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Admins are allowed to view tasks, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task->id]], $admin->id);
		$this->assertResponseContains('/tasks/edit?task=' . $task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $task->id);

		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $assigned_task->id]], $admin->id);
		$this->assertResponseContains('/tasks/edit?task=' . $assigned_task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $assigned_task->id);

		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $affiliate_task->id]], $admin->id);
		$this->assertResponseContains('/tasks/edit?task=' . $affiliate_task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $affiliate_task->id);

		// Managers are allowed to view tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task->id]], $manager->id);
		$this->assertResponseContains('/tasks/edit?task=' . $task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $task->id);

		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $assigned_task->id]], $manager->id);
		$this->assertResponseContains('/tasks/edit?task=' . $assigned_task->id);
		$this->assertResponseContains('/tasks/delete?task=' . $assigned_task->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessRedirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $affiliate_task->id]],
			$manager->id, ['controller' => 'Tasks', 'action' => 'index'],
			'Invalid task.');

		// Volunteers are allowed to view tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task->id]], $volunteer->id);

		// But not assigned ones
		$this->assertGetAsAccessRedirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $assigned_task->id]],
			$volunteer->id, ['controller' => 'Tasks', 'action' => 'index'],
			'Invalid task.');

		// Others are not allowed to view tasks
		$this->assertGetAsAccessRedirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task->id]],
			$player->id, ['controller' => 'Tasks', 'action' => 'index'],
			'Invalid task.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task->id]]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to add tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'add'], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to add tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'add'], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		[$volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer', 'player']);

		// Others are not allowed to add tasks
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'add'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'add'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Tasks', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		$affiliate_task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Admins are allowed to edit tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'edit', '?' => ['task' => $task->id]], $admin->id);
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'edit', '?' => ['task' => $affiliate_task->id]], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		$affiliate_task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Managers are allowed to edit tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'edit', '?' => ['task' => $task->id]], $manager->id);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'edit', '?' => ['task' => $affiliate_task->id]], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);
		$affiliates = $admin->affiliates;

		$task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		// Others are not allowed to edit tasks
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'edit', '?' => ['task' => $task->id]], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'edit', '?' => ['task' => $task->id]], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Tasks', 'action' => 'edit', '?' => ['task' => $task->id]]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableSecurityToken();

		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);
		$affiliates = $admin->affiliates;

		$task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		$dependent_task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[1]->id])
			->with('TaskSlots[2]')
			->persist();

		// Admins are allowed to delete tasks
		$this->assertPostAsAccessRedirect(['controller' => 'Tasks', 'action' => 'delete', '?' => ['task' => $task->id]],
			$admin->id, [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Tasks', 'action' => 'delete', '?' => ['task' => $dependent_task->id]],
			$admin->id, [], ['controller' => 'Tasks', 'action' => 'index'],
			'#The following records reference this task, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableSecurityToken();

		[$admin, $manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'manager']);
		$affiliates = $admin->affiliates;

		$task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[0]->id])
			->persist();

		$affiliate_task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $affiliates[1]->id])
			->persist();

		// Managers are allowed to delete tasks in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Tasks', 'action' => 'delete', '?' => ['task' => $task->id]],
			$manager->id, [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Tasks', 'action' => 'delete', '?' => ['task' => $affiliate_task->id]],
			$manager->id);
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableSecurityToken();

		[$admin, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer', 'player']);

		$task = TaskFactory::make([
			'person_id' => $admin->id
		])
			->with('Categories', ['affiliate_id' => $admin->affiliates[0]->id])
			->persist();

		// Others are not allowed to delete tasks
		$this->assertPostAsAccessDenied(['controller' => 'Tasks', 'action' => 'delete', '?' => ['task' => $task->id]],
			$volunteer->id);
		$this->assertPostAsAccessDenied(['controller' => 'Tasks', 'action' => 'delete', '?' => ['task' => $task->id]],
			$player->id);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Tasks', 'action' => 'delete', '?' => ['task' => $task->id]]);
	}

}
