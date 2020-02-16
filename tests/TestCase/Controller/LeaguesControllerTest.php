<?php
namespace App\Test\TestCase\Controller;

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
			'app.badges',
				'app.badges_people',
			'app.settings',
		'app.i18n',
	];

	/**
	 * Test index method
	 *
	 * @return void
	 */
	public function testIndex() {
		$this_year = date('Y');
		$last_year = $this_year - 1;

		// Admins are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], PERSON_ID_ADMIN);
		$this->assertResponseContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/edit?league=' . LEAGUE_ID_SUNDAY_SUB);
		$this->assertResponseContains('/leagues/delete?league=' . LEAGUE_ID_SUNDAY_SUB);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		// Managers are allowed to see the index
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], PERSON_ID_MANAGER);
		$this->assertResponseContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_SUNDAY_SUB);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_SUNDAY_SUB);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		// Others are allowed to see the index, but not edit anything
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'index'], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);

		$this->assertGetAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'index']);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/index?year=' . $this_year);
		$this->assertResponseContains('/leagues/index?year=' . $last_year);
	}

	/**
	 * Test summary method
	 *
	 * @return void
	 */
	public function testSummary() {
		// Admins are allowed to view the summary
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'summary'], PERSON_ID_ADMIN);

		// Managers are allowed to view the summary
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'summary'], PERSON_ID_MANAGER);

		// Others are not allowed to view the summary
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'summary'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'summary'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'summary'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'summary'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'summary']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view leagues, with full edit and delete permissions
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertResponseContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('/leagues/edit?league=' . LEAGUE_ID_SUNDAY_SUB);
		$this->assertResponseContains('/leagues/delete?league=' . LEAGUE_ID_SUNDAY_SUB);

		// Managers are allowed to view, edit and delete leagues in their affiliate
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);
		$this->assertResponseContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_SUNDAY_SUB);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_SUNDAY_SUB);

		// Others are allowed to view leagues, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);

		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_VISITOR);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);

		$this->assertGetAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'view', 'league' => LEAGUE_ID_MONDAY]);
		$this->assertResponseNotContains('/leagues/edit?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseNotContains('/leagues/delete?league=' . LEAGUE_ID_MONDAY);
	}

	/**
	 * Test tooltip method
	 *
	 * @return void
	 */
	public function testTooltip() {
		// Anyone is allowed to view league tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertResponseContains('/leagues\\/view?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER2);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_LADDER2);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER2);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_PLAYOFF);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_PLAYOFF);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_PLAYOFF);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => 0],
			PERSON_ID_ADMIN, ['controller' => 'Leagues', 'action' => 'index'],
			'Invalid league.');

		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);
		$this->assertResponseContains('/leagues\\/view?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER2);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_LADDER2);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER2);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_PLAYOFF);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_PLAYOFF);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_PLAYOFF);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY],
			PERSON_ID_COORDINATOR);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY],
			PERSON_ID_CAPTAIN);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
		$this->assertResponseContains('/leagues\\/view?league=' . LEAGUE_ID_MONDAY);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_LADDER2);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_LADDER2);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_LADDER2);
		$this->assertResponseContains('/divisions\\/view?division=' . DIVISION_ID_MONDAY_PLAYOFF);
		$this->assertResponseContains('/divisions\\/schedule?division=' . DIVISION_ID_MONDAY_PLAYOFF);
		$this->assertResponseContains('/divisions\\/standings?division=' . DIVISION_ID_MONDAY_PLAYOFF);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY],
			PERSON_ID_VISITOR);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'tooltip', 'league' => LEAGUE_ID_MONDAY]);
	}

	/**
	 * Test participation method
	 *
	 * @return void
	 */
	public function testParticipation() {
		// Admins are allowed to view the participation report
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'participation', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);

		// Managers are allowed to view the participation report
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'participation', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);

		// Others are not allowed to view the participation report
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'participation', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'participation', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'participation', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'participation', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'participation', 'league' => LEAGUE_ID_MONDAY]);
	}

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add new leagues anywhere
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'add'], PERSON_ID_ADMIN);
		$this->assertResponseContains('<option value="1" selected="selected">Club</option>');
		$this->assertResponseContains('<option value="2">Sub</option>');

		// If a league ID is given, we will clone that league
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'add', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<input type="text" name="name"[^>]*value="Monday Night"#ms');
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add new leagues in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'add'], PERSON_ID_MANAGER);
		$this->assertResponseContains('<input type="hidden" name="affiliate_id" value="1"/>');
		$this->assertResponseNotContains('<option value="2">Sub</option>');

		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'add', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'add'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'add'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'add'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'add'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'add']);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit leagues anywhere
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit leagues in their own affiliate, but not others
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Coordinators are allowed to edit leagues where they coordinate all the divisions
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_THURSDAY], PERSON_ID_COORDINATOR);

		// But not leagues where they coordinate only some divisions
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit leagues
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'edit', 'league' => LEAGUE_ID_MONDAY]);
	}

	/**
	 * Test add_division method as an admin
	 *
	 * @return void
	 */
	public function testAddDivisionAsAdmin() {
		// Admins are allowed to add division
		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'add_division'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_division method as a manager
	 *
	 * @return void
	 */
	public function testAddDivisionAsManager() {
		// Managers are allowed to add divisions
		$this->assertGetAjaxAsAccessOk(['controller' => 'Leagues', 'action' => 'add_division'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_division method as others
	 *
	 * @return void
	 */
	public function testAddDivisionAsOthers() {
		// Coordinators are not allowed to add divisions
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Leagues', 'action' => 'add_division'], PERSON_ID_COORDINATOR);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Leagues', 'action' => 'add_division'], PERSON_ID_CAPTAIN);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Leagues', 'action' => 'add_division'], PERSON_ID_PLAYER);
		$this->assertGetAjaxAsAccessDenied(['controller' => 'Leagues', 'action' => 'add_division'], PERSON_ID_VISITOR);
		$this->assertGetAjaxAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'add_division']);
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
		$this->assertPostAsAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_WEDNESDAY],
			PERSON_ID_ADMIN, [], ['controller' => 'Leagues', 'action' => 'index'],
			'The league has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY],
			PERSON_ID_ADMIN, [], ['controller' => 'Leagues', 'action' => 'index'],
			'#The following records reference this league, so it cannot be deleted#');
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete leagues in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_WEDNESDAY],
			PERSON_ID_MANAGER, [], ['controller' => 'Leagues', 'action' => 'index'],
			'The league has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_SUNDAY_SUB],
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

		// Others are not allowed to delete leagues
		$this->assertPostAsAccessDenied(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_WEDNESDAY],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_WEDNESDAY],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_WEDNESDAY],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_WEDNESDAY],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'delete', 'league' => LEAGUE_ID_WEDNESDAY]);
	}

	/**
	 * Test schedule method
	 *
	 * @return void
	 */
	public function testSchedule() {
		// Anyone is allowed to see the schedule
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_MONDAY]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test standings method
	 *
	 * @return void
	 */
	public function testStandings() {
		// Anyone is allowed to see the standings
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'standings', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Leagues', 'action' => 'standings', 'league' => LEAGUE_ID_MONDAY]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test slots method
	 *
	 * @return void
	 */
	public function testSlots() {
		// Admins are allowed to see the slots report
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'slots', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_ADMIN);

		// Managers are allowed to see the slots report
		$this->assertGetAsAccessOk(['controller' => 'Leagues', 'action' => 'slots', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_MANAGER);

		// Others are not allowed to see the slots report
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'slots', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'slots', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'slots', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Leagues', 'action' => 'slots', 'league' => LEAGUE_ID_MONDAY], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Leagues', 'action' => 'slots', 'league' => LEAGUE_ID_MONDAY]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

}
