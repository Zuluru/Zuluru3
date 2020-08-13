<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\TaskSlotsController Test Case
 */
class TaskSlotsControllerTest extends ControllerTestCase {

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
		'app.Plugins',
	];

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view task slots, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_ADMIN);
		$this->assertResponseContains('/task_slots/edit?slot=' . TASK_SLOT_ID_CAPTAINS_MEETING);
		$this->assertResponseContains('/task_slots/delete?slot=' . TASK_SLOT_ID_CAPTAINS_MEETING);

		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_POSTERS_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/task_slots/edit?slot=' . TASK_SLOT_ID_POSTERS_SUB);
		$this->assertResponseContains('/task_slots/delete?slot=' . TASK_SLOT_ID_POSTERS_SUB);

		// Managers are allowed to view task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_MANAGER);
		$this->assertResponseContains('/task_slots/edit?slot=' . TASK_SLOT_ID_CAPTAINS_MEETING);
		$this->assertResponseContains('/task_slots/delete?slot=' . TASK_SLOT_ID_CAPTAINS_MEETING);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_POSTERS_SUB], PERSON_ID_MANAGER);

		// Others are not allowed to view task slots
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING]);
	}

	/**
	 * Test ical method
	 *
	 * @return void
	 */
	public function testIcal() {
		$this->assertGetAnonymousAccessOk(['controller' => 'TaskSlots', 'action' => 'ical', TASK_SLOT_ID_CAPTAINS_MEETING]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'add', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'add', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add task slots
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'add', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'add', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'add', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'add', 'task' => TASK_ID_CAPTAINS_MEETING], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'add', 'task' => TASK_ID_CAPTAINS_MEETING]);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit task slots
		$this->assertGetAsAccessOk(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit task slots
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'edit', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete task slots
		$this->assertPostAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_ADMIN, [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task slot has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete task slots in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_MANAGER, [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task slot has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_POSTERS_SUB],
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

		// Others are not allowed to delete task slots
		$this->assertPostAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING]);
	}

	/**
	 * Test assign method as an admin
	 *
	 * @return void
	 */
	public function testAssignAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to assign task slots
		$this->assertPostAjaxAsAccessOk(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_ADMIN, ['person' => PERSON_ID_PLAYER]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as a manager
	 *
	 * @return void
	 */
	public function testAssignAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to assign task slots
		$this->assertPostAjaxAsAccessOk(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_MANAGER, ['person' => PERSON_ID_PLAYER]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as a others
	 *
	 * @return void
	 */
	public function testAssignAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to assign task slots
		$this->assertPostAjaxAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_COORDINATOR, ['person' => PERSON_ID_PLAYER], ['controller' => 'Tasks', 'action' => 'index'], 'Invalid task slot.');
		$this->assertPostAjaxAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_CAPTAIN, ['person' => PERSON_ID_PLAYER], ['controller' => 'Tasks', 'action' => 'index'], 'Invalid task slot.');
		$this->assertPostAjaxAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_PLAYER, ['person' => PERSON_ID_PLAYER], ['controller' => 'Tasks', 'action' => 'index'], 'Invalid task slot.');
		$this->assertPostAjaxAsAccessRedirect(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_VISITOR, ['person' => PERSON_ID_PLAYER], ['controller' => 'Tasks', 'action' => 'index'], 'Invalid task slot.');
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'assign', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			['person' => PERSON_ID_PLAYER]);
	}

	/**
	 * Test approve method as an admin
	 *
	 * @return void
	 */
	public function testApproveAsAdmin() {
		// Admins are allowed to approve task slots
		$this->assertGetAjaxAsAccessOk(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING_UNAPPROVED],
			PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a manager
	 *
	 * @return void
	 */
	public function testApproveAsManager() {
		// Managers are allowed to approve task slots
		$this->assertGetAjaxAsAccessOk(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING_UNAPPROVED],
			PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as others
	 *
	 * @return void
	 */
	public function testApproveAsOthers() {
		// Others are not allowed to approve task slots
		$this->assertGetAjaxAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING_UNAPPROVED],
			PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING_UNAPPROVED],
			PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING_UNAPPROVED],
			PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING_UNAPPROVED],
			PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'TaskSlots', 'action' => 'approve', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING_UNAPPROVED]);
	}

}
