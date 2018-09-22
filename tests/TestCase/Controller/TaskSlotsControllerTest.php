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
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
			'app.categories',
				'app.tasks',
					'app.task_slots',
			'app.settings',
	];

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view task slots, with full edit permissions
		$this->assertAccessOk(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/task_slots/edit\?slot=' . TASK_SLOT_ID_CAPTAINS_MEETING . '#ms');
		$this->assertResponseRegExp('#/task_slots/delete\?slot=' . TASK_SLOT_ID_CAPTAINS_MEETING . '#ms');

		$this->assertAccessOk(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_POSTERS_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/task_slots/edit\?slot=' . TASK_SLOT_ID_POSTERS_SUB . '#ms');
		$this->assertResponseRegExp('#/task_slots/delete\?slot=' . TASK_SLOT_ID_POSTERS_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view task slots
		$this->assertAccessOk(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/task_slots/edit\?slot=' . TASK_SLOT_ID_CAPTAINS_MEETING . '#ms');
		$this->assertResponseRegExp('#/task_slots/delete\?slot=' . TASK_SLOT_ID_CAPTAINS_MEETING . '#ms');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_POSTERS_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as a captain
	 *
	 * @return void
	 */
	public function testViewAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		// Others are not allowed to view task slots
		$this->assertAccessRedirect(['controller' => 'TaskSlots', 'action' => 'view', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING], PERSON_ID_PLAYER);
	}

	/**
	 * Test view method as someone else
	 *
	 * @return void
	 */
	public function testViewAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method without being logged in
	 *
	 * @return void
	 */
	public function testViewAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as an admin
	 *
	 * @return void
	 */
	public function testIcalAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as a manager
	 *
	 * @return void
	 */
	public function testIcalAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as a coordinator
	 *
	 * @return void
	 */
	public function testIcalAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as a captain
	 *
	 * @return void
	 */
	public function testIcalAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as a player
	 *
	 * @return void
	 */
	public function testIcalAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method as someone else
	 *
	 * @return void
	 */
	public function testIcalAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method without being logged in
	 *
	 * @return void
	 */
	public function testIcalAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a captain
	 *
	 * @return void
	 */
	public function testAddAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a player
	 *
	 * @return void
	 */
	public function testAddAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as someone else
	 *
	 * @return void
	 */
	public function testAddAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method without being logged in
	 *
	 * @return void
	 */
	public function testAddAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a captain
	 *
	 * @return void
	 */
	public function testEditAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a player
	 *
	 * @return void
	 */
	public function testEditAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as someone else
	 *
	 * @return void
	 */
	public function testEditAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method without being logged in
	 *
	 * @return void
	 */
	public function testEditAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
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
		$this->assertAccessRedirect(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task slot has been deleted.', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete task slots in their affiliate
		$this->assertAccessRedirect(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_CAPTAINS_MEETING],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Tasks', 'action' => 'index'],
			'The task slot has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'TaskSlots', 'action' => 'delete', 'slot' => TASK_SLOT_ID_POSTERS_SUB],
			PERSON_ID_MANAGER, 'post');
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a captain
	 *
	 * @return void
	 */
	public function testDeleteAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as a player
	 *
	 * @return void
	 */
	public function testDeleteAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as someone else
	 *
	 * @return void
	 */
	public function testDeleteAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as an admin
	 *
	 * @return void
	 */
	public function testAssignAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as a manager
	 *
	 * @return void
	 */
	public function testAssignAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as a coordinator
	 *
	 * @return void
	 */
	public function testAssignAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as a captain
	 *
	 * @return void
	 */
	public function testAssignAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as a player
	 *
	 * @return void
	 */
	public function testAssignAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method as someone else
	 *
	 * @return void
	 */
	public function testAssignAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test assign method without being logged in
	 *
	 * @return void
	 */
	public function testAssignAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as an admin
	 *
	 * @return void
	 */
	public function testApproveAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a manager
	 *
	 * @return void
	 */
	public function testApproveAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a coordinator
	 *
	 * @return void
	 */
	public function testApproveAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a captain
	 *
	 * @return void
	 */
	public function testApproveAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as a player
	 *
	 * @return void
	 */
	public function testApproveAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method as someone else
	 *
	 * @return void
	 */
	public function testApproveAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve method without being logged in
	 *
	 * @return void
	 */
	public function testApproveAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
