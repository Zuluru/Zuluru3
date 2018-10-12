<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;

/**
 * App\Controller\DivisionsController Test Case
 */
class DivisionsControllerTest extends ControllerTestCase {

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
					'app.skills',
			'app.groups',
				'app.groups_people',
			'app.upload_types',
				'app.uploads',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_people',
					'app.divisions_days',
					'app.game_slots',
						'app.divisions_gameslots',
					'app.divisions_people',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.score_entries',
						'app.spirit_entries',
						'app.stats',
				'app.leagues_stat_types',
			'app.franchises',
				'app.franchises_teams',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
				'app.preregistrations',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
	];

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		// Everyone is allowed to view the index; admins, managers and coordinators have extra options
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/divisions/edit\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions/delete\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/divisions/edit\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions/delete\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertResponseRegExp('#/divisions/edit\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseNotRegExp('#/divisions/delete\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#/divisions/edit\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseNotRegExp('#/divisions/delete\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
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
	 * Test tooltip method as an admin
	 *
	 * @return void
	 */
	public function testTooltipAsAdmin() {
		// Everyone is allowed to view division tooltips
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');

		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => 0],
			PERSON_ID_ADMIN, 'getajax', [], ['controller' => 'Leagues', 'action' => 'index'],
			'Invalid division.');
	}

	/**
	 * Test tooltip method as a manager
	 *
	 * @return void
	 */
	public function testTooltipAsManager() {
		// Everyone is allowed to view division tooltips
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
	}

	/**
	 * Test tooltip method as a coordinator
	 *
	 * @return void
	 */
	public function testTooltipAsCoordinator() {
		// Everyone is allowed to view division tooltips
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR, 'getajax');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
	}

	/**
	 * Test tooltip method as a captain
	 *
	 * @return void
	 */
	public function testTooltipAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method as a player
	 *
	 * @return void
	 */
	public function testTooltipAsPlayer() {
		// Everyone is allowed to view division tooltips
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER, 'getajax');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
	}

	/**
	 * Test tooltip method as someone else
	 *
	 * @return void
	 */
	public function testTooltipAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method without being logged in
	 *
	 * @return void
	 */
	public function testTooltipAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stats method as an admin
	 *
	 * @return void
	 */
	public function testStatsAsAdmin() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		// Everyone is allowed to view the stats; admins, managers and coordinators have extra options
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/divisions/edit\?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN . '#ms');
		$this->assertResponseRegExp('#/divisions/delete\?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN . '#ms');
	}

	/**
	 * Test stats method as a manager
	 *
	 * @return void
	 */
	public function testStatsAsManager() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		// Everyone is allowed to view the stats; admins, managers and coordinators have extra options
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/divisions/edit\?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN . '#ms');
		$this->assertResponseRegExp('#/divisions/delete\?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN . '#ms');
	}

	/**
	 * Test stats method as a coordinator
	 *
	 * @return void
	 */
	public function testStatsAsCoordinator() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		// Everyone is allowed to view the stats; admins, managers and coordinators have extra options
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_COORDINATOR);
		$this->assertResponseRegExp('#/divisions/edit\?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN . '#ms');
		$this->assertResponseNotRegExp('#/divisions/delete\?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN . '#ms');
	}

	/**
	 * Test stats method as a captain
	 *
	 * @return void
	 */
	public function testStatsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stats method as a player
	 *
	 * @return void
	 */
	public function testStatsAsPlayer() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		// Everyone is allowed to view the stats; admins, managers and coordinators have extra options
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#/divisions/edit\?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN . '#ms');
		$this->assertResponseNotRegExp('#/divisions/delete\?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN . '#ms');
	}

	/**
	 * Test stats method as someone else
	 *
	 * @return void
	 */
	public function testStatsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stats method without being logged in
	 *
	 * @return void
	 */
	public function testStatsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins can add new divisions anywhere
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_ADMIN);

		// If a division ID is given, we will clone that division
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY, 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<input type="text" name="name"[^>]*value="Competitive"#ms');
		$this->assertResponseRegExp('#<input type="checkbox" name="days\[_ids\]\[\]" value="1" checked="checked" id="days-ids-1">Monday#ms');
		$this->assertResponseNotRegExp('#<input type="checkbox" name="days\[_ids\]\[\]" value="2" checked="checked" id="days-ids-2">Tuesday#ms');
		$this->assertResponseNotRegExp('#<input type="checkbox" name="days\[_ids\]\[\]" value="3" checked="checked" id="days-ids-3">Wednesday#ms');
		$this->assertResponseNotRegExp('#<input type="checkbox" name="days\[_ids\]\[\]" value="4" checked="checked" id="days-ids-4">Thursday#ms');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers can add new divisions in their own affiliate, but not others
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a player
	 *
	 * @return void
	 */
	public function testAddAsPlayer() {
		// Others cannot add new divisions
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins can edit divisions anywhere
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers can edit divisions in their own affiliate, but not others
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Coordinators can edit their own divisions, but not others
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_COORDINATOR);
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
		// Others cannot edit divisions
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
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
	 * Test scheduling_fields method as an admin
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduling_fields method as a manager
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduling_fields method as a coordinator
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduling_fields method as a captain
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduling_fields method as a player
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduling_fields method as someone else
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduling_fields method without being logged in
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_coordinator method as an admin
	 *
	 * @return void
	 */
	public function testAddCoordinatorAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add coordinators
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER2], PERSON_ID_ADMIN);

		// Try the search page
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER2],
			PERSON_ID_ADMIN, 'post', [
				'affiliate_id' => '1',
				'first_name' => '',
				'last_name' => 'coordinator',
				'sort' => 'last_name',
				'direction' => 'asc',
			]);
		$this->assertResponseRegExp('#/divisions/add_coordinator\?person=' . PERSON_ID_COORDINATOR . '&amp;division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');

		// Try to add the coordinator
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'add_coordinator', 'person' => PERSON_ID_COORDINATOR, 'division' => DIVISION_ID_MONDAY_LADDER2],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER2],
			'Added Cindy Coordinator as coordinator.', 'Flash.flash.0.message');

		// Make sure they were added successfully
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER2], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/divisions/remove_coordinator\?division=' . DIVISION_ID_MONDAY_LADDER2 . '&amp;person=' . PERSON_ID_COORDINATOR . '#ms');
	}

	/**
	 * Test add_coordinator method as a manager
	 *
	 * @return void
	 */
	public function testAddCoordinatorAsManager() {
		// Managers are allowed to add coordinators
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER2], PERSON_ID_MANAGER);
	}

	/**
	 * Test add_coordinator method as a coordinator
	 *
	 * @return void
	 */
	public function testAddCoordinatorAsCoordinator() {
		// Others are not allowed to add coordinators
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test add_coordinator method as a captain
	 *
	 * @return void
	 */
	public function testAddCoordinatorAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_coordinator method as a player
	 *
	 * @return void
	 */
	public function testAddCoordinatorAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_coordinator method as someone else
	 *
	 * @return void
	 */
	public function testAddCoordinatorAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_coordinator method without being logged in
	 *
	 * @return void
	 */
	public function testAddCoordinatorAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_coordinator method as an admin
	 *
	 * @return void
	 */
	public function testRemoveCoordinatorAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to remove coordinators
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER, 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER],
			'Successfully removed coordinator.', 'Flash.flash.0.message');
	}

	/**
	 * Test remove_coordinator method as a manager
	 *
	 * @return void
	 */
	public function testRemoveCoordinatorAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_coordinator method as a coordinator
	 *
	 * @return void
	 */
	public function testRemoveCoordinatorAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_coordinator method as a captain
	 *
	 * @return void
	 */
	public function testRemoveCoordinatorAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to remove coordinators
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN, 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_CAPTAIN, 'post');
	}

	/**
	 * Test remove_coordinator method as a player
	 *
	 * @return void
	 */
	public function testRemoveCoordinatorAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_coordinator method as someone else
	 *
	 * @return void
	 */
	public function testRemoveCoordinatorAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test remove_coordinator method without being logged in
	 *
	 * @return void
	 */
	public function testRemoveCoordinatorAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method as an admin
	 *
	 * @return void
	 */
	public function testAddTeamsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method as a manager
	 *
	 * @return void
	 */
	public function testAddTeamsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method as a coordinator
	 *
	 * @return void
	 */
	public function testAddTeamsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method as a captain
	 *
	 * @return void
	 */
	public function testAddTeamsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method as a player
	 *
	 * @return void
	 */
	public function testAddTeamsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method as someone else
	 *
	 * @return void
	 */
	public function testAddTeamsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method without being logged in
	 *
	 * @return void
	 */
	public function testAddTeamsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method as an admin
	 *
	 * @return void
	 */
	public function testRatingsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method as a manager
	 *
	 * @return void
	 */
	public function testRatingsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method as a coordinator
	 *
	 * @return void
	 */
	public function testRatingsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method as a captain
	 *
	 * @return void
	 */
	public function testRatingsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method as a player
	 *
	 * @return void
	 */
	public function testRatingsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method as someone else
	 *
	 * @return void
	 */
	public function testRatingsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method without being logged in
	 *
	 * @return void
	 */
	public function testRatingsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method as an admin
	 *
	 * @return void
	 */
	public function testSeedsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method as a manager
	 *
	 * @return void
	 */
	public function testSeedsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method as a coordinator
	 *
	 * @return void
	 */
	public function testSeedsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method as a captain
	 *
	 * @return void
	 */
	public function testSeedsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method as a player
	 *
	 * @return void
	 */
	public function testSeedsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method as someone else
	 *
	 * @return void
	 */
	public function testSeedsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method without being logged in
	 *
	 * @return void
	 */
	public function testSeedsAsAnonymous() {
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

		// Admins are allowed to delete divisions
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER2],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Leagues', 'action' => 'index'],
			'The division has been deleted.', 'Flash.flash.0.message');

		// But not the last division in a league
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_FRIDAY],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Leagues', 'action' => 'index'],
			'You cannot delete the only division in a league.', 'Flash.flash.0.message');

		// And not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Leagues', 'action' => 'index'],
			'#The following records reference this division, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete divisions in their affiliate
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER2],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Leagues', 'action' => 'index'],
			'The division has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_SUNDAY_SUB],
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
	 * Test schedule method as an admin
	 *
	 * @return void
	 */
	public function testScheduleAsAdmin() {
		// Submit and attendance links will depend on the date, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow((new FrozenDate('first Monday of June'))->addDays(22));
		FrozenTime::setTestNow((new FrozenTime('first Monday of June'))->addDays(22));

		// Admins get the schedule with edit links
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_CANCELLED . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 2</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_GREEN . '"[^>]*>Green</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">cancelled\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_LADDER_CANCELLED . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_HOME_DEFAULT . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_GREEN . '"[^>]*>Green</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">0 - 6\s*\(default\)\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_LADDER_HOME_DEFAULT . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_AWAY_DEFAULT . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 2</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">6 - 0\s*\(default\)\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_LADDER_AWAY_DEFAULT . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_MISMATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 2</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_GREEN . '"[^>]*>Green</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">score mismatch\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_LADDER_MISMATCHED_SCORES . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Confirm that there are appropriate links for unfinalized weeks
		$date = (new FrozenDate('third Monday of June'))->toDateString();
		$this->assertResponseRegExp('#/divisions/schedule\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;edit_date=' . $date . '#ms');
		$this->assertResponseRegExp('#/divisions/slots\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');
		$this->assertResponseRegExp('#/schedules/delete\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');
		$this->assertResponseRegExp('#/schedules/reschedule\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');
		$this->assertResponseRegExp('#/schedules/unpublish\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');

		// Admins don't get to submit scores or do attendance
		$this->assertResponseNotRegExp('#/games/submit_score#ms');
		$this->assertResponseNotRegExp('#/games/attendance#ms');

		// Check for initialize dependencies link where appropriate
		$date = (new FrozenDate('first Monday of September'))->toDateString();
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_PLAYOFF], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '&amp;date=' . $date . '[^>]*\"#ms');
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '&amp;date=' . $date . '&amp;reset=1[^>]*\"#ms');

		// Admins can get schedules from any affiliate
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_ADMIN);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_SUB . '"[^>]*>7:00PM-8:30PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_CENTRAL_TECH . '"[^>]*>CTS 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_BEARS . '"[^>]*>Bears</a> <span[^>]*title="Shirt colour: Brown"[^>]*><img src="/img/shirts/brown.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_LIONS . '"[^>]*>Lions</a> <span[^>]*title="Shirt colour: Gold"[^>]*><img src="/img/shirts/default.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_SUB . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Tuesday week 2 game isn't published, but admins can see it
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_ADMIN);
		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2 . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
	}

	/**
	 * Test schedule method as a manager
	 *
	 * @return void
	 */
	public function testScheduleAsManager() {
		// Submit and attendance links will depend on the date, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow((new FrozenDate('first Monday of June'))->addDays(22));
		FrozenTime::setTestNow((new FrozenTime('first Monday of June'))->addDays(22));

		// Managers get the schedule with edit links
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Confirm that there are appropriate links for unfinalized weeks
		$date = (new FrozenDate('third Monday of June'))->toDateString();
		$this->assertResponseRegExp('#/divisions/schedule\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;edit_date=' . $date . '#ms');
		$this->assertResponseRegExp('#/divisions/slots\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');
		$this->assertResponseRegExp('#/schedules/delete\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');
		$this->assertResponseRegExp('#/schedules/reschedule\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');
		$this->assertResponseRegExp('#/schedules/unpublish\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');

		// Managers don't get to submit scores or do attendance
		$this->assertResponseNotRegExp('#/games/submit_score#ms');
		$this->assertResponseNotRegExp('#/games/attendance#ms');

		// Managers can get schedules from any affiliate, but no edit links
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_MANAGER);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_SUB . '"[^>]*>7:00PM-8:30PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_CENTRAL_TECH . '"[^>]*>CTS 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_BEARS . '"[^>]*>Bears</a> <span[^>]*title="Shirt colour: Brown"[^>]*><img src="/img/shirts/brown.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_LIONS . '"[^>]*>Lions</a> <span[^>]*title="Shirt colour: Gold"[^>]*><img src="/img/shirts/default.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"></span></td>';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
		$this->assertResponseNotRegExp('#/games/edit#ms');

		// Tuesday week 2 game isn't published, but managers can see it
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_MANAGER);
		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2 . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
	}

	/**
	 * Test schedule method as a coordinator
	 *
	 * @return void
	 */
	public function testScheduleAsCoordinator() {
		// Submit and attendance links will depend on the date, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow((new FrozenDate('first Monday of June'))->addDays(22));
		FrozenTime::setTestNow((new FrozenTime('first Monday of June'))->addDays(22));

		// Coordinators get the schedule with edit links
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions"><a href="/games/edit\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Confirm that there are appropriate links for unfinalized weeks
		$date = (new FrozenDate('third Monday of June'))->toDateString();
		$this->assertResponseRegExp('#/divisions/schedule\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;edit_date=' . $date . '#ms');
		$this->assertResponseRegExp('#/divisions/slots\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');
		$this->assertResponseRegExp('#/schedules/delete\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');
		$this->assertResponseRegExp('#/schedules/reschedule\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');
		$this->assertResponseRegExp('#/schedules/unpublish\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date . '#ms');

		// Coordinators don't get to submit scores or do attendance
		$this->assertResponseNotRegExp('#/games/submit_score#ms');
		$this->assertResponseNotRegExp('#/games/attendance#ms');

		// Coordinators can get schedules from any division, but no edit links, and can't see unpublished games there
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_COORDINATOR);
		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"></span></td>';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
		$this->assertResponseNotRegExp('#/games/edit#ms');
		$this->assertResponseNotRegExp('#/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2 . '#ms');
	}

	/**
	 * Test schedule method as a captain
	 *
	 * @return void
	 */
	public function testScheduleAsCaptain() {
		// Submit and attendance links will depend on the date, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow((new FrozenDate('first Monday of June'))->addDays(22));
		FrozenTime::setTestNow((new FrozenTime('first Monday of June'))->addDays(22));

		// Captains get the schedule with score submission, attendance and game note links where appropriate
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
		$this->assertResponseNotRegExp('#<a href="/games/submit_score\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '[^>0-9]*"#ms');

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions"><a href="/games/submit_score\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Captains don't get to edit games or do anything with schedules
		$this->assertResponseNotRegExp('#/games/edit#ms');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotRegExp('#/divisions/slots#ms');
		$this->assertResponseNotRegExp('#/schedules/delete#ms');
		$this->assertResponseNotRegExp('#/schedules/reschedule#ms');
		$this->assertResponseNotRegExp('#/schedules/unpublish#ms');

		// Captains can get schedules from any division, but no edit or submit links
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_CAPTAIN);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_THURSDAY_ROUND_ROBIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_CHICKADEES . '"[^>]*>Chickadees</a> <span[^>]*title="Shirt colour: White"[^>]*><img src="/img/shirts/white.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_SPARROWS . '"[^>]*>Sparrows</a> <span[^>]*title="Shirt colour: Brown"[^>]*><img src="/img/shirts/brown.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">15 - 14\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
		$this->assertResponseNotRegExp('#/games/edit#ms');
		$this->assertResponseNotRegExp('#/games/submit_score#ms');
	}

	/**
	 * Test schedule method as a player
	 *
	 * @return void
	 */
	public function testScheduleAsPlayer() {
		// Submit and attendance links will depend on the date, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow((new FrozenDate('first Monday of June'))->addDays(22));
		FrozenTime::setTestNow((new FrozenTime('first Monday of June'))->addDays(22));

		// Players get the schedule with attendance and game note links where appropriate
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// TODO: Check a future game

		// Players don't get to edit games, submit scores or do anything with schedules
		$this->assertResponseNotRegExp('#/games/edit#ms');
		$this->assertResponseNotRegExp('#/games/submit_score#ms');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotRegExp('#/divisions/slots#ms');
		$this->assertResponseNotRegExp('#/schedules/delete#ms');
		$this->assertResponseNotRegExp('#/schedules/reschedule#ms');
		$this->assertResponseNotRegExp('#/schedules/unpublish#ms');
	}

	/**
	 * Test schedule method as someone else
	 *
	 * @return void
	 */
	public function testScheduleAsVisitor() {
		// Submit and attendance links will depend on the date, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow((new FrozenDate('first Monday of June'))->addDays(22));
		FrozenTime::setTestNow((new FrozenTime('first Monday of June'))->addDays(22));

		// Visitors get the schedule with minimal links
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Visitors don't get to edit games, submit scores, do attendance, or anything with schedules
		$this->assertResponseNotRegExp('#/games/edit#ms');
		$this->assertResponseNotRegExp('#/games/submit_score#ms');
		$this->assertResponseNotRegExp('#/games/attendance#ms');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotRegExp('#/divisions/slots#ms');
		$this->assertResponseNotRegExp('#/schedules/delete#ms');
		$this->assertResponseNotRegExp('#/schedules/reschedule#ms');
		$this->assertResponseNotRegExp('#/schedules/unpublish#ms');
	}

	/**
	 * Test schedule method without being logged in
	 *
	 * @return void
	 */
	public function testScheduleAsAnonymous() {
		// Submit and attendance links will depend on the date, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow((new FrozenDate('first Monday of June'))->addDays(22));
		FrozenTime::setTestNow((new FrozenTime('first Monday of June'))->addDays(22));

		// Anonymous browsers get the schedule with minimal links
		$this->assertAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Anonymous browsers don't get any actions
		$this->assertResponseNotRegExp('#/games/edit#ms');
		$this->assertResponseNotRegExp('#/games/submit_score#ms');
		$this->assertResponseNotRegExp('#/games/attendance#ms');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotRegExp('#/divisions/slots#ms');
		$this->assertResponseNotRegExp('#/schedules/delete#ms');
		$this->assertResponseNotRegExp('#/schedules/reschedule#ms');
		$this->assertResponseNotRegExp('#/schedules/unpublish#ms');
	}

	/**
	 * Test standings method as an admin
	 *
	 * @return void
	 */
	public function testStandingsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test standings method as a manager
	 *
	 * @return void
	 */
	public function testStandingsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test standings method as a coordinator
	 *
	 * @return void
	 */
	public function testStandingsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test standings method as a captain
	 *
	 * @return void
	 */
	public function testStandingsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test standings method as a player
	 *
	 * @return void
	 */
	public function testStandingsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test standings method as someone else
	 *
	 * @return void
	 */
	public function testStandingsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test standings method without being logged in
	 *
	 * @return void
	 */
	public function testStandingsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scores method as an admin
	 *
	 * @return void
	 */
	public function testScoresAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scores method as a manager
	 *
	 * @return void
	 */
	public function testScoresAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scores method as a coordinator
	 *
	 * @return void
	 */
	public function testScoresAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scores method as a captain
	 *
	 * @return void
	 */
	public function testScoresAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scores method as a player
	 *
	 * @return void
	 */
	public function testScoresAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scores method as someone else
	 *
	 * @return void
	 */
	public function testScoresAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scores method without being logged in
	 *
	 * @return void
	 */
	public function testScoresAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fields method as an admin
	 *
	 * @return void
	 */
	public function testFieldsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fields method as a manager
	 *
	 * @return void
	 */
	public function testFieldsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fields method as a coordinator
	 *
	 * @return void
	 */
	public function testFieldsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fields method as a captain
	 *
	 * @return void
	 */
	public function testFieldsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fields method as a player
	 *
	 * @return void
	 */
	public function testFieldsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fields method as someone else
	 *
	 * @return void
	 */
	public function testFieldsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fields method without being logged in
	 *
	 * @return void
	 */
	public function testFieldsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test slots method as an admin
	 *
	 * @return void
	 */
	public function testSlotsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test slots method as a manager
	 *
	 * @return void
	 */
	public function testSlotsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test slots method as a coordinator
	 *
	 * @return void
	 */
	public function testSlotsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test slots method as a captain
	 *
	 * @return void
	 */
	public function testSlotsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test slots method as a player
	 *
	 * @return void
	 */
	public function testSlotsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test slots method as someone else
	 *
	 * @return void
	 */
	public function testSlotsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test slots method without being logged in
	 *
	 * @return void
	 */
	public function testSlotsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test status method as an admin
	 *
	 * @return void
	 */
	public function testStatusAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test status method as a manager
	 *
	 * @return void
	 */
	public function testStatusAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test status method as a coordinator
	 *
	 * @return void
	 */
	public function testStatusAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test status method as a captain
	 *
	 * @return void
	 */
	public function testStatusAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test status method as a player
	 *
	 * @return void
	 */
	public function testStatusAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test status method as someone else
	 *
	 * @return void
	 */
	public function testStatusAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test status method without being logged in
	 *
	 * @return void
	 */
	public function testStatusAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allstars method as an admin
	 *
	 * @return void
	 */
	public function testAllstarsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allstars method as a manager
	 *
	 * @return void
	 */
	public function testAllstarsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allstars method as a coordinator
	 *
	 * @return void
	 */
	public function testAllstarsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allstars method as a captain
	 *
	 * @return void
	 */
	public function testAllstarsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allstars method as a player
	 *
	 * @return void
	 */
	public function testAllstarsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allstars method as someone else
	 *
	 * @return void
	 */
	public function testAllstarsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allstars method without being logged in
	 *
	 * @return void
	 */
	public function testAllstarsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test emails method as an admin
	 *
	 * @return void
	 */
	public function testEmailsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test emails method as a manager
	 *
	 * @return void
	 */
	public function testEmailsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test emails method as a coordinator
	 *
	 * @return void
	 */
	public function testEmailsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test emails method as a captain
	 *
	 * @return void
	 */
	public function testEmailsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test emails method as a player
	 *
	 * @return void
	 */
	public function testEmailsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test emails method as someone else
	 *
	 * @return void
	 */
	public function testEmailsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test emails method without being logged in
	 *
	 * @return void
	 */
	public function testEmailsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test spirit method as an admin
	 *
	 * @return void
	 */
	public function testSpiritAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test spirit method as a manager
	 *
	 * @return void
	 */
	public function testSpiritAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test spirit method as a coordinator
	 *
	 * @return void
	 */
	public function testSpiritAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test spirit method as a captain
	 *
	 * @return void
	 */
	public function testSpiritAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test spirit method as a player
	 *
	 * @return void
	 */
	public function testSpiritAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test spirit method as someone else
	 *
	 * @return void
	 */
	public function testSpiritAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test spirit method without being logged in
	 *
	 * @return void
	 */
	public function testSpiritAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_scores method as an admin
	 *
	 * @return void
	 */
	public function testApproveScoresAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_scores method as a manager
	 *
	 * @return void
	 */
	public function testApproveScoresAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_scores method as a coordinator
	 *
	 * @return void
	 */
	public function testApproveScoresAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_scores method as a captain
	 *
	 * @return void
	 */
	public function testApproveScoresAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_scores method as a player
	 *
	 * @return void
	 */
	public function testApproveScoresAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_scores method as someone else
	 *
	 * @return void
	 */
	public function testApproveScoresAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test approve_scores method without being logged in
	 *
	 * @return void
	 */
	public function testApproveScoresAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method as an admin
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method as a manager
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method as a coordinator
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method as a captain
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method as a player
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method as someone else
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method without being logged in
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method as an admin
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method as a manager
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method as a coordinator
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method as a captain
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method as a player
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method as someone else
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method without being logged in
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method as an admin
	 *
	 * @return void
	 */
	public function testDeleteStageAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method as a manager
	 *
	 * @return void
	 */
	public function testDeleteStageAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteStageAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method as a captain
	 *
	 * @return void
	 */
	public function testDeleteStageAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method as a player
	 *
	 * @return void
	 */
	public function testDeleteStageAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method as someone else
	 *
	 * @return void
	 */
	public function testDeleteStageAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteStageAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method as an admin
	 *
	 * @return void
	 */
	public function testRedirectAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method as a manager
	 *
	 * @return void
	 */
	public function testRedirectAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method as a coordinator
	 *
	 * @return void
	 */
	public function testRedirectAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method as a captain
	 *
	 * @return void
	 */
	public function testRedirectAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method as a player
	 *
	 * @return void
	 */
	public function testRedirectAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method as someone else
	 *
	 * @return void
	 */
	public function testRedirectAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redirect method without being logged in
	 *
	 * @return void
	 */
	public function testRedirectAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as an admin
	 *
	 * @return void
	 */
	public function testSelectAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as a manager
	 *
	 * @return void
	 */
	public function testSelectAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as a coordinator
	 *
	 * @return void
	 */
	public function testSelectAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as a captain
	 *
	 * @return void
	 */
	public function testSelectAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as a player
	 *
	 * @return void
	 */
	public function testSelectAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method as someone else
	 *
	 * @return void
	 */
	public function testSelectAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method without being logged in
	 *
	 * @return void
	 */
	public function testSelectAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
