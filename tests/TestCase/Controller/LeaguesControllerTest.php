<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * App\Controller\LeaguesController Test Case
 */
class LeaguesControllerTest extends ControllerTestCase {

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
				'app.leagues_stat_types',
			'app.franchises',
				'app.franchises_teams',
			'app.events',
				'app.prices',
					'app.registrations',
			'app.settings',
	];

	/**
	 * Test index method as an admin
	 *
	 * @return void
	 */
	public function testIndexAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a manager
	 *
	 * @return void
	 */
	public function testIndexAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a coordinator
	 *
	 * @return void
	 */
	public function testIndexAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a captain
	 *
	 * @return void
	 */
	public function testIndexAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as a player
	 *
	 * @return void
	 */
	public function testIndexAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method as someone else
	 *
	 * @return void
	 */
	public function testIndexAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test index method without being logged in
	 *
	 * @return void
	 */
	public function testIndexAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test summary method as an admin
	 *
	 * @return void
	 */
	public function testSummaryAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test summary method as a manager
	 *
	 * @return void
	 */
	public function testSummaryAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test summary method as a coordinator
	 *
	 * @return void
	 */
	public function testSummaryAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test summary method as a captain
	 *
	 * @return void
	 */
	public function testSummaryAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test summary method as a player
	 *
	 * @return void
	 */
	public function testSummaryAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test summary method as someone else
	 *
	 * @return void
	 */
	public function testSummaryAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test summary method without being logged in
	 *
	 * @return void
	 */
	public function testSummaryAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view leagues, with full edit and delete permissions
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/leagues/edit\?league=' . LEAGUE_ID_MONDAY . '#ms');
		$this->assertResponseRegExp('#/leagues/delete\?league=' . LEAGUE_ID_MONDAY . '#ms');

		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#/leagues/edit\?league=' . LEAGUE_ID_SUNDAY_SUB . '#ms');
		$this->assertResponseRegExp('#/leagues/delete\?league=' . LEAGUE_ID_SUNDAY_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view, edit and delete leagues in their affiliate
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#/leagues/edit\?league=' . LEAGUE_ID_MONDAY . '#ms');
		$this->assertResponseRegExp('#/leagues/delete\?league=' . LEAGUE_ID_MONDAY . '#ms');

		// But cannot edit ones in other affiliates
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#/leagues/edit\?league=' . LEAGUE_ID_SUNDAY_SUB . '#ms');
		$this->assertResponseNotRegExp('#/leagues/delete\?league=' . LEAGUE_ID_SUNDAY_SUB . '#ms');
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
		// Others are allowed to view leagues, but have no edit permissions
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#/leagues/edit\?league=' . LEAGUE_ID_MONDAY . '#ms');
		$this->assertResponseNotRegExp('#/leagues/delete\?league=' . LEAGUE_ID_MONDAY . '#ms');
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
		// Everyone is allowed to view league tooltips
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/leagues\\\\/view\?league=' . LEAGUE_ID_MONDAY . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '#ms');

		$this->assertAccessRedirect(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => 0],
			PERSON_ID_ADMIN, 'getajax', [], ['controller' => 'Leagues', 'action' => 'index'],
			'Invalid league.');
	}

	/**
	 * Test tooltip method as a manager
	 *
	 * @return void
	 */
	public function testTooltipAsManager() {
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/leagues\\\\/view\?league=' . LEAGUE_ID_MONDAY . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '#ms');
	}

	/**
	 * Test tooltip method as a coordinator
	 *
	 * @return void
	 */
	public function testTooltipAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER, 'getajax');
		$this->assertResponseRegExp('#/leagues\\\\/view\?league=' . LEAGUE_ID_MONDAY . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER2 . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_PLAYOFF . '#ms');
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
	 * Test participation method as an admin
	 *
	 * @return void
	 */
	public function testParticipationAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as a manager
	 *
	 * @return void
	 */
	public function testParticipationAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as a coordinator
	 *
	 * @return void
	 */
	public function testParticipationAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as a captain
	 *
	 * @return void
	 */
	public function testParticipationAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as a player
	 *
	 * @return void
	 */
	public function testParticipationAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method as someone else
	 *
	 * @return void
	 */
	public function testParticipationAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test participation method without being logged in
	 *
	 * @return void
	 */
	public function testParticipationAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins can add new leagues anywhere
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<option value="1" selected="selected">Club</option>#ms');
		$this->assertResponseRegExp('#<option value="2">Sub</option>#ms');

		// If a league ID is given, we will clone that league
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<input type="text" name="name"[^>]*value="Monday Night"#ms');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers can add new leagues in their own affiliate, but not others
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#<input type="hidden" name="affiliate_id" value="1"/>#ms');
		$this->assertResponseNotRegExp('#<option value="2">Sub</option>#ms');

		$this->assertAccessRedirect(['controller' => 'Leagues', 'action' => 'add', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
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
		// Others cannot add new leagues
		$this->assertAccessRedirect(['controller' => 'Leagues', 'action' => 'add'], PERSON_ID_PLAYER);
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
		// Admins can edit leagues anywhere
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers can edit leagues in their own affiliate, but not others
		$this->assertAccessOk(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);
		$this->assertAccessRedirect(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
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
		// Others cannot edit leagues
		$this->assertAccessRedirect(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
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
	 * Test add_division method as an admin
	 *
	 * @return void
	 */
	public function testAddDivisionAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_division method as a manager
	 *
	 * @return void
	 */
	public function testAddDivisionAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_division method as a coordinator
	 *
	 * @return void
	 */
	public function testAddDivisionAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_division method as a captain
	 *
	 * @return void
	 */
	public function testAddDivisionAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_division method as a player
	 *
	 * @return void
	 */
	public function testAddDivisionAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_division method as someone else
	 *
	 * @return void
	 */
	public function testAddDivisionAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_division method without being logged in
	 *
	 * @return void
	 */
	public function testAddDivisionAsAnonymous() {
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

		// Admins are allowed to delete leagues
		$this->assertAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_WEDNESDAY],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Leagues', 'action' => 'index'],
			'The league has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Leagues', 'action' => 'index'],
			'#The following records reference this league, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete leagues in their affiliate
		$this->assertAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_WEDNESDAY],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Leagues', 'action' => 'index'],
			'The league has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_SUNDAY_SUB],
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
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as a manager
	 *
	 * @return void
	 */
	public function testScheduleAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as a coordinator
	 *
	 * @return void
	 */
	public function testScheduleAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as a captain
	 *
	 * @return void
	 */
	public function testScheduleAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as a player
	 *
	 * @return void
	 */
	public function testScheduleAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method as someone else
	 *
	 * @return void
	 */
	public function testScheduleAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedule method without being logged in
	 *
	 * @return void
	 */
	public function testScheduleAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
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

}
