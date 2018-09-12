<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;

/**
 * App\Controller\TeamsController Test Case
 */
class TeamsControllerTest extends ControllerTestCase {

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
						'app.team_events',
						'app.teams_facilities',
					'app.divisions_days',
					'app.divisions_people',
					'app.game_slots',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.score_entries',
						'app.spirit_entries',
						'app.incidents',
						'app.stats',
				'app.leagues_stat_types',
			'app.attendances',
			'app.franchises',
				'app.franchises_people',
				'app.franchises_teams',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
			'app.badges',
				'app.badges_people',
			'app.mailing_lists',
				'app.newsletters',
			'app.activity_logs',
			'app.notes',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
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
	 * Test letter method as an admin
	 *
	 * @return void
	 */
	public function testLetterAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as a manager
	 *
	 * @return void
	 */
	public function testLetterAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as a coordinator
	 *
	 * @return void
	 */
	public function testLetterAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as a captain
	 *
	 * @return void
	 */
	public function testLetterAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as a player
	 *
	 * @return void
	 */
	public function testLetterAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method as someone else
	 *
	 * @return void
	 */
	public function testLetterAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test letter method without being logged in
	 *
	 * @return void
	 */
	public function testLetterAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as an admin
	 *
	 * @return void
	 */
	public function testJoinAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as a manager
	 *
	 * @return void
	 */
	public function testJoinAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as a coordinator
	 *
	 * @return void
	 */
	public function testJoinAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as a captain
	 *
	 * @return void
	 */
	public function testJoinAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as a player
	 *
	 * @return void
	 */
	public function testJoinAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method as someone else
	 *
	 * @return void
	 */
	public function testJoinAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test join method without being logged in
	 *
	 * @return void
	 */
	public function testJoinAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unassigned method as an admin
	 *
	 * @return void
	 */
	public function testUnassignedAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unassigned method as a manager
	 *
	 * @return void
	 */
	public function testUnassignedAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unassigned method as a coordinator
	 *
	 * @return void
	 */
	public function testUnassignedAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unassigned method as a captain
	 *
	 * @return void
	 */
	public function testUnassignedAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unassigned method as a player
	 *
	 * @return void
	 */
	public function testUnassignedAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unassigned method as someone else
	 *
	 * @return void
	 */
	public function testUnassignedAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unassigned method without being logged in
	 *
	 * @return void
	 */
	public function testUnassignedAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as an admin
	 *
	 * @return void
	 */
	public function testStatisticsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as a manager
	 *
	 * @return void
	 */
	public function testStatisticsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as a coordinator
	 *
	 * @return void
	 */
	public function testStatisticsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as a captain
	 *
	 * @return void
	 */
	public function testStatisticsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as a player
	 *
	 * @return void
	 */
	public function testStatisticsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method as someone else
	 *
	 * @return void
	 */
	public function testStatisticsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test statistics method without being logged in
	 *
	 * @return void
	 */
	public function testStatisticsAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareAffiliateAndCount method as an admin
	 *
	 * @return void
	 */
	public function testCompareAffiliateAndCountAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareAffiliateAndCount method as a manager
	 *
	 * @return void
	 */
	public function testCompareAffiliateAndCountAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareAffiliateAndCount method as a coordinator
	 *
	 * @return void
	 */
	public function testCompareAffiliateAndCountAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareAffiliateAndCount method as a captain
	 *
	 * @return void
	 */
	public function testCompareAffiliateAndCountAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareAffiliateAndCount method as a player
	 *
	 * @return void
	 */
	public function testCompareAffiliateAndCountAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareAffiliateAndCount method as someone else
	 *
	 * @return void
	 */
	public function testCompareAffiliateAndCountAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareAffiliateAndCount method without being logged in
	 *
	 * @return void
	 */
	public function testCompareAffiliateAndCountAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view teams, with full edit permissions
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		// The regexps for edit are all longer here than other places, because there can be simple edit links in help text.
		$this->assertResponseRegExp('#<div><a href="/teams/edit\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/teams/delete\?team=' . TEAM_ID_RED . '#ms');

		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BEARS], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<div><a href="/teams/edit\?team=' . TEAM_ID_BEARS . '#ms');
		$this->assertResponseRegExp('#/teams/delete\?team=' . TEAM_ID_BEARS . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view teams
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#<div><a href="/teams/edit\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/teams/delete\?team=' . TEAM_ID_RED . '#ms');

		// But cannot edit ones in other affiliates
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BEARS], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#<div><a href="/teams/edit\?team=' . TEAM_ID_BEARS . '#ms');
		$this->assertResponseNotRegExp('#/teams/delete\?team=' . TEAM_ID_BEARS . '#ms');
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
		// Captains are allowed to edit their teams
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#<div><a href="/teams/edit\?team=' . TEAM_ID_RED . '#ms');
		// TODO: Test that captains can delete their own teams when the registration module is turned off
		$this->assertResponseNotRegExp('#/teams/delete\?team=' . TEAM_ID_RED . '#ms');
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		// Others are allowed to view teams, but have no edit permissions
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#<div><a href="/teams/edit\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseNotRegExp('#/teams/delete\?team=' . TEAM_ID_RED . '#ms');
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
	 * Test numbers method as an admin
	 *
	 * @return void
	 */
	public function testNumbersAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as a manager
	 *
	 * @return void
	 */
	public function testNumbersAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as a coordinator
	 *
	 * @return void
	 */
	public function testNumbersAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as a captain
	 *
	 * @return void
	 */
	public function testNumbersAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as a player
	 *
	 * @return void
	 */
	public function testNumbersAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method as someone else
	 *
	 * @return void
	 */
	public function testNumbersAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test numbers method without being logged in
	 *
	 * @return void
	 */
	public function testNumbersAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stats method as an admin
	 *
	 * @return void
	 */
	public function testStatsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stats method as a manager
	 *
	 * @return void
	 */
	public function testStatsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stats method as a coordinator
	 *
	 * @return void
	 */
	public function testStatsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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
		$this->markTestIncomplete('Not implemented yet.');
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
	 * Test stat_sheet method as an admin
	 *
	 * @return void
	 */
	public function testStatSheetAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stat_sheet method as a manager
	 *
	 * @return void
	 */
	public function testStatSheetAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stat_sheet method as a coordinator
	 *
	 * @return void
	 */
	public function testStatSheetAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stat_sheet method as a captain
	 *
	 * @return void
	 */
	public function testStatSheetAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stat_sheet method as a player
	 *
	 * @return void
	 */
	public function testStatSheetAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stat_sheet method as someone else
	 *
	 * @return void
	 */
	public function testStatSheetAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test stat_sheet method without being logged in
	 *
	 * @return void
	 */
	public function testStatSheetAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tooltip method as an admin
	 *
	 * @return void
	 */
	public function testTooltipAsAdmin() {
		// Everyone is allowed to view team tooltips
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/teams\\\\/view\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/teams\\\\/schedule\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'tooltip', 'team' => 0],
			PERSON_ID_ADMIN, 'getajax', [], ['controller' => 'Teams', 'action' => 'index'],
			'Invalid team.');
	}

	/**
	 * Test tooltip method as a manager
	 *
	 * @return void
	 */
	public function testTooltipAsManager() {
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/teams\\\\/view\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/teams\\\\/schedule\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
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
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'tooltip', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER, 'getajax');
		$this->assertResponseRegExp('#/teams\\\\/view\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/teams\\\\/schedule\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/standings\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/view\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
		$this->assertResponseRegExp('#/divisions\\\\/schedule\?division=' . DIVISION_ID_MONDAY_LADDER . '#ms');
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
	 * Test note method as an admin
	 *
	 * @return void
	 */
	public function testNoteAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as a manager
	 *
	 * @return void
	 */
	public function testNoteAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as a coordinator
	 *
	 * @return void
	 */
	public function testNoteAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as a captain
	 *
	 * @return void
	 */
	public function testNoteAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as a player
	 *
	 * @return void
	 */
	public function testNoteAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method as someone else
	 *
	 * @return void
	 */
	public function testNoteAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test note method without being logged in
	 *
	 * @return void
	 */
	public function testNoteAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as an admin
	 *
	 * @return void
	 */
	public function testDeleteNoteAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a manager
	 *
	 * @return void
	 */
	public function testDeleteNoteAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteNoteAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a captain
	 *
	 * @return void
	 */
	public function testDeleteNoteAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a player
	 *
	 * @return void
	 */
	public function testDeleteNoteAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as someone else
	 *
	 * @return void
	 */
	public function testDeleteNoteAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteNoteAsAnonymous() {
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

		// Admins are allowed to delete teams
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Teams', 'action' => 'index'],
			'The team has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_RED],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Teams', 'action' => 'index'],
			'#The following records reference this team, so it cannot be deleted#', 'Flash.flash.0.message');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers can delete teams in their affiliate
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_OAKS],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Teams', 'action' => 'index'],
			'The team has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_BEARS],
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
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Team owners can delete their own teams
		/* TODO: Not at this time
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, 'post', [], ['controller' => 'Teams', 'action' => 'index'],
			'The team has been deleted.', 'Flash.flash.0.message');
		*/

		// But not others
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'delete', 'team' => TEAM_ID_BLUE],
			PERSON_ID_CAPTAIN, 'post');
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
	 * Test move method as an admin
	 *
	 * @return void
	 */
	public function testMoveAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test move method as a manager
	 *
	 * @return void
	 */
	public function testMoveAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test move method as a coordinator
	 *
	 * @return void
	 */
	public function testMoveAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test move method as a captain
	 *
	 * @return void
	 */
	public function testMoveAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test move method as a player
	 *
	 * @return void
	 */
	public function testMoveAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test move method as someone else
	 *
	 * @return void
	 */
	public function testMoveAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test move method without being logged in
	 *
	 * @return void
	 */
	public function testMoveAsAnonymous() {
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
	 * Test attendance method as an admin
	 *
	 * @return void
	 */
	public function testAttendanceAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance method as a manager
	 *
	 * @return void
	 */
	public function testAttendanceAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance method as a coordinator
	 *
	 * @return void
	 */
	public function testAttendanceAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance method as a captain
	 *
	 * @return void
	 */
	public function testAttendanceAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance method as a player
	 *
	 * @return void
	 */
	public function testAttendanceAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance method as someone else
	 *
	 * @return void
	 */
	public function testAttendanceAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance method without being logged in
	 *
	 * @return void
	 */
	public function testAttendanceAsAnonymous() {
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
	 * Test add_player method as an admin
	 *
	 * @return void
	 */
	public function testAddPlayerAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add players to teams
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_OAKS], PERSON_ID_ADMIN);

		// Try the search page
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, 'post', [
				'affiliate_id' => '1',
				'first_name' => '',
				'last_name' => 'player',
				'sort' => 'last_name',
				'direction' => 'asc',
			]);
		$return = urlencode(\App\Lib\base64_url_encode('/teams/add_player?team=' . TEAM_ID_OAKS));
		$this->assertResponseRegExp('#/teams/roster_add\?person=' . PERSON_ID_PLAYER . '&amp;return=' . $return . '&amp;team=' . TEAM_ID_OAKS . '#ms');
	}

	/**
	 * Test add_player method as a manager
	 *
	 * @return void
	 */
	public function testAddPlayerAsManager() {
		// Managers are allowed to add players to teams
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_OAKS], PERSON_ID_MANAGER);

		// But not teams in other affiliates
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_LIONS], PERSON_ID_MANAGER);
	}

	/**
	 * Test add_player method as a coordinator
	 *
	 * @return void
	 */
	public function testAddPlayerAsCoordinator() {
		// Coordinators are allowed to add players to teams in their divisions
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);

		// But not other divisions
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_MAPLES], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test add_player method as a captain
	 *
	 * @return void
	 */
	public function testAddPlayerAsCaptain() {
		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are allowed to add players to their own teams
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);

		// But not other teams
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_MAPLES], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test add_player method as a player
	 *
	 * @return void
	 */
	public function testAddPlayerAsPlayer() {
		// Others are not allowed to add players to teams
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'add_player', 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
	}

	/**
	 * Test add_player method as someone else
	 *
	 * @return void
	 */
	public function testAddPlayerAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_player method without being logged in
	 *
	 * @return void
	 */
	public function testAddPlayerAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_team method as an admin
	 *
	 * @return void
	 */
	public function testAddFromTeamAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_team method as a manager
	 *
	 * @return void
	 */
	public function testAddFromTeamAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_team method as a coordinator
	 *
	 * @return void
	 */
	public function testAddFromTeamAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_team method as a captain
	 *
	 * @return void
	 */
	public function testAddFromTeamAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are allowed to add players from their past teams
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, 'post', [
				'team' => TEAM_ID_RED_PAST,
			]);
		$this->assertResponseRegExp('#<span id="people_person_' .  PERSON_ID_CHILD . '" class="trigger">Carla Child</span>#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_CHILD . '\]\[role\]" value="captain" id="player-' .  PERSON_ID_CHILD . '-role-captain">\s*Captain#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_CHILD . '\]\[position\]" value="unspecified" id="player-' .  PERSON_ID_CHILD . '-position-unspecified" checked="checked">\s*Unspecified#ms');
		$this->assertResponseRegExp('#<span id="people_person_' .  PERSON_ID_MANAGER . '" class="trigger">Mary Manager</span>#ms');
		// The manager is not a player, so doesn't get player options, just coach and none
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_MANAGER . '\]\[role\]" value="coach" id="player-' .  PERSON_ID_MANAGER . '-role-coach">\s*Non-playing coach#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_MANAGER . '\]\[position\]" value="unspecified" id="player-' .  PERSON_ID_MANAGER . '-position-unspecified" checked="checked">\s*Unspecified#ms');

		// Submit the add form
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'add_from_team', 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, 'post', [
				'team' => TEAM_ID_RED_PAST,
				'player' => [
					PERSON_ID_CHILD => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					PERSON_ID_MANAGER => [
						'role' => 'none',
						'position' => 'unspecified',
					],
				],
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'Invitation has been sent to Carla Child.', 'Flash.flash.0.message');

		// Confirm the roster email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		$this->assertRegExp('#Subject: Invitation to join Red#ms', $messages[0]);
		$this->assertRegExp('#Crystal Captain has invited you to join the roster of the Test Zuluru Affiliate team Red as a Regular player.#ms', $messages[0]);
		$this->assertRegExp('#Red plays in the Competitive division of the Monday Night league, which operates on Monday.#ms', $messages[0]);
		$this->assertRegExp('#More details about Red may be found at\s*' . Configure::read('App.fullBaseUrl') . '/teams/view\?team=' . TEAM_ID_RED . '#ms', $messages[0]);

		// Make sure they were added successfully
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#Regular player \[invited:#ms');
		// There is no accept link, because the membership is not yet paid for
		$this->assertResponseNotRegExp('#/teams/roster_accept\?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_CHILD . '#ms');
		$this->assertResponseRegExp('#/teams/roster_decline\?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_CHILD . '#ms');
		$this->assertResponseNotRegExp('#/teams/roster_accept\?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_MANAGER . '#ms');
		$this->assertResponseNotRegExp('#/teams/roster_decline\?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_MANAGER . '#ms');
	}

	/**
	 * Test add_from_team method as a player
	 *
	 * @return void
	 */
	public function testAddFromTeamAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_team method as someone else
	 *
	 * @return void
	 */
	public function testAddFromTeamAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_team method without being logged in
	 *
	 * @return void
	 */
	public function testAddFromTeamAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_event method as an admin
	 *
	 * @return void
	 */
	public function testAddFromEventAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add players from events
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, 'post', [
				'event' => EVENT_ID_MEMBERSHIP,
			]);
		$this->assertResponseRegExp('#<span id="people_person_' .  PERSON_ID_PLAYER . '" class="trigger">Pam Player</span>#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_PLAYER . '\]\[role\]" value="captain" id="player-' .  PERSON_ID_PLAYER . '-role-captain">\s*Captain#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_PLAYER . '\]\[position\]" value="unspecified" id="player-' .  PERSON_ID_PLAYER . '-position-unspecified" checked="checked">\s*Unspecified#ms');

		// Submit the add form
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, 'post', [
				'event' => EVENT_ID_MEMBERSHIP,
				'player' => [
					PERSON_ID_PLAYER => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					// Coordinator will not be added; the registration is not paid
					PERSON_ID_COORDINATOR => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					PERSON_ID_CHILD => [
						'role' => 'player',
						'position' => 'unspecified',
					],
					// Captain2 will not be added; the role is "none"
					PERSON_ID_CAPTAIN2 => [
						'role' => 'none',
						'position' => 'unspecified',
					],
				],
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS],
			'Pam Player and Carla Child have been added to the roster.', 'Flash.flash.0.message');

		// Confirm the roster email
		$messages = Configure::read('test_emails');
		$this->assertEquals(2, count($messages));

		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Amy Administrator&quot; &lt;amy@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		$this->assertRegExp('#Subject: You have been added to Oaks#ms', $messages[0]);
		$this->assertRegExp('#You have been added to the roster of the Test Zuluru Affiliate team Oaks as a Regular player.#ms', $messages[0]);
		$this->assertRegExp('#Oaks plays in the Intermediate division of the Tuesday Night league, which operates on Tuesday.#ms', $messages[0]);
		$this->assertRegExp('#More details about Oaks may be found at\s*' . Configure::read('App.fullBaseUrl') . '/teams/view\?team=' . TEAM_ID_OAKS . '#ms', $messages[0]);

		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[1]);
		$this->assertRegExp('#Reply-To: &quot;Amy Administrator&quot; &lt;amy@zuluru.org&gt;#ms', $messages[1]);
		// To line still says Pam, because the child has no email address listed
		$this->assertRegExp('#To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;#ms', $messages[1]);
		$this->assertNotRegExp('#CC: #ms', $messages[1]);
		$this->assertRegExp('#Subject: You have been added to Oaks#ms', $messages[1]);
		$this->assertRegExp('#You have been added to the roster of the Test Zuluru Affiliate team Oaks as a Regular player.#ms', $messages[1]);
		$this->assertRegExp('#Oaks plays in the Intermediate division of the Tuesday Night league, which operates on Tuesday.#ms', $messages[1]);
		$this->assertRegExp('#More details about Oaks may be found at\s*' . Configure::read('App.fullBaseUrl') . '/teams/view\?team=' . TEAM_ID_OAKS . '#ms', $messages[1]);

		// Make sure they were added successfully
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#Regular player#ms');
		$this->assertResponseRegExp('#/teams/roster_role\?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseRegExp('#/teams/roster_role\?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_CHILD . '#ms');
		$this->assertResponseNotRegExp('#/teams/roster_role\?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_COORDINATOR . '#ms');
		$this->assertResponseNotRegExp('#/teams/roster_role\?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_CAPTAIN2 . '#ms');
	}

	/**
	 * Test add_from_event method as a manager
	 *
	 * @return void
	 */
	public function testAddFromEventAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to add players from events
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_OAKS],
			PERSON_ID_MANAGER, 'post', [
				'event' => EVENT_ID_MEMBERSHIP,
			]);
		$this->assertResponseRegExp('#<span id="people_person_' .  PERSON_ID_PLAYER . '" class="trigger">Pam Player</span>#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_PLAYER . '\]\[role\]" value="captain" id="player-' .  PERSON_ID_PLAYER . '-role-captain">\s*Captain#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_PLAYER . '\]\[position\]" value="unspecified" id="player-' .  PERSON_ID_PLAYER . '-position-unspecified" checked="checked">\s*Unspecified#ms');

		// But not to teams in other affiliates
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_BEARS],
			PERSON_ID_MANAGER, 'post', [
				'event' => EVENT_ID_LEAGUE_INDIVIDUAL_SUB,
			]);
	}

	/**
	 * Test add_from_event method as a coordinator
	 *
	 * @return void
	 */
	public function testAddFromEventAsCoordinator() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to add players from events to teams in their divisions
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_CHICKADEES],
			PERSON_ID_COORDINATOR, 'post', [
				'event' => EVENT_ID_MEMBERSHIP,
			]);
		$this->assertResponseRegExp('#<span id="people_person_' .  PERSON_ID_PLAYER . '" class="trigger">Pam Player</span>#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_PLAYER . '\]\[role\]" value="captain" id="player-' .  PERSON_ID_PLAYER . '-role-captain">\s*Captain#ms');
		$this->assertResponseRegExp('#<input type="radio" name="player\[' .  PERSON_ID_PLAYER . '\]\[position\]" value="unspecified" id="player-' .  PERSON_ID_PLAYER . '-position-unspecified" checked="checked">\s*Unspecified#ms');

		// But not other divisions
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_OAKS],
			PERSON_ID_COORDINATOR, 'post', [
				'event' => EVENT_ID_MEMBERSHIP,
			]);
	}

	/**
	 * Test add_from_event method as a captain
	 *
	 * @return void
	 */
	public function testAddFromEventAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Others are not allowed to add players from events
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'add_from_event', 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test add_from_event method as a player
	 *
	 * @return void
	 */
	public function testAddFromEventAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_event method as someone else
	 *
	 * @return void
	 */
	public function testAddFromEventAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_from_event method without being logged in
	 *
	 * @return void
	 */
	public function testAddFromEventAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_role method as an admin
	 *
	 * @return void
	 */
	public function testRosterRoleAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_role method as a manager
	 *
	 * @return void
	 */
	public function testRosterRoleAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_role method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterRoleAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_role method as a captain
	 *
	 * @return void
	 */
	public function testRosterRoleAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_CAPTAIN, 'postajax', ['role' => 'substitute'], [],
			'The roster deadline for this division has already passed.');

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_COORDINATOR, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, 'postajax', ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'This person is not on this team.');

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, 'postajax', ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'A player\'s role on a team cannot be changed until they have been approved on the roster.');

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CAPTAIN, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, 'postajax', ['role' => 'substitute'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'All teams must have at least one player as coach or captain.');

		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_CAPTAIN2, 'postajax', ['role' => 'substitute']);
		$this->assertResponseRegExp('#"\\\\/teams\\\\/roster_role\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Substitute player#ms');
	}

	/**
	 * Test roster_role method as a player
	 *
	 * @return void
	 */
	public function testRosterRoleAsPlayer() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_CHILD, 'postajax', ['role' => 'substitute'], [],
			'The roster deadline for this division has already passed.');

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_CHILD, 'postajax', ['role' => 'captain'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BLUE],
			'You do not have permission to set that role.');

		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_role', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_CHILD, 'postajax', ['role' => 'substitute']);
		$this->assertResponseRegExp('#"\\\\/teams\\\\/roster_role\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Substitute player#ms');
	}

	/**
	 * Test roster_role method as someone else
	 *
	 * @return void
	 */
	public function testRosterRoleAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_role method without being logged in
	 *
	 * @return void
	 */
	public function testRosterRoleAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_position method as an admin
	 *
	 * @return void
	 */
	public function testRosterPositionAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_position method as a manager
	 *
	 * @return void
	 */
	public function testRosterPositionAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_position method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterPositionAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_position method as a captain
	 *
	 * @return void
	 */
	public function testRosterPositionAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_CAPTAIN, 'postajax', ['position' => 'handler'], [],
			'The roster deadline for this division has already passed.');

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_COORDINATOR, 'team' => TEAM_ID_RED],
			PERSON_ID_CAPTAIN, 'postajax', ['position' => 'handler'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED],
			'This person is not on this team.');

		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_CAPTAIN2, 'postajax', ['position' => 'handler']);
		$this->assertResponseRegExp('#"\\\\/teams\\\\/roster_position\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Handler#ms');
	}

	/**
	 * Test roster_position method as a player
	 *
	 * @return void
	 */
	public function testRosterPositionAsPlayer() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_CHILD, 'postajax', ['position' => 'handler'], [],
			'The roster deadline for this division has already passed.');

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_CHILD, 'postajax', ['position' => 'xyz'], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_BLUE],
			'That is not a valid position.');

		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_position', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_BLUE],
			PERSON_ID_CHILD, 'postajax', ['position' => 'handler']);
		$this->assertResponseRegExp('#"\\\\/teams\\\\/roster_position\?team=' . TEAM_ID_BLUE . '&amp;person=' . PERSON_ID_CHILD . '.*Handler#ms');
	}

	/**
	 * Test roster_position method as someone else
	 *
	 * @return void
	 */
	public function testRosterPositionAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_position method without being logged in
	 *
	 * @return void
	 */
	public function testRosterPositionAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_add method as an admin
	 *
	 * @return void
	 */
	public function testRosterAddAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add players to teams
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_OAKS], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#action="/teams/roster_add\?person=' . PERSON_ID_PLAYER . '&amp;team=' . TEAM_ID_OAKS . '#ms');

		// Submit an empty add form
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, 'post');
		$this->assertResponseRegExp('#You must select a role for this person.#ms');

		// Submit the add form
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_OAKS],
			PERSON_ID_ADMIN, 'post', [
				'role' => 'player',
				'position' => 'unspecified',
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS], false);

		// Confirm the roster email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Amy Administrator&quot; &lt;amy@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		// TODO: Why is this an invitation, when add_from_event is a direct add?
		$this->assertRegExp('#Subject: Invitation to join Oaks#ms', $messages[0]);
		$this->assertRegExp('#Amy Administrator has invited you to join the roster of the Test Zuluru Affiliate team Oaks as a Regular player.#ms', $messages[0]);
		$this->assertRegExp('#Oaks plays in the Intermediate division of the Tuesday Night league, which operates on Tuesday.#ms', $messages[0]);
		$this->assertRegExp('#More details about Oaks may be found at\s*' . Configure::read('App.fullBaseUrl') . '/teams/view\?team=' . TEAM_ID_OAKS . '#ms', $messages[0]);

		// Make sure they were added successfully
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#Regular player \[invited:#ms');
		$this->assertResponseRegExp('#/teams/roster_accept\?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_PLAYER . '#ms');
		$this->assertResponseRegExp('#/teams/roster_decline\?team=' . TEAM_ID_OAKS . '&amp;person=' . PERSON_ID_PLAYER . '#ms');

		// TODO: Check all the potential emails and different states that can be generated in other situations: add vs invite, admin vs captain, etc.
	}

	/**
	 * Test roster_add method as a manager
	 *
	 * @return void
	 */
	public function testRosterAddAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to add players to teams
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_OAKS], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#action="/teams/roster_add\?person=' . PERSON_ID_CHILD . '&amp;team=' . TEAM_ID_OAKS . '#ms');

		// Submit the add form
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_OAKS],
			PERSON_ID_MANAGER, 'post', [
				'role' => 'player',
				'position' => 'unspecified',
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_OAKS], false);

		// But not teams in other affiliates
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_LIONS], PERSON_ID_MANAGER);
	}

	/**
	 * Test roster_add method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterAddAsCoordinator() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to add players to teams in their divisions
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_MANAGER, 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertResponseRegExp('#action="/teams/roster_add\?person=' . PERSON_ID_MANAGER . '&amp;team=' . TEAM_ID_RED . '#ms');

		// Submit the add form
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_MANAGER, 'team' => TEAM_ID_RED],
			PERSON_ID_COORDINATOR, 'post', [
				'role' => 'player',
				'position' => 'unspecified',
			], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], false);

		// But not other divisions
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_MAPLES], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test roster_add method as a captain
	 *
	 * @return void
	 */
	public function testRosterAddAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Make sure that we're before the roster deadline for captains to add players
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are allowed to add players to their own teams
		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#action="/teams/roster_add\?person=' . PERSON_ID_CHILD . '&amp;team=' . TEAM_ID_RED . '#ms');

		// Submit the add form
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_CHILD, 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN, 'post', [
			'role' => 'player',
			'position' => 'unspecified',
		], ['controller' => 'Teams', 'action' => 'view', 'team' => TEAM_ID_RED], false);

		// But not other teams
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_MAPLES], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test roster_add method as a player
	 *
	 * @return void
	 */
	public function testRosterAddAsPlayer() {
		// Others are not allowed to add players to teams
		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_add', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
	}

	/**
	 * Test roster_add method as someone else
	 *
	 * @return void
	 */
	public function testRosterAddAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_add method without being logged in
	 *
	 * @return void
	 */
	public function testRosterAddAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method as an admin
	 *
	 * @return void
	 */
	public function testRosterRequestAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method as a manager
	 *
	 * @return void
	 */
	public function testRosterRequestAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterRequestAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method as a captain
	 *
	 * @return void
	 */
	public function testRosterRequestAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method as a player
	 *
	 * @return void
	 */
	public function testRosterRequestAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method as someone else
	 *
	 * @return void
	 */
	public function testRosterRequestAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_request method without being logged in
	 *
	 * @return void
	 */
	public function testRosterRequestAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method as an admin
	 *
	 * @return void
	 */
	public function testRosterAcceptAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method as a manager
	 *
	 * @return void
	 */
	public function testRosterAcceptAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterAcceptAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method as a captain
	 *
	 * @return void
	 */
	public function testRosterAcceptAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method as a player
	 *
	 * @return void
	 */
	public function testRosterAcceptAsPlayer() {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_PLAYER, 'getajax', [], null,
			'The roster deadline for this division has already passed.');

		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_accept', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER, 'getajax');
		$this->assertResponseRegExp('#"\\\\/teams\\\\/roster_role\?team=' . TEAM_ID_RED . '&amp;person=' . PERSON_ID_PLAYER . '.*Regular player#ms');
	}

	/**
	 * Test roster_accept method as someone else
	 *
	 * @return void
	 */
	public function testRosterAcceptAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_accept method without being logged in
	 *
	 * @return void
	 */
	public function testRosterAcceptAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method as an admin
	 *
	 * @return void
	 */
	public function testRosterDeclineAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method as a manager
	 *
	 * @return void
	 */
	public function testRosterDeclineAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method as a coordinator
	 *
	 * @return void
	 */
	public function testRosterDeclineAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method as a captain
	 *
	 * @return void
	 */
	public function testRosterDeclineAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method as a player
	 *
	 * @return void
	 */
	public function testRosterDeclineAsPlayer() {
		// Make sure that we're before the roster deadline
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		$this->assertAccessRedirect(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED_PAST],
			PERSON_ID_PLAYER, 'getajax', [], null,
			'The roster deadline for this division has already passed.');

		$this->assertAccessOk(['controller' => 'Teams', 'action' => 'roster_decline', 'person' => PERSON_ID_PLAYER, 'team' => TEAM_ID_RED],
			PERSON_ID_PLAYER, 'getajax');
		$error = [
			'error' => null,
			'content' => '',
			'_message' => null,
		];
		$this->assertEquals(json_encode($error), $this->_response->body());
	}

	/**
	 * Test roster_decline method as someone else
	 *
	 * @return void
	 */
	public function testRosterDeclineAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test roster_decline method without being logged in
	 *
	 * @return void
	 */
	public function testRosterDeclineAsAnonymous() {
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
