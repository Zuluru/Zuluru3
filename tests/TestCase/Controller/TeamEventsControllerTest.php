<?php
namespace App\Test\TestCase\Controller;

use Cake\I18n\FrozenTime;

/**
 * App\Controller\TeamEventsController Test Case
 */
class TeamEventsControllerTest extends ControllerTestCase {

	/**
	 * Test view method
	 */
	public function testView(): void {
		// Admins are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_ADMIN);

		// Managers are allowed to view
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_MANAGER);

		// Captains from the team in question are allowed to view their team's events, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/team_events/edit?event=' . TEAM_EVENT_ID_RED_PRACTICE);
		$this->assertResponseContains('/team_events/delete?event=' . TEAM_EVENT_ID_RED_PRACTICE);

		// But not other team's events
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_BEARS_PRACTICE], PERSON_ID_CAPTAIN);

		// Players are allowed to view their team's events, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/team_events/edit?event=' . TEAM_EVENT_ID_RED_PRACTICE);
		$this->assertResponseNotContains('/team_events/delete?event=' . TEAM_EVENT_ID_RED_PRACTICE);

		// Others are not allowed to view
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_RED_PRACTICE]);
	}

	/**
	 * Test add method as an admin
	 */
	public function testAddAsAdmin(): void {
		// Admins are allowed to add events
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'add', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a manager
	 */
	public function testAddAsManager(): void {
		// Managers are allowed to add events
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'add', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as a captain
	 */
	public function testAddAsCaptain(): void {
		// Captains are allowed to add events to their own teams
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'add', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'add', 'team' => TEAM_ID_BLUE], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as others
	 */
	public function testAddAsOthers(): void {
		// Others are not allowed to add events
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'add', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'add', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'add', 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'add', 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test edit method as an admin
	 */
	public function testEditAsAdmin(): void {
		// Admins are allowed to edit team events
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 */
	public function testEditAsManager(): void {
		// Managers are allowed to edit team events in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => TEAM_EVENT_ID_BEARS_PRACTICE], PERSON_ID_MANAGER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a captain
	 */
	public function testEditAsCaptain(): void {
		// Captains are allowed to edit their own team's events
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 */
	public function testEditAsOthers(): void {
		// Others are not allowed to edit
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'edit', 'event' => TEAM_EVENT_ID_RED_PRACTICE]);
	}

	/**
	 * Test delete method as an admin
	 */
	public function testDeleteAsAdmin(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete team events
		$this->assertPostAsAccessRedirect(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_RED_PRACTICE],
			PERSON_ID_ADMIN, [], '/',
			'The team event has been deleted.');
	}

	/**
	 * Test delete method as a manager
	 */
	public function testDeleteAsManager(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete team events in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_RED_PRACTICE],
			PERSON_ID_MANAGER, [], '/',
			'The team event has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_BEARS_PRACTICE],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as a captain
	 */
	public function testDeleteAsCaptain(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Captains are allowed to delete their team's events
		$this->assertPostAsAccessRedirect(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_RED_PRACTICE],
			PERSON_ID_CAPTAIN, [], '/',
			'The team event has been deleted.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete method as others
	 */
	public function testDeleteAsOthers(): void {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete team events
		$this->assertPostAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_RED_PRACTICE],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_RED_PRACTICE],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_RED_PRACTICE],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_RED_PRACTICE]);
	}

	/**
	 * Test attendance_change method as an admin
	 */
	public function testAttendanceChangeAsAdmin(): void {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Admins are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE, 'person' => PERSON_ID_CAPTAIN], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a manager
	 */
	public function testAttendanceChangeAsManager(): void {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Managers are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE, 'person' => PERSON_ID_CAPTAIN], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a coordinator
	 */
	public function testAttendanceChangeAsCoordinator(): void {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Coordinators are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE, 'person' => PERSON_ID_CAPTAIN], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a captain
	 */
	public function testAttendanceChangeAsCaptain(): void {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Captains are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a player
	 */
	public function testAttendanceChangeAsPlayer(): void {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Players are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_CAPTAIN3);

		// But not for teams they're only just invited to, or not on at all
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_PLAYER);

		// And not for long after the event
		FrozenTime::setTestNow((new FrozenTime('last Friday of July'))->addDays(15));
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_CAPTAIN3);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as others
	 */
	public function testAttendanceChangeAsOthers(): void {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Others are not allowed to change attendance
		$this->assertGetAsAccessDenied(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => TEAM_EVENT_ID_RED_PRACTICE]);
	}

}
