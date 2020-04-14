<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\TasksController Test Case
 */
class TasksControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
					'app.PeoplePeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.DivisionsPeople',
			'app.Categories',
				'app.Tasks',
					'app.TaskSlots',
			'app.Settings',
		'app.I18n',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		// Admins are allowed to view tasks, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_ADMIN);
		$this->assertResponseContains('/tasks/edit?task=' . TASK_ID_CAPTAINS_MEETING);
		$this->assertResponseContains('/tasks/delete?task=' . TASK_ID_CAPTAINS_MEETING);

		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_POSTERS_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/tasks/edit?task=' . TASK_ID_POSTERS_SUB);
		$this->assertResponseContains('/tasks/delete?task=' . TASK_ID_POSTERS_SUB);

		// Managers are allowed to view tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_MANAGER);
		$this->assertResponseContains('/tasks/edit?task=' . TASK_ID_CAPTAINS_MEETING);
		$this->assertResponseContains('/tasks/delete?task=' . TASK_ID_CAPTAINS_MEETING);

		// But not ones in other affiliates
		$this->assertGetAsAccessRedirect(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_POSTERS_SUB],
			PERSON_ID_MANAGER, ['controller' => 'Tasks', 'action' => 'index'],
			'Invalid task.');

		// Coordinators are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');

		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Tasks', 'action' => 'index']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_ADMIN);

		// Managers are allowed to view tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_MANAGER);

		// Volunteers are allowed to view tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_COORDINATOR);

		// Others are not allowed to view tasks
		$this->assertGetAsAccessRedirect(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_CAPTAINS_MEETING],
			PERSON_ID_CAPTAIN, ['controller' => 'Tasks', 'action' => 'index'],
			'Invalid task.');
		$this->assertGetAsAccessRedirect(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_CAPTAINS_MEETING],
			PERSON_ID_PLAYER, ['controller' => 'Tasks', 'action' => 'index'],
			'Invalid task.');
		$this->assertGetAsAccessRedirect(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_CAPTAINS_MEETING],
			PERSON_ID_VISITOR, ['controller' => 'Tasks', 'action' => 'index'],
			'Invalid task.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Tasks', 'action' => 'view', 'task' => TASK_ID_CAPTAINS_MEETING]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add tasks
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Tasks', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'edit', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit tasks
		$this->assertGetAsAccessOk(['controller' => 'Tasks', 'action' => 'edit', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit tasks
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'edit', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'edit', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'edit', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Tasks', 'action' => 'edit', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Tasks', 'action' => 'edit', 'task' => TASK_ID_CAPTAINS_MEETING]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete tasks
		$this->assertPostAsAccessRedirect(['controller' => 'Tasks', 'action' => 'delete', 'task' => TASK_ID_PLAYOFFS_SETUP],
			PERSON_ID_ADMIN, [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Tasks', 'action' => 'delete', 'task' => TASK_ID_CAPTAINS_MEETING],
			PERSON_ID_ADMIN, [], ['controller' => 'Tasks', 'action' => 'index'],
			'#The following records reference this task, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete tasks in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Tasks', 'action' => 'delete', 'task' => TASK_ID_PLAYOFFS_SETUP],
			PERSON_ID_MANAGER, [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Tasks', 'action' => 'delete', 'task' => TASK_ID_POSTERS_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete tasks
		$this->assertPostAsAccessDenied(['controller' => 'Tasks', 'action' => 'delete', 'task' => TASK_ID_PLAYOFFS_SETUP],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Tasks', 'action' => 'delete', 'task' => TASK_ID_PLAYOFFS_SETUP],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Tasks', 'action' => 'delete', 'task' => TASK_ID_PLAYOFFS_SETUP],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Tasks', 'action' => 'delete', 'task' => TASK_ID_PLAYOFFS_SETUP],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Tasks', 'action' => 'delete', 'task' => TASK_ID_PLAYOFFS_SETUP]);
	}

}
