<?php
namespace App\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

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
						'app.games_allstars',
						'app.score_entries',
						'app.spirit_entries',
						'app.incidents',
						'app.stats',
				'app.leagues_stat_types',
			'app.franchises',
				'app.franchises_teams',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
				'app.preregistrations',
			'app.badges',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
	];

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		// Anyone is allowed to view the index; admins, managers and coordinators have extra options
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertResponseContains('/divisions/edit?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions/delete?division=' . DIVISION_ID_MONDAY_LADDER);

		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->assertResponseContains('/divisions/edit?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions/delete?division=' . DIVISION_ID_MONDAY_LADDER);

		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/divisions/edit?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseNotContains('/divisions/delete?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/divisions/edit?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseNotContains('/divisions/delete?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('/divisions/edit?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseNotContains('/divisions/delete?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertGetAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER]);
		$this->assertResponseNotContains('/divisions/edit?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseNotContains('/divisions/delete?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test tooltip method
	 *
	 * @return void
	 */
	public function testTooltip() {
		// Anyone is allowed to view division tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => 0],
			PERSON_ID_ADMIN, ['controller' => 'Leagues', 'action' => 'index'],
			'Invalid division.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'tooltip', 'division' => DIVISION_ID_MONDAY_LADDER]);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test stats method
	 *
	 * @return void
	 */
	public function testStats() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		// Anyone is allowed to view the stats; admins, managers and coordinators have extra options
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_ADMIN);
		$this->assertResponseContains('/divisions/edit?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);
		$this->assertResponseContains('/divisions/delete?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_MANAGER);
		$this->assertResponseContains('/divisions/edit?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);
		$this->assertResponseContains('/divisions/delete?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/divisions/edit?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);
		$this->assertResponseNotContains('/divisions/delete?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('/divisions/edit?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);
		$this->assertResponseNotContains('/divisions/delete?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/divisions/edit?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);
		$this->assertResponseNotContains('/divisions/delete?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);

		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('/divisions/edit?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);
		$this->assertResponseNotContains('/divisions/delete?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);

		// Non-public sites, stats are not available unless logged in
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN]);

		// With public sites, anyone is allowed to view the stats
		Cache::clear(false, 'long_term');
		TableRegistry::get('Settings')->updateAll(['value' => true], ['category' => 'feature', 'name' => 'public']);
		$this->assertGetAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'stats', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN]);
		$this->assertResponseNotContains('/divisions/edit?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);
		$this->assertResponseNotContains('/divisions/delete?division=' . DIVISION_ID_THURSDAY_ROUND_ROBIN);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add new divisions anywhere
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_ADMIN);

		// If a division ID is given, we will clone that division
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY, 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<input type="text" name="name"[^>]*value="Competitive"#ms');
		$this->assertResponseContains('<input type="checkbox" name="days[_ids][]" value="1" checked="checked" id="days-ids-1">Monday');
		$this->assertResponseNotContains('<input type="checkbox" name="days[_ids][]" value="2" checked="checked" id="days-ids-2">Tuesday');
		$this->assertResponseNotContains('<input type="checkbox" name="days[_ids][]" value="3" checked="checked" id="days-ids-3">Wednesday');
		$this->assertResponseNotContains('<input type="checkbox" name="days[_ids][]" value="4" checked="checked" id="days-ids-4">Thursday');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add new divisions in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add new divisions
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY]);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit divisions anywhere
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit divisions in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Coordinators are allowed to edit their own divisions, but not others
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit divisions
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'edit', 'division' => DIVISION_ID_MONDAY_LADDER]);
	}

	/**
	 * Test scheduling_fields method as an admin
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsAdmin() {
		$this->enableCsrfToken();

		// Admins are allowed to get the scheduling fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			PERSON_ID_ADMIN, ['schedule_type' => 'ratings_ladder']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduling_fields method as a manager
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsManager() {
		$this->enableCsrfToken();

		// Managers are allowed to get the scheduling fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			PERSON_ID_MANAGER, ['schedule_type' => 'ratings_ladder']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduling_fields method as a coordinator
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsCoordinator() {
		$this->enableCsrfToken();

		// Coordinators are allowed to get the scheduling fields
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			PERSON_ID_COORDINATOR, ['schedule_type' => 'ratings_ladder']);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduling_fields method as others
	 *
	 * @return void
	 */
	public function testSchedulingFieldsAsOthers() {
		$this->enableCsrfToken();

		// Others are not allowed to get the scheduling fields
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			PERSON_ID_CAPTAIN, ['schedule_type' => 'ratings_ladder']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			PERSON_ID_PLAYER, ['schedule_type' => 'ratings_ladder']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			PERSON_ID_VISITOR, ['schedule_type' => 'ratings_ladder']);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'scheduling_fields'],
			['schedule_type' => 'ratings_ladder']);
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
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER2], PERSON_ID_ADMIN);

		// Try the search page
		$this->assertPostAsAccessOk(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER2],
			PERSON_ID_ADMIN, [
				'affiliate_id' => '1',
				'first_name' => '',
				'last_name' => 'coordinator',
				'sort' => 'last_name',
				'direction' => 'asc',
			]
		);
		$this->assertResponseContains('/divisions/add_coordinator?person=' . PERSON_ID_COORDINATOR . '&amp;division=' . DIVISION_ID_MONDAY_LADDER2);

		// Try to add the coordinator
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'add_coordinator', 'person' => PERSON_ID_COORDINATOR, 'division' => DIVISION_ID_MONDAY_LADDER2],
			PERSON_ID_ADMIN, [], ['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER2],
			'Added Cindy Coordinator as coordinator.');

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER2], PERSON_ID_ADMIN);
		$this->assertResponseContains('/divisions/remove_coordinator?division=' . DIVISION_ID_MONDAY_LADDER2 . '&amp;person=' . PERSON_ID_COORDINATOR);
	}

	/**
	 * Test add_coordinator method as a manager
	 *
	 * @return void
	 */
	public function testAddCoordinatorAsManager() {
		// Managers are allowed to add coordinators
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER2], PERSON_ID_MANAGER);
	}

	/**
	 * Test add_coordinator method as others
	 *
	 * @return void
	 */
	public function testAddCoordinatorAsOthers() {
		// Others are not allowed to add coordinators
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'add_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER]);
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
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER, 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_ADMIN, [], ['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER],
			'Successfully removed coordinator.');
	}

	/**
	 * Test remove_coordinator method as a manager
	 *
	 * @return void
	 */
	public function testRemoveCoordinatorAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to remove coordinators
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER, 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_MANAGER, [], ['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_LADDER],
			'Successfully removed coordinator.');
	}

	/**
	 * Test remove_coordinator method as others
	 *
	 * @return void
	 */
	public function testRemoveCoordinatorAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to remove coordinators
		$this->assertPostAsAccessDenied(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER, 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER, 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER, 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER, 'person' => PERSON_ID_COORDINATOR],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'remove_coordinator', 'division' => DIVISION_ID_MONDAY_LADDER, 'person' => PERSON_ID_COORDINATOR]);
	}

	/**
	 * Test add_teams method as an admin
	 *
	 * @return void
	 */
	public function testAddTeamsAsAdmin() {
		// Admins are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method as a manager
	 *
	 * @return void
	 */
	public function testAddTeamsAsManager() {
		// Managers are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method as a coordinator
	 *
	 * @return void
	 */
	public function testAddTeamsAsCoordinator() {
		// Coordinators are allowed to add teams
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_teams method as others
	 *
	 * @return void
	 */
	public function testAddTeamsAsOthers() {
		// Captains are not allowed to add teams
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'add_teams', 'division' => DIVISION_ID_MONDAY_LADDER]);
	}

	/**
	 * Test ratings method as an admin
	 *
	 * @return void
	 */
	public function testRatingsAsAdmin() {
		// Admins are allowed to ratings
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'ratings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method as a manager
	 *
	 * @return void
	 */
	public function testRatingsAsManager() {
		// Managers are allowed to ratings
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'ratings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method as a coordinator
	 *
	 * @return void
	 */
	public function testRatingsAsCoordinator() {
		// Coordinators are allowed to ratings
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'ratings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings method as others
	 *
	 * @return void
	 */
	public function testRatingsAsOthers() {
		// Others are not allowed to ratings
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'ratings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'ratings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'ratings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'ratings', 'division' => DIVISION_ID_MONDAY_LADDER]);
	}

	/**
	 * Test seeds method as an admin
	 *
	 * @return void
	 */
	public function testSeedsAsAdmin() {
		// Admins are allowed to update seeds
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'seeds', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method as a manager
	 *
	 * @return void
	 */
	public function testSeedsAsManager() {
		// Managers are allowed to update seeds
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'seeds', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method as a coordinator
	 *
	 * @return void
	 */
	public function testSeedsAsCoordinator() {
		// Coordinators are allowed to update seeds
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'seeds', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test seeds method as others
	 *
	 * @return void
	 */
	public function testSeedsAsOthers() {
		// Others are not allowed to update seeds
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'seeds', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'seeds', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'seeds', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'seeds', 'division' => DIVISION_ID_MONDAY_LADDER]);
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
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER2],
			PERSON_ID_ADMIN, [], ['controller' => 'Leagues', 'action' => 'index'],
			'The division has been deleted.');

		// But not the last division in a league
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_FRIDAY],
			PERSON_ID_ADMIN, [], ['controller' => 'Leagues', 'action' => 'index'],
			'You cannot delete the only division in a league.');

		// And not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER],
			PERSON_ID_ADMIN, [], ['controller' => 'Leagues', 'action' => 'index'],
			'#The following records reference this division, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete divisions in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER2],
			PERSON_ID_MANAGER, [], ['controller' => 'Leagues', 'action' => 'index'],
			'The division has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_SUNDAY_SUB],
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

		// Others are not allowed to delete divisions
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER]);
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
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_CANCELLED . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 2</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_GREEN . '"[^>]*>Green</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">cancelled\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_LADDER_CANCELLED . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_HOME_DEFAULT . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_GREEN . '"[^>]*>Green</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">0 - 6\s*\(default\)\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_LADDER_HOME_DEFAULT . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_AWAY_DEFAULT . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 2</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">6 - 0\s*\(default\)\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_LADDER_AWAY_DEFAULT . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_MISMATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 2</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_GREEN . '"[^>]*>Green</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">score mismatch\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_LADDER_MISMATCHED_SCORES . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Confirm that there are appropriate links for unfinalized weeks
		$date = (new FrozenDate('third Monday of June'))->toDateString();
		$this->assertResponseContains('/divisions/schedule?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;edit_date=' . $date);
		$this->assertResponseContains('/divisions/slots?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/delete?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/reschedule?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/unpublish?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);

		// Admins don't get to submit scores or do attendance
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/attendance');

		// Check for initialize dependencies link where appropriate
		$date = (new FrozenDate('first Monday of September'))->toDateString();
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_PLAYOFF], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '&amp;date=' . $date . '[^>]*\"#ms');
		$this->assertResponseRegExp('#/divisions/initialize_dependencies\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '&amp;date=' . $date . '&amp;reset=1[^>]*\"#ms');

		// Admins are allowed to see schedules from any affiliate
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_ADMIN);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_SUB . '"[^>]*>7:00PM-8:30PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_CENTRAL_TECH . '"[^>]*>CTS 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_BEARS . '"[^>]*>Bears</a> <span[^>]*title="Shirt colour: Brown"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/brown.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_LIONS . '"[^>]*>Lions</a> <span[^>]*title="Shirt colour: Gold"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/default.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_SUB . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Tuesday week 2 game isn't published, but admins can see it
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_ADMIN);
		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2 . '[^>]*"';
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
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Confirm that there are appropriate links for unfinalized weeks
		$date = (new FrozenDate('third Monday of June'))->toDateString();
		$this->assertResponseContains('/divisions/schedule?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;edit_date=' . $date);
		$this->assertResponseContains('/divisions/slots?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/delete?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/reschedule?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/unpublish?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);

		// Managers don't get to submit scores or do attendance
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/attendance');

		// Managers are allowed to see schedules from any affiliate, but no edit links
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_MANAGER);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_SUB . '"[^>]*>7:00PM-8:30PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_CENTRAL_TECH . '"[^>]*>CTS 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_BEARS . '"[^>]*>Bears</a> <span[^>]*title="Shirt colour: Brown"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/brown.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_LIONS . '"[^>]*>Lions</a> <span[^>]*title="Shirt colour: Gold"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/default.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"></span></td>';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
		$this->assertResponseNotContains('/games/edit');

		// Tuesday week 2 game isn't published, but Managers are allowed to see it
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_MANAGER);
		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2 . '[^>]*"';
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
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/edit\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Confirm that there are appropriate links for unfinalized weeks
		$date = (new FrozenDate('third Monday of June'))->toDateString();
		$this->assertResponseContains('/divisions/schedule?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;edit_date=' . $date);
		$this->assertResponseContains('/divisions/slots?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/delete?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/reschedule?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);
		$this->assertResponseContains('/schedules/unpublish?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date);

		// Coordinators don't get to submit scores or do attendance
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/attendance');

		// Coordinators are allowed to see schedules from any division, but no edit links, and can't see unpublished games there
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_COORDINATOR);
		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_MAPLES . '"[^>]*>Maples</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_OAKS . '"[^>]*>Oaks</a> <span[^>]*title="Shirt colour: Green"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/green.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">not entered\s*<span class="actions"></span></td>';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotContains('/games/view?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2);
	}

	/**
	 * Test schedule method as a captain
	 *
	 * @return void
	 */
	public function testScheduleAsCaptain() {
		// Submit and attendance links will depend on the date, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow((new FrozenDate('first Monday of June'))->addDays(22));

		// Captains get the schedule with score submission, attendance and game note links where appropriate
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
		$this->assertResponseNotRegExp('#<a href="' . Configure::read('App.base') . '/games/submit_score\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '[^>0-9]*"#ms');
		$this->assertResponseNotContains('stats');

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/submit_score\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '[^>]*"';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Captains don't get to edit games or do anything with schedules
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotContains('/divisions/slots');
		$this->assertResponseNotContains('/schedules/delete');
		$this->assertResponseNotContains('/schedules/reschedule');
		$this->assertResponseNotContains('/schedules/unpublish');

		// Captains are allowed to see schedules from any division, but no edit or submit links
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_CAPTAIN);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_THURSDAY_ROUND_ROBIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_CHICKADEES . '"[^>]*>Chickadees</a> <span[^>]*title="Shirt colour: White"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/white.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_SPARROWS . '"[^>]*>Sparrows</a> <span[^>]*title="Shirt colour: Brown"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/brown.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">15 - 14\s*<span class="actions"><a href="' . Configure::read('App.base') . '/games/submit_stats\?game=' . GAME_ID_THURSDAY_ROUND_ROBIN . '&amp;team=' . TEAM_ID_CHICKADEES . '">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotContains('/games/submit_score');
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
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// TODO: Check a future game

		// Players don't get to edit games, submit scores or do anything with schedules
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/submit_stats');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotContains('/divisions/slots');
		$this->assertResponseNotContains('/schedules/delete');
		$this->assertResponseNotContains('/schedules/reschedule');
		$this->assertResponseNotContains('/schedules/unpublish');
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
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Visitors don't get to edit games, submit scores, do attendance, or anything with schedules
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/attendance');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotContains('/divisions/slots');
		$this->assertResponseNotContains('/schedules/delete');
		$this->assertResponseNotContains('/schedules/reschedule');
		$this->assertResponseNotContains('/schedules/unpublish');
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
		$this->assertGetAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_FINALIZED_HOME_WIN . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_YELLOW . '"[^>]*>Yellow</a> <span[^>]*title="Shirt colour: Yellow"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/yellow.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 5\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		$game = '<td><a[^>]*href="' . Configure::read('App.base') . '/games/view\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '"[^>]*>7:00PM-9:00PM</a></td>';
		$field = '<td><a[^>]*href="' . Configure::read('App.base') . '/facilities/view\?facility=' . FACILITY_ID_SUNNYBROOK . '"[^>]*>SUN Field Hockey 1</a></td>';
		$home = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_RED . '"[^>]*>Red</a> <span[^>]*title="Shirt colour: Red"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/red.png\?\d+"[^>]*></span></td>';
		$away = '<td><a[^>]*href="' . Configure::read('App.base') . '/teams/view\?team=' . TEAM_ID_BLUE . '"[^>]*>Blue</a> <span[^>]*title="Shirt colour: Blue"[^>]*><img src="' . Configure::read('App.base') . '/img/shirts/blue.png\?\d+"[^>]*></span></td>';
		$actions = '<td class="actions">17 - 12\s*\(unofficial\)\s*<span class="actions">';
		$this->assertResponseRegExp("#$game\s*$field\s*$home\s*$away\s*$actions#ms");

		// Anonymous browsers don't get any actions
		$this->assertResponseNotContains('/games/edit');
		$this->assertResponseNotContains('/games/submit_score');
		$this->assertResponseNotContains('/games/attendance');
		$this->assertResponseNotRegExp('#/divisions/schedule\?division=\d+&amp;edit_date=#ms');
		$this->assertResponseNotContains('/divisions/slots');
		$this->assertResponseNotContains('/schedules/delete');
		$this->assertResponseNotContains('/schedules/reschedule');
		$this->assertResponseNotContains('/schedules/unpublish');
	}

	/**
	 * Test standings method
	 *
	 * @return void
	 */
	public function testStandings() {
		// Anyone logged in is allowed to view standings
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Divisions', 'action' => 'standings', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test scores method
	 *
	 * @return void
	 */
	public function testScores() {
		// Anyone logged in is allowed to view scores
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'scores', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test fields method
	 *
	 * @return void
	 */
	public function testFields() {
		// Admins are allowed to view the fields report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'fields', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);

		// Managers are allowed to view the fields report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'fields', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);

		// Coordinators are allowed to view the fields report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'fields', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);

		// Others are not allowed to view the fields report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'fields', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'fields', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'fields', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'fields', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test slots method
	 *
	 * @return void
	 */
	public function testSlots() {
		// Admins are allowed to view the slots report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'slots', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);

		// Managers are allowed to view the slots report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'slots', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);

		// Coordinators are allowed to view the slots report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'slots', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);

		// Others are not allowed to view the slots report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'slots', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'slots', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'slots', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'slots', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test status method
	 *
	 * @return void
	 */
	public function testStatus() {
		// Admins are allowed to view the status report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'status', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);

		// Managers are allowed to view the status report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'status', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);

		// Coordinators are allowed to view the status report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'status', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);

		// Others are not allowed to view the status report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'status', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'status', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'status', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'status', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test allstars method
	 *
	 * @return void
	 */
	public function testAllstars() {
		// Admins are allowed to view the allstars report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'allstars', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);

		// Managers are allowed to view the allstars report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'allstars', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);

		// Coordinators are allowed to view the allstars report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'allstars', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);

		// Others are not allowed to view the allstars report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'allstars', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'allstars', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'allstars', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'allstars', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test emails method
	 *
	 * @return void
	 */
	public function testEmails() {
		// Admins are allowed to view emails
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'emails', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);

		// Managers are allowed to view emails
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'emails', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);

		// Coordinators are allowed to view emails
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'emails', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);

		// Others are not allowed to view emails
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'emails', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'emails', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'emails', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'emails', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test spirit method
	 *
	 * @return void
	 */
	public function testSpirit() {
		// Admins are allowed to view the spirit report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'spirit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);

		// Managers are allowed to view the spirit report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'spirit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);

		// Coordinators are allowed to view the spirit report
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'spirit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);

		// Others are not allowed to view the spirit report
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'spirit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'spirit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'spirit', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'spirit', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test approve_scores method
	 *
	 * @return void
	 */
	public function testApproveScores() {
		// Make sure that we're after the game date
		FrozenDate::setTestNow(new FrozenDate('July 1'));
		FrozenTime::setTestNow(new FrozenTime('July 1'));

		// Admins are allowed to approve scores
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);

		// Managers are allowed to approve scores
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);

		// Coordinators are allowed to approve scores
		$this->assertGetAsAccessOk(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);

		// Others are not allowed to approve scores
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'approve_scores', 'division' => DIVISION_ID_MONDAY_LADDER]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test initialize_ratings method as an admin
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsAdmin() {
		// Admins are allowed to initialize ratings
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			PERSON_ID_ADMIN, ['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			'Team ratings have been initialized.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method as a manager
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsManager() {
		// Managers are allowed to initialize ratings
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			PERSON_ID_MANAGER, ['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			'Team ratings have been initialized.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method as a coordinator
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsCoordinator() {
		// Coordinators are allowed to initialize ratings
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			PERSON_ID_COORDINATOR, ['controller' => 'Divisions', 'action' => 'view', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			'Team ratings have been initialized.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_ratings method as others
	 *
	 * @return void
	 */
	public function testInitializeRatingsAsOthers() {
		// Others are not allowed to initialize ratings
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_ratings', 'division' => DIVISION_ID_MONDAY_LADDER]);
	}

	/**
	 * Test initialize_dependencies method as an admin
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsAdmin() {
		// Admins are allowed to initialize dependencies
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			PERSON_ID_ADMIN, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			'Dependencies have been resolved.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method as a manager
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsManager() {
		// Managers are allowed to initialize dependencies
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			PERSON_ID_MANAGER, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			'Dependencies have been resolved.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method as a coordinator
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsCoordinator() {
		// Coordinators are allowed to initialize dependencies
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			PERSON_ID_COORDINATOR, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			'Dependencies have been resolved.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initialize_dependencies method as others
	 *
	 * @return void
	 */
	public function testInitializeDependenciesAsOthers() {
		// Others are not allowed to initialize dependencies
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'initialize_dependencies', 'division' => DIVISION_ID_MONDAY_LADDER]);
	}

	/**
	 * Test delete_stage method as an admin
	 *
	 * @return void
	 */
	public function testDeleteStageAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete stages
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => DIVISION_ID_MONDAY_PLAYOFF, 'stage' => 2],
			PERSON_ID_ADMIN, ['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			'The pools in this stage have been deleted.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method as a manager
	 *
	 * @return void
	 */
	public function testDeleteStageAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete stages
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => DIVISION_ID_MONDAY_PLAYOFF, 'stage' => 2],
			PERSON_ID_MANAGER, ['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			'The pools in this stage have been deleted.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteStageAsCoordinator() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to delete stages
		$this->assertGetAsAccessRedirect(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => DIVISION_ID_MONDAY_PLAYOFF, 'stage' => 2],
			PERSON_ID_COORDINATOR, ['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_PLAYOFF],
			'The pools in this stage have been deleted.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_stage method as others
	 *
	 * @return void
	 */
	public function testDeleteStageAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Captains are not allowed to delete stages
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => DIVISION_ID_MONDAY_PLAYOFF, 'stage' => 2], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => DIVISION_ID_MONDAY_PLAYOFF, 'stage' => 2], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => DIVISION_ID_MONDAY_PLAYOFF, 'stage' => 2], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'delete_stage', 'division' => DIVISION_ID_MONDAY_PLAYOFF, 'stage' => 2]);
	}

	/**
	 * Test redirect method
	 *
	 * @return void
	 */
	public function testRedirect() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test select method
	 *
	 * @return void
	 */
	public function testSelect() {
		$this->enableCsrfToken();

		// Admins are allowed to select
		$now = FrozenDate::now();
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_ADMIN, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);

		// Managers are allowed to select
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_MANAGER, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);

		// Coordinators are allowed to select
		$this->assertPostAjaxAsAccessOk(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_COORDINATOR, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);

		// Others are not allowed to select
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_CAPTAIN, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_PLAYER, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => AFFILIATE_ID_CLUB],
			PERSON_ID_VISITOR, ['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Divisions', 'action' => 'select', 'affiliate' => AFFILIATE_ID_CLUB],
			['game_date' => ['year' => $now->year, 'month' => $now->month, 'day' => $now->day], 'sport' => 'ultimate']);
	}

}
