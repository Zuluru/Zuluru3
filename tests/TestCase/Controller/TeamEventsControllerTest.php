<?php
namespace App\Test\TestCase\Controller;

/**
 * App\Controller\TeamEventsController Test Case
 */
class TeamEventsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
			'app.groups',
				'app.groups_people',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_people',
						'app.team_events',
					'app.divisions_days',
					'app.pools',
						'app.pools_teams',
					'app.games',
			'app.attendances',
			'app.franchises',
				'app.franchises_people',
				'app.franchises_teams',
			'app.events',
				'app.prices',
					'app.registrations',
			'app.mailing_lists',
				'app.newsletters',
			'app.activity_logs',
			'app.settings',
	];

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
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
		// Captains from the team in question are allowed to view their team's events, with full edit permissions
		$this->assertAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#/team_events/edit\?event=' . TEAM_EVENT_ID_RED_PRACTICE . '#ms');
		$this->assertResponseRegExp('#/team_events/delete\?event=' . TEAM_EVENT_ID_RED_PRACTICE . '#ms');

		// But not other team's events
		$this->assertAccessRedirect(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_BEARS_PRACTICE], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		// Players are allowed to view their team's events, but have no edit permissions
		$this->assertAccessOk(['controller' => 'TeamEvents', 'action' => 'view', 'event' => TEAM_EVENT_ID_RED_PRACTICE], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#/team_events/edit\?event=' . TEAM_EVENT_ID_RED_PRACTICE . '#ms');
		$this->assertResponseNotRegExp('#/team_events/delete\?event=' . TEAM_EVENT_ID_RED_PRACTICE . '#ms');
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

		// Admins are allowed to delete team events
		$this->assertAccessRedirect(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_RED_PRACTICE],
			PERSON_ID_ADMIN, 'post', [], null,
			'The team event has been deleted.', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete team events in their affiliate
		$this->assertAccessRedirect(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_RED_PRACTICE],
			PERSON_ID_MANAGER, 'post', [], null,
			'The team event has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'TeamEvents', 'action' => 'delete', 'event' => TEAM_EVENT_ID_BEARS_PRACTICE],
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
	 * Test attendance_change method as an admin
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a manager
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a coordinator
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a captain
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a player
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as someone else
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method without being logged in
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _makeHash method
	 *
	 * @return void
	 */
	public function testMakeHash() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _checkHash method
	 *
	 * @return void
	 */
	public function testCheckHash() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
