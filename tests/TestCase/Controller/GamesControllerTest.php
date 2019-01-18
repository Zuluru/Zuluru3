<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\GamesController Test Case
 */
class GamesControllerTest extends ControllerTestCase {

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
					'app.divisions_days',
					'app.divisions_people',
					'app.game_slots',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.games_allstars',
						'app.score_entries',
						'app.spirit_entries',
						'app.incidents',
						'app.stats',
						'app.score_details',
							'app.score_detail_stats',
				'app.leagues_stat_types',
			'app.attendances',
			'app.franchises',
				'app.franchises_teams',
			'app.badges',
				'app.badges_people',
			'app.mailing_lists',
				'app.newsletters',
			'app.activity_logs',
			'app.notes',
			'app.settings',
	];

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Make sure that we're after the game date
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Admins are allowed to view games, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);
		$this->assertResponseContains('currently rated');
		$this->assertResponseContains('chance to win');
		$this->assertResponseRegExp('#<dt>Game Status</dt>\s*<dd>Normal</dd>#ms');
		$this->assertResponseNotContains('<dt>Round</dt>');
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseContains('Ratings Table');
		$this->assertResponseNotContains('/games/attendance?team=' . TEAM_ID_RED . '&amp;game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/note?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/edit?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/delete?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseNotContains('/games/stats?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseNotContains('<dt>Score Approved By</dt>');
		$this->assertResponseContains('<p>The score of this game has not yet been finalized.</p>');
		$this->assertResponseContains('Score as entered');
		$this->assertResponseRegExp('#<th>Red \(home\)</th>\s*<th>Blue \(away\)</th>#ms');
		$this->assertResponseRegExp('#<td>Home Score</td>\s*<td>17</td>\s*<td>17</td>#ms');
		$this->assertResponseRegExp('#<td>Away Score</td>\s*<td>12</td>\s*<td>12</td>#ms');
		$this->assertResponseRegExp('#<td>Defaulted\?</td>\s*<td>no</td>\s*<td>no</td>#ms');

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_SUB], PERSON_ID_ADMIN);
		$this->assertResponseContains('chance to win');
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseContains('Ratings Table');
		$this->assertResponseContains('/games/edit?game=' . GAME_ID_SUB);
		$this->assertResponseContains('/games/delete?game=' . GAME_ID_SUB);
		$this->assertResponseNotContains('/games/stats?game=' . GAME_ID_SUB);

		// Managers are allowed to view games; the game view won't include a team ID, so no attendance link
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER);
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseContains('Ratings Table');
		$this->assertResponseNotContains('/games/attendance?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/note?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/edit?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/delete?game=' . GAME_ID_LADDER_MATCHED_SCORES);

		// But are not allowed to edit ones in other affiliates
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('Captain Emails');
		$this->assertResponseContains('Ratings Table');
		$this->assertResponseNotContains('/games/edit?game=' . GAME_ID_SUB);
		$this->assertResponseNotContains('/games/delete?game=' . GAME_ID_SUB);

		// Coordinators are allowed to view games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseNotContains('/games/attendance?team=' . TEAM_ID_RED . '&amp;game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/note?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/edit?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/delete?game=' . GAME_ID_LADDER_MATCHED_SCORES);

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('<dt>Round</dt>');
		$this->assertResponseNotContains('Captain Emails');
		// Round robin games don't have ratings tables
		$this->assertResponseNotContains('Ratings Table');
		$this->assertResponseNotContains('/games/edit?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1);
		$this->assertResponseNotContains('/games/delete?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1);

		// But not unpublished ones in divisions they don't run
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_SUB_UNPUBLISHED], PERSON_ID_COORDINATOR);

		// Captains are allowed to view games, perhaps with slightly more permission than players
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('Captain Emails');
		$this->assertResponseContains('/games/attendance?team=' . TEAM_ID_RED . '&amp;game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/note?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseNotContains('/games/edit?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseNotContains('/games/delete?game=' . GAME_ID_LADDER_MATCHED_SCORES);

		// Confirm different output for finalized games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_CAPTAIN);
		$this->assertResponseNotContains('chance to win');
		$this->assertResponseNotContains('Ratings Table');
		$this->assertResponseContains('/games/stats?game=' . GAME_ID_THURSDAY_ROUND_ROBIN);
		$this->assertResponseRegExp('#<dt>Chickadees</dt>\s*<dd>15</dd>#ms');
		$this->assertResponseRegExp('#<dt>Sparrows</dt>\s*<dd>14</dd>#ms');
		$this->assertResponseRegExp('#<dt>Score Approved By</dt>\s*<dd>automatic approval</dd>#ms');

		// Confirm different output for playoff games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_PLAYOFFS], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#<dt>Home Team</dt>\s*<dd>1st seed</dd>#ms');
		$this->assertResponseNotContains('currently rated');
		$this->assertResponseNotContains('chance to win');
		$this->assertResponseNotContains('Captain Emails');
		// Uninitialized playoff games don't have ratings tables
		$this->assertResponseNotContains('Ratings Table');
		$this->assertResponseNotContains('/games/edit?game=' . GAME_ID_PLAYOFFS);
		$this->assertResponseNotContains('/games/delete?game=' . GAME_ID_PLAYOFFS);

		// TODO: All the different options for carbon flips, spirit, rating points, approved by. We need more games, in various states, across all divisions to fully test all of these.
		//$this->assertResponseRegExp('#<dt>Carbon Flip</dt>\s*<dd>Red won</dd>#ms');
		//$this->assertResponseRegExp('#<dt>Rating Points</dt>\s*<dd>.*gain 5 points\s*</dd>#ms');

		// Others are allowed to view games, but have no edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_PLAYER);
		$this->assertResponseNotContains('Captain Emails');
		$this->assertResponseContains('/games/attendance?team=' . TEAM_ID_RED . '&amp;game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseContains('/games/note?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseNotContains('/games/edit?game=' . GAME_ID_LADDER_MATCHED_SCORES);
		$this->assertResponseNotContains('/games/delete?game=' . GAME_ID_LADDER_MATCHED_SCORES);

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES]);

		// No viewing of unpublished games, though
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_SUB_UNPUBLISHED], PERSON_ID_PLAYER);
	}

	/**
	 * Test tooltip method
	 *
	 * @return void
	 */
	public function testTooltip() {
		// Anyone is allowed to view game tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);
		$this->assertResponseContains('/facilities\\/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_BLUE);

		$this->assertGetAjaxAsAccessRedirect(['controller' => 'Games', 'action' => 'tooltip', 'game' => 0],
			PERSON_ID_ADMIN, '/',
			'Invalid game.');

		// Anyone is allowed to view game tooltips
		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_MANAGER);
		$this->assertResponseContains('/facilities\\/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_BLUE);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/facilities\\/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_BLUE);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_CAPTAIN);
		$this->assertResponseContains('/facilities\\/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_BLUE);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_PLAYER);
		$this->assertResponseContains('/facilities\\/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_BLUE);

		$this->assertGetAjaxAsAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_VISITOR);
		$this->assertResponseContains('/facilities\\/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_BLUE);

		$this->assertGetAjaxAnonymousAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES]);
		$this->assertResponseContains('/facilities\/view?facility=' . FACILITY_ID_SUNNYBROOK);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_RED);
		$this->assertResponseContains('/teams\\/view?team=' . TEAM_ID_BLUE);
	}

	/**
	 * Test ratings_table method
	 *
	 * @return void
	 */
	public function testRatingsTable() {
		// Anyone logged in is allowed to view ratings tables
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'ratings_table', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_VISITOR);

		// Others are not allowed to ratings table
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'ratings_table', 'game' => GAME_ID_LADDER_MATCHED_SCORES]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ical method
	 *
	 * @return void
	 */
	public function testIcal() {
		// Can get the ical feed for any game in the future, but not after the division has been closed for a couple weeks
		FrozenDate::setTestNow(new FrozenDate('June 1'));
		$this->assertGetAnonymousAccessOk(['controller' => 'Games', 'action' => 'ical', GAME_ID_LADDER_MATCHED_SCORES, TEAM_ID_RED]);

		FrozenDate::setTestNow(new FrozenDate('October 1'));
		$this->get(['controller' => 'Games', 'action' => 'ical', GAME_ID_LADDER_MATCHED_SCORES, TEAM_ID_RED]);
		$this->assertResponseCode(410);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_SUB], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as a coordinator
	 *
	 * @return void
	 */
	public function testEditAsCoordinator() {
		// Coordinators are allowed to edit games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are not allowed to edit games
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_LADDER_MATCHED_SCORES], [PERSON_ID_CAPTAIN, PERSON_ID_ADMIN]);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'edit', 'game' => GAME_ID_LADDER_MATCHED_SCORES]);
	}

	/**
	 * Test edit_boxscore method as an admin
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsAdmin() {
		// Admins are allowed to edit boxscore
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit_boxscore', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_boxscore method as a manager
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsManager() {
		// Managers are allowed to edit boxscore
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit_boxscore', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_boxscore method as a coordinator
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsCoordinator() {
		// Coordinators are allowed to edit boxscore
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'edit_boxscore', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_boxscore method as others
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsOthers() {
		// Others are not allowed to edit boxscores
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit_boxscore', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit_boxscore', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'edit_boxscore', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'edit_boxscore', 'game' => GAME_ID_LADDER_MATCHED_SCORES]);
	}

	/**
	 * Test delete_score method as an admin
	 *
	 * @return void
	 */
	public function testDeleteScoreAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'delete_score', 'detail' => DETAIL_ID_LADDER_MATCHED_SCORES_START, 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method as a manager
	 *
	 * @return void
	 */
	public function testDeleteScoreAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'delete_score', 'detail' => DETAIL_ID_LADDER_MATCHED_SCORES_START, 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteScoreAsCoordinator() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to delete scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'delete_score', 'detail' => DETAIL_ID_LADDER_MATCHED_SCORES_START, 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method as others
	 *
	 * @return void
	 */
	public function testDeleteScoreAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete scores
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'delete_score', 'detail' => DETAIL_ID_LADDER_MATCHED_SCORES_START, 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_CAPTAIN);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'delete_score', 'detail' => DETAIL_ID_LADDER_MATCHED_SCORES_START, 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_PLAYER);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'delete_score', 'detail' => DETAIL_ID_LADDER_MATCHED_SCORES_START, 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_VISITOR);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Games', 'action' => 'delete_score', 'detail' => DETAIL_ID_LADDER_MATCHED_SCORES_START, 'game' => GAME_ID_LADDER_MATCHED_SCORES]);
	}

	/**
	 * Test add_score method as an admin
	 *
	 * @return void
	 */
	public function testAddScoreAsAdmin() {
		$this->enableCsrfToken();

		// Game date
		$date = (new FrozenDate('last Monday of May'))->addWeeks(2);

		// Admins are allowed to add scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'add_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_ADMIN, ['add_detail' => [
				'team_id' => TEAM_ID_RED,
				'created' => ['year' => $date->year, 'month' => $date->month, 'day' => $date->day, 'hour' => 19, 'minute' => 10],
				'play' => 'Timeout',
			]]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method as a manager
	 *
	 * @return void
	 */
	public function testAddScoreAsManager() {
		$this->enableCsrfToken();

		// Game date
		$date = (new FrozenDate('last Monday of May'))->addWeeks(2);

		// Managers are allowed to add scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'add_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_MANAGER, ['add_detail' => [
				'team_id' => TEAM_ID_RED,
				'created' => ['year' => $date->year, 'month' => $date->month, 'day' => $date->day, 'hour' => 19, 'minute' => 10],
				'play' => 'Timeout',
			]]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method as a coordinator
	 *
	 * @return void
	 */
	public function testAddScoreAsCoordinator() {
		$this->enableCsrfToken();

		// Game date
		$date = (new FrozenDate('last Monday of May'))->addWeeks(2);

		// Coordinators are allowed to add scores
		$this->assertPostAjaxAsAccessOk(['controller' => 'Games', 'action' => 'add_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_COORDINATOR, ['add_detail' => [
				'team_id' => TEAM_ID_RED,
				'created' => ['year' => $date->year, 'month' => $date->month, 'day' => $date->day, 'hour' => 19, 'minute' => 10],
				'play' => 'Timeout',
			]]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method as others
	 *
	 * @return void
	 */
	public function testAddScoreAsOthers() {
		$this->enableCsrfToken();

		// Others are not allowed to add scores
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'add_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_CAPTAIN);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'add_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_PLAYER);
		$this->assertPostAjaxAsAccessDenied(['controller' => 'Games', 'action' => 'add_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_VISITOR);
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Games', 'action' => 'add_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES]);
	}

	/**
	 * Test note method as an admin
	 *
	 * @return void
	 */
	public function testNoteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);

		// Empty notes don't get added
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_ADMIN, [
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => '',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], 'You entered no text, so no note was added.');

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_ADMIN, [
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_ADMIN, [
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
		// And the old one is still there
		$this->assertResponseContains('Admin note from admin about game.');

		// Check the manager can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a manager
	 *
	 * @return void
	 */
	public function testNoteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_MANAGER, [
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all admins to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_MANAGER, [
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'visibility' => VISIBILITY_ADMIN,
				'note' => 'This is an admin note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');

		// Check the admin can also see the admin one
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);
		$this->assertResponseNotContains('This is a private note.');
		$this->assertResponseContains('This is an admin note.');
	}

	/**
	 * Test note method as a coordinator
	 *
	 * @return void
	 */
	public function testNoteAsCoordinator() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_COORDINATOR);

		// Add a private note
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_COORDINATOR, [
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'visibility' => VISIBILITY_PRIVATE,
				'note' => 'This is a private note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Add a note for all coordinators to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_COORDINATOR, [
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'visibility' => VISIBILITY_COORDINATOR,
				'note' => 'This is a coordinator note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], 'The note has been saved.');

		// Confirm there was no notification email
		$this->assertEmpty(Configure::read('test_emails'));

		// Make sure they were added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('This is a private note.');
		$this->assertResponseContains('This is a coordinator note.');
	}

	/**
	 * Test note method as a captain
	 *
	 * @return void
	 */
	public function testNoteAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Captains are allowed to add notes
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_CAPTAIN);

		// Add a note for all captains to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_FINALIZED_HOME_WIN],
			PERSON_ID_CAPTAIN4, [
				'game_id' => GAME_ID_LADDER_FINALIZED_HOME_WIN,
				'visibility' => VISIBILITY_CAPTAINS,
				'note' => 'This is a captain note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_FINALIZED_HOME_WIN], 'The note has been saved.');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Carl Captain&quot; &lt;carl@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Carolyn Captain&quot; &lt;carolyn@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Yellow game note', $messages[0]);
		$this->assertRegExp('#Carl Captain has added a note.*This is a captain note\.#ms', $messages[0]);

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_FINALIZED_HOME_WIN], PERSON_ID_CAPTAIN4);
		$this->assertResponseContains('This is a captain note.');

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_CANCELLED], [PERSON_ID_PLAYER, PERSON_ID_CHILD]);
		$this->assertResponseNotContains('This is a captain note.');

		// Add a note for the team to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_CANCELLED],
			PERSON_ID_CAPTAIN2, [
				'game_id' => GAME_ID_LADDER_CANCELLED,
				'visibility' => VISIBILITY_TEAM,
				'note' => 'This is a team note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_CANCELLED], 'The note has been saved.');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;', $messages[0]);
		// Children don't have their own email addresses; the email goes to the parent
		$this->assertContains('To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Blue game note', $messages[0]);
		$this->assertRegExp('#Chuck Captain has added a note.*This is a team note\.#ms', $messages[0]);

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_CANCELLED], PERSON_ID_CAPTAIN2);
		$this->assertResponseContains('This is a team note.');

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_CANCELLED], [PERSON_ID_PLAYER, PERSON_ID_CHILD]);
		$this->assertResponseContains('This is a team note.');
	}

	/**
	 * Test note method as a player
	 *
	 * @return void
	 */
	public function testNoteAsPlayer() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Players are only allowed to add notes on games they are playing in
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_PLAYER);
		$this->assertGetAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MISMATCHED_SCORES],
			PERSON_ID_PLAYER, ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MISMATCHED_SCORES],
			'You are not on the roster of a team playing in this game.');

		// Add a note for all captains to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_PLAYER, [
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'visibility' => VISIBILITY_CAPTAINS,
				'note' => 'This is a captain note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], 'The note has been saved.');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Red game note', $messages[0]);
		$this->assertRegExp('#Pam Player has added a note.*This is a captain note\.#ms', $messages[0]);

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_PLAYER);
		$this->assertResponseContains('This is a captain note.');

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('This is a captain note.');

		// Add a note for the team to see
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_PLAYER, [
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'visibility' => VISIBILITY_TEAM,
				'note' => 'This is a team note.',
			], ['action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], 'The note has been saved.');

		// Confirm the notification email
		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Pam Player&quot; &lt;pam@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Carolyn Captain&quot; &lt;carolyn@zuluru.org&gt;, &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Red game note', $messages[0]);
		$this->assertRegExp('#Pam Player has added a note.*This is a team note\.#ms', $messages[0]);

		// Make sure it was added successfully
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_PLAYER);
		$this->assertResponseContains('This is a team note.');

		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_CAPTAIN);
		$this->assertResponseContains('This is a team note.');
	}

	/**
	 * Test note method as others
	 *
	 * @return void
	 */
	public function testNoteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to add notes
		$this->assertGetAsAccessRedirect(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			PERSON_ID_VISITOR, ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'You are not on the roster of a team playing in this game.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'note', 'game' => GAME_ID_LADDER_MATCHED_SCORES]);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as an admin
	 *
	 * @return void
	 */
	public function testDeleteNoteAsAdmin() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Admins are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_ADMIN],
			PERSON_ID_ADMIN, [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'The note has been deleted.');

		// And coordinator notes
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_COORDINATOR],
			PERSON_ID_ADMIN, [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_CAPTAIN],
			PERSON_ID_ADMIN);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_PLAYER],
			PERSON_ID_ADMIN);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_VISITOR],
			PERSON_ID_ADMIN);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a manager
	 *
	 * @return void
	 */
	public function testDeleteNoteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete admin notes
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_ADMIN],
			PERSON_ID_MANAGER, [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'The note has been deleted.');

		// And coordinator notes
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_COORDINATOR],
			PERSON_ID_MANAGER, [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_CAPTAIN],
			PERSON_ID_MANAGER);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_PLAYER],
			PERSON_ID_MANAGER);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_VISITOR],
			PERSON_ID_MANAGER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteNoteAsCoordinator() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to delete coordinator notes
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_COORDINATOR],
			PERSON_ID_COORDINATOR, [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_ADMIN],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_CAPTAIN],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_PLAYER],
			PERSON_ID_COORDINATOR);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_VISITOR],
			PERSON_ID_COORDINATOR);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a captain
	 *
	 * @return void
	 */
	public function testDeleteNoteAsCaptain() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Captains are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_CAPTAIN],
			PERSON_ID_CAPTAIN, [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_ADMIN],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_COORDINATOR],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_PLAYER],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_VISITOR],
			PERSON_ID_CAPTAIN);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as a player
	 *
	 * @return void
	 */
	public function testDeleteNoteAsPlayer() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Players are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_PLAYER],
			PERSON_ID_PLAYER, [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_ADMIN],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_COORDINATOR],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_CAPTAIN],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_VISITOR],
			PERSON_ID_PLAYER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method as someone else
	 *
	 * @return void
	 */
	public function testDeleteNoteAsVisitor() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Visitors are only allowed to delete notes they created
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_VISITOR],
			PERSON_ID_VISITOR, [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'The note has been deleted.');

		// But not other notes
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_ADMIN],
			PERSON_ID_VISITOR);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_COORDINATOR],
			PERSON_ID_VISITOR);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_CAPTAIN],
			PERSON_ID_VISITOR);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_PLAYER],
			PERSON_ID_VISITOR);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_note method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteNoteAsAnonymous() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not only allowed to delete notes they created
		$this->assertPostAnonymousAccessDenied(['controller' => 'Games', 'action' => 'delete_note', 'note' => NOTE_ID_GAME_LADDER_MATCHED_SCORES_PLAYER]);
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

		// Admins are allowed to delete games
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES],
			PERSON_ID_ADMIN, [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'The game has been deleted.');

		// But not ones with dependencies
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY],
			PERSON_ID_ADMIN, [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY],
			'A score has already been submitted for this game.', 'Flash.flash.0.message.0');
		$this->assertEquals('If you are absolutely sure that you want to delete it anyway, {0}. <b>This cannot be undone!</b>', $this->_requestSession->read('Flash.flash.0.message.1'));
		$this->assertEquals(['action' => 'delete', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY, 'force' => true], $this->_requestSession->read('Flash.flash.0.params.replacements.0.target'));

		// Unless we force it
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY, 'force' => true],
			PERSON_ID_ADMIN, [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'The game has been deleted.');

		// Make sure the score for the game was also deleted
		$entries = TableRegistry::get('ScoreEntries');
		$query = $entries->find();
		$this->assertEquals(8, $query->count());
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Managers are allowed to delete games in their affiliate
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES],
			PERSON_ID_MANAGER, [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'The game has been deleted.');

		// But not ones in other affiliates
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_SUB],
			PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsCoordinator() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators are allowed to delete their own games
		$this->assertPostAsAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES],
			PERSON_ID_COORDINATOR, [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'The game has been deleted.');

		// But not ones in other divisions
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1],
			PERSON_ID_COORDINATOR);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Others are not allowed to delete games
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES],
			PERSON_ID_CAPTAIN);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES],
			PERSON_ID_PLAYER);
		$this->assertPostAsAccessDenied(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES],
			PERSON_ID_VISITOR);
		$this->assertPostAnonymousAccessDenied(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES]);
	}

	/**
	 * Test attendance method
	 *
	 * @return void
	 */
	public function testAttendance() {
		// Admins are allowed to see attendance
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);

		// Managers are allowed to see attendance
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);

		// Coordinators are not allowed to see attendance
		$this->assertGetAsAccessRedirect(['controller' => 'Games', 'action' => 'attendance', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED],
			PERSON_ID_COORDINATOR, ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'You are not on the roster of a team playing in this game.');

		// Captains are allowed to see attendance for their games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);

		// Players are allowed to see attendance for their games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);

		// Others are not allowed to see attendance
		$this->assertGetAsAccessRedirect(['controller' => 'Games', 'action' => 'attendance', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED],
			PERSON_ID_VISITOR, ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES],
			'You are not on the roster of a team playing in this game.');
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'attendance', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test add_sub method as an admin
	 *
	 * @return void
	 */
	public function testAddSubAsAdmin() {
		// Admins are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as a manager
	 *
	 * @return void
	 */
	public function testAddSubAsManager() {
		// Managers are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as a coordinator
	 *
	 * @return void
	 */
	public function testAddSubAsCoordinator() {
		// Coordinators are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as a captain
	 *
	 * @return void
	 */
	public function testAddSubAsCaptain() {
		// Captains are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as a player
	 *
	 * @return void
	 */
	public function testAddSubAsPlayer() {
		// Players are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method as someone else
	 *
	 * @return void
	 */
	public function testAddSubAsVisitor() {
		// Visitors are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test add_sub method without being logged in
	 *
	 * @return void
	 */
	public function testAddSubAsAnonymous() {
		// Others are allowed to add sub
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test attendance_change method as an admin
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsAdmin() {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Admins are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED, 'person' => PERSON_ID_CAPTAIN], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a manager
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsManager() {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Managers are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED, 'person' => PERSON_ID_CAPTAIN], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a coordinator
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsCoordinator() {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Coordinators are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED, 'person' => PERSON_ID_CAPTAIN], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a captain
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsCaptain() {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Captains are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as a player
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsPlayer() {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Players are allowed to change attendance
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN3);

		// But not for teams they're only just invited to, or not on at all
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_BLUE], PERSON_ID_PLAYER);

		// And not for long after the game. This game is 3 weeks after the first Monday of May, plus 2 weeks when you can update, plus one more day.
		FrozenTime::setTestNow((new FrozenTime('last Monday of May'))->addDays(36));
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_CAPTAIN3);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendance_change method as others
	 *
	 * @return void
	 */
	public function testAttendanceChangeAsOthers() {
		FrozenTime::setTestNow(new FrozenTime('last Monday of May'));

		// Others are not allowed to change attendance
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'attendance_change', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED]);
	}

	/**
	 * Test stat_sheet method
	 *
	 * @return void
	 */
	public function testStatSheet() {
		// Admins are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stat_sheet', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_ADMIN);

		// Managers are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stat_sheet', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_MANAGER);

		// Coordinators are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stat_sheet', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_COORDINATOR);

		// Captains are allowed to see the stat sheet
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stat_sheet', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_CAPTAIN);

		// Others are not allowed to see the stat sheet
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'stat_sheet', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'stat_sheet', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'stat_sheet', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test live_score method as an admin
	 *
	 * @return void
	 */
	public function testLiveScoreAsAdmin() {
		// Admins are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as a manager
	 *
	 * @return void
	 */
	public function testLiveScoreAsManager() {
		// Managers are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as a coordinator
	 *
	 * @return void
	 */
	public function testLiveScoreAsCoordinator() {
		// Coordinators are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as a captain
	 *
	 * @return void
	 */
	public function testLiveScoreAsCaptain() {
		// Captains are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as a player
	 *
	 * @return void
	 */
	public function testLiveScoreAsPlayer() {
		// Players are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method as someone else
	 *
	 * @return void
	 */
	public function testLiveScoreAsVisitor() {
		// Visitors are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test live_score method without being logged in
	 *
	 * @return void
	 */
	public function testLiveScoreAsAnonymous() {
		// Others are allowed to live score
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as an admin
	 *
	 * @return void
	 */
	public function testScoreUpAsAdmin() {
		// Admins are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as a manager
	 *
	 * @return void
	 */
	public function testScoreUpAsManager() {
		// Managers are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as a coordinator
	 *
	 * @return void
	 */
	public function testScoreUpAsCoordinator() {
		// Coordinators are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as a captain
	 *
	 * @return void
	 */
	public function testScoreUpAsCaptain() {
		// Captains are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as a player
	 *
	 * @return void
	 */
	public function testScoreUpAsPlayer() {
		// Players are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method as someone else
	 *
	 * @return void
	 */
	public function testScoreUpAsVisitor() {
		// Visitors are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_up method without being logged in
	 *
	 * @return void
	 */
	public function testScoreUpAsAnonymous() {
		// Others are allowed to score up
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as an admin
	 *
	 * @return void
	 */
	public function testScoreDownAsAdmin() {
		// Admins are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as a manager
	 *
	 * @return void
	 */
	public function testScoreDownAsManager() {
		// Managers are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as a coordinator
	 *
	 * @return void
	 */
	public function testScoreDownAsCoordinator() {
		// Coordinators are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as a captain
	 *
	 * @return void
	 */
	public function testScoreDownAsCaptain() {
		// Captains are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as a player
	 *
	 * @return void
	 */
	public function testScoreDownAsPlayer() {
		// Players are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method as someone else
	 *
	 * @return void
	 */
	public function testScoreDownAsVisitor() {
		// Visitors are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test score_down method without being logged in
	 *
	 * @return void
	 */
	public function testScoreDownAsAnonymous() {
		// Others are allowed to score down
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as an admin
	 *
	 * @return void
	 */
	public function testTimeoutAsAdmin() {
		// Admins are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as a manager
	 *
	 * @return void
	 */
	public function testTimeoutAsManager() {
		// Managers are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as a coordinator
	 *
	 * @return void
	 */
	public function testTimeoutAsCoordinator() {
		// Coordinators are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as a captain
	 *
	 * @return void
	 */
	public function testTimeoutAsCaptain() {
		// Captains are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as a player
	 *
	 * @return void
	 */
	public function testTimeoutAsPlayer() {
		// Players are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method as someone else
	 *
	 * @return void
	 */
	public function testTimeoutAsVisitor() {
		// Visitors are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test timeout method without being logged in
	 *
	 * @return void
	 */
	public function testTimeoutAsAnonymous() {
		// Others are allowed to timeout
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as an admin
	 *
	 * @return void
	 */
	public function testPlayAsAdmin() {
		// Admins are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as a manager
	 *
	 * @return void
	 */
	public function testPlayAsManager() {
		// Managers are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as a coordinator
	 *
	 * @return void
	 */
	public function testPlayAsCoordinator() {
		// Coordinators are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as a captain
	 *
	 * @return void
	 */
	public function testPlayAsCaptain() {
		// Captains are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as a player
	 *
	 * @return void
	 */
	public function testPlayAsPlayer() {
		// Players are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method as someone else
	 *
	 * @return void
	 */
	public function testPlayAsVisitor() {
		// Visitors are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test play method without being logged in
	 *
	 * @return void
	 */
	public function testPlayAsAnonymous() {
		// Others are allowed to play
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as an admin
	 *
	 * @return void
	 */
	public function testTweetAsAdmin() {
		// Admins are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as a manager
	 *
	 * @return void
	 */
	public function testTweetAsManager() {
		// Managers are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as a coordinator
	 *
	 * @return void
	 */
	public function testTweetAsCoordinator() {
		// Coordinators are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as a captain
	 *
	 * @return void
	 */
	public function testTweetAsCaptain() {
		// Captains are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as a player
	 *
	 * @return void
	 */
	public function testTweetAsPlayer() {
		// Players are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method as someone else
	 *
	 * @return void
	 */
	public function testTweetAsVisitor() {
		// Visitors are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test tweet method without being logged in
	 *
	 * @return void
	 */
	public function testTweetAsAnonymous() {
		// Others are allowed to tweet
		$this->markTestIncomplete('Operation not implemented yet.');
	}

	/**
	 * Test submit_score method as a captain
	 *
	 * @return void
	 */
	public function testSubmitScoreAsCaptain() {
		$url = ['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_LADDER_NO_SCORES, 'team' => TEAM_ID_BLUE];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow((new FrozenTime('last Monday of May'))->addDays(28));
		$this->assertGetAsAccessRedirect($url,
			PERSON_ID_CAPTAIN2, ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_NO_SCORES],
			'That game has not yet occurred!');

		FrozenTime::setTestNow((new FrozenTime('last Monday of May'))->addDays(29));
		$this->assertGetAsAccessOk($url, PERSON_ID_CAPTAIN2);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			PERSON_ID_CAPTAIN2, [
				'score_entries' => [
					[
						'team_id' => TEAM_ID_BLUE,
						'game_id' => GAME_ID_LADDER_NO_SCORES,
						'status' => 'normal',
						'score_for' => '17',
						'score_against' => '10',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'team_id' => TEAM_ID_YELLOW,
						'created_team_id' => TEAM_ID_BLUE,
						'q1' => 2,
						'q2' => 2,
						'q3' => 2,
						'q4' => 2,
						'q5' => 2,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score has been saved. Once your opponent has entered their score, it will be officially posted. The score you have submitted indicates that this game was {0}. If this is incorrect, you can {1} to correct it.'
		);
		$this->assertEquals('a win for your team', $this->_requestSession->read('Flash.flash.0.params.replacements.0.text'));

		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Carl Captain&quot; &lt;carl@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Opponent score submission', $messages[0]);
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertContains("Your opponent has indicated that the game between your team Yellow and Blue, starting at 7:00PM on {$date->format('M d, Y')} in {$date->year} Summer Monday Night Ultimate Competitive was a 17-10 loss for your team.", $messages[0]);

		$game = TableRegistry::get('Games')->get(GAME_ID_LADDER_NO_SCORES, ['contain' => ['ScoreEntries', 'SpiritEntries']]);
		$this->assertFalse($game->isFinalized());

		$this->assertEquals(1, count($game->score_entries));
		$this->assertEquals(PERSON_ID_CAPTAIN2, $game->score_entries[0]->person_id);
		$this->assertEquals(TEAM_ID_BLUE, $game->score_entries[0]->team_id);
		$this->assertEquals(GAME_ID_LADDER_NO_SCORES, $game->score_entries[0]->game_id);
		$this->assertEquals('normal', $game->score_entries[0]->status);
		$this->assertEquals(17, $game->score_entries[0]->score_for);
		$this->assertEquals(10, $game->score_entries[0]->score_against);
		$this->assertEquals(1, $game->score_entries[0]->home_carbon_flip);

		$this->assertEquals(1, count($game->spirit_entries));
		$this->assertEquals(TEAM_ID_YELLOW, $game->spirit_entries[0]->team_id);
		$this->assertEquals(TEAM_ID_BLUE, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals(GAME_ID_LADDER_NO_SCORES, $game->spirit_entries[0]->game_id);
		$this->assertEquals(2, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(2, $game->spirit_entries[0]->q3);
		$this->assertEquals(2, $game->spirit_entries[0]->q4);
		$this->assertEquals(2, $game->spirit_entries[0]->q5);
	}

	/**
	 * Test submit_score method while acting as a captain. All exactly the same assertions as above, just acting as the captain.
	 *
	 * @return void
	 */
	public function testSubmitScoreActingAsCaptain() {
		$url = ['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_LADDER_NO_SCORES, 'team' => TEAM_ID_BLUE];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow((new FrozenTime('last Monday of May'))->addDays(28));
		$this->assertGetAsAccessRedirect($url,
			[PERSON_ID_ADMIN, PERSON_ID_CAPTAIN2], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_NO_SCORES],
			'That game has not yet occurred!');

		FrozenTime::setTestNow((new FrozenTime('last Monday of May'))->addDays(29));
		$this->assertGetAsAccessOk($url, [PERSON_ID_ADMIN, PERSON_ID_CAPTAIN2]);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			[PERSON_ID_ADMIN, PERSON_ID_CAPTAIN2], [
				'score_entries' => [
					[
						'team_id' => TEAM_ID_BLUE,
						'game_id' => GAME_ID_LADDER_NO_SCORES,
						'status' => 'normal',
						'score_for' => '17',
						'score_against' => '10',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'team_id' => TEAM_ID_YELLOW,
						'created_team_id' => TEAM_ID_BLUE,
						'q1' => 2,
						'q2' => 2,
						'q3' => 2,
						'q4' => 2,
						'q5' => 2,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score has been saved. Once your opponent has entered their score, it will be officially posted. The score you have submitted indicates that this game was {0}. If this is incorrect, you can {1} to correct it.'
		);
		$this->assertEquals('a win for your team', $this->_requestSession->read('Flash.flash.0.params.replacements.0.text'));

		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('Reply-To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Carl Captain&quot; &lt;carl@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertContains('Subject: Opponent score submission', $messages[0]);
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertContains("Your opponent has indicated that the game between your team Yellow and Blue, starting at 7:00PM on {$date->format('M d, Y')} in {$date->year} Summer Monday Night Ultimate Competitive was a 17-10 loss for your team.", $messages[0]);

		$game = TableRegistry::get('Games')->get(GAME_ID_LADDER_NO_SCORES, ['contain' => ['ScoreEntries', 'SpiritEntries']]);
		$this->assertFalse($game->isFinalized());

		$this->assertEquals(1, count($game->score_entries));
		$this->assertEquals(PERSON_ID_CAPTAIN2, $game->score_entries[0]->person_id);
		$this->assertEquals(TEAM_ID_BLUE, $game->score_entries[0]->team_id);
		$this->assertEquals(GAME_ID_LADDER_NO_SCORES, $game->score_entries[0]->game_id);
		$this->assertEquals('normal', $game->score_entries[0]->status);
		$this->assertEquals(17, $game->score_entries[0]->score_for);
		$this->assertEquals(10, $game->score_entries[0]->score_against);
		$this->assertEquals(1, $game->score_entries[0]->home_carbon_flip);

		$this->assertEquals(1, count($game->spirit_entries));
		$this->assertEquals(TEAM_ID_YELLOW, $game->spirit_entries[0]->team_id);
		$this->assertEquals(TEAM_ID_BLUE, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals(GAME_ID_LADDER_NO_SCORES, $game->spirit_entries[0]->game_id);
		$this->assertEquals(2, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(2, $game->spirit_entries[0]->q3);
		$this->assertEquals(2, $game->spirit_entries[0]->q4);
		$this->assertEquals(2, $game->spirit_entries[0]->q5);
	}

	/**
	 * Test submit_score method as a captain, matching existing score
	 *
	 * @return void
	 */
	public function testSubmitMatchingScoreAsCaptain() {
		$url = ['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY, 'team' => TEAM_ID_GREEN];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow((new FrozenTime('last Monday of May'))->addDays(29));
		$this->assertGetAsAccessOk($url, PERSON_ID_CAPTAIN3);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			PERSON_ID_CAPTAIN3, [
				'score_entries' => [
					[
						'team_id' => TEAM_ID_GREEN,
						'game_id' => GAME_ID_LADDER_HOME_SCORE_ONLY,
						'status' => 'normal',
						'score_for' => '4',
						'score_against' => '5',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'team_id' => TEAM_ID_RED,
						'created_team_id' => TEAM_ID_GREEN,
						'q1' => 0,
						'q2' => 1,
						'q3' => 2,
						'q4' => 3,
						'q5' => 4,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score agrees with the score submitted by your opponent. It will now be posted as an official game result.'
		);

		$this->assertEmpty(Configure::read('test_emails'));

		$game = TableRegistry::get('Games')->get(GAME_ID_LADDER_HOME_SCORE_ONLY, ['contain' => ['SpiritEntries']]);
		$this->assertTrue($game->isFinalized());
		$this->assertEquals(5, $game->home_score);
		$this->assertEquals(4, $game->away_score);
		$this->assertEquals(2, count($game->spirit_entries));
		$this->assertEquals(TEAM_ID_GREEN, $game->spirit_entries[0]->team_id);
		$this->assertEquals(TEAM_ID_RED, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals(GAME_ID_LADDER_HOME_SCORE_ONLY, $game->spirit_entries[0]->game_id);
		$this->assertEquals(2, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(1, $game->spirit_entries[0]->q3);
		$this->assertEquals(1, $game->spirit_entries[0]->q4);
		$this->assertEquals(2, $game->spirit_entries[0]->q5);
		$this->assertEquals(TEAM_ID_RED, $game->spirit_entries[1]->team_id);
		$this->assertEquals(TEAM_ID_GREEN, $game->spirit_entries[1]->created_team_id);
		$this->assertEquals(GAME_ID_LADDER_HOME_SCORE_ONLY, $game->spirit_entries[1]->game_id);
		$this->assertEquals(0, $game->spirit_entries[1]->q1);
		$this->assertEquals(1, $game->spirit_entries[1]->q2);
		$this->assertEquals(2, $game->spirit_entries[1]->q3);
		$this->assertEquals(3, $game->spirit_entries[1]->q4);
		$this->assertEquals(4, $game->spirit_entries[1]->q5);
	}

	/**
	 * Test submit_score method as a captain, not matching existing score
	 *
	 * @return void
	 */
	public function testSubmitMismatchedScoreAsCaptain() {
		$url = ['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY, 'team' => TEAM_ID_GREEN];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow((new FrozenTime('last Monday of May'))->addDays(29));
		$this->assertGetAsAccessOk($url, PERSON_ID_CAPTAIN3);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			PERSON_ID_CAPTAIN3, [
				'score_entries' => [
					[
						'team_id' => TEAM_ID_GREEN,
						'game_id' => GAME_ID_LADDER_HOME_SCORE_ONLY,
						'status' => 'normal',
						'score_for' => '5',
						'score_against' => '5',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'team_id' => TEAM_ID_RED,
						'created_team_id' => TEAM_ID_GREEN,
						'q1' => 0,
						'q2' => 1,
						'q3' => 2,
						'q4' => 3,
						'q5' => 4,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score doesn\'t agree with the one your opponent submitted. Because of this, the score will not be posted until your coordinator approves it. Alternately, whichever coach or captain made an error can {0}.'
		);

		$messages = Configure::read('test_emails');
		$this->assertEquals(2, count($messages));

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[0]);
		$this->assertContains('To: &quot;Cindy Coordinator&quot; &lt;cindy@zuluru.org&gt;', $messages[0]);
		$this->assertNotContains('CC: ', $messages[0]);
		$this->assertNotContains('Reply-To: ', $messages[0]);
		$this->assertContains('Subject: Score entry mismatch', $messages[0]);
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertContains("The {$date->format('M d, Y')} game between Red and Green in Monday Night has score entries which do not match. You can edit the game here:", $messages[0]);

		$this->assertContains('From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;', $messages[1]);
		$this->assertContains('Reply-To: &quot;Carolyn Captain&quot; &lt;carolyn@zuluru.org&gt;', $messages[1]);
		$this->assertContains('To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;', $messages[1]);
		$this->assertNotContains('CC: ', $messages[1]);
		$this->assertContains('Subject: Opponent score submission', $messages[1]);
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertContains("Your opponent has indicated that the game between your team Red and Green, starting at 7:00PM on {$date->format('M d, Y')} in {$date->year} Summer Monday Night Ultimate Competitive was a 5-5 tie.", $messages[1]);

		$game = TableRegistry::get('Games')->get(GAME_ID_LADDER_HOME_SCORE_ONLY, ['contain' => ['SpiritEntries']]);
		$this->assertFalse($game->isFinalized());
		$this->assertNull($game->home_score);
		$this->assertNull($game->away_score);
		$this->assertEquals(2, count($game->spirit_entries));
		$this->assertEquals(TEAM_ID_GREEN, $game->spirit_entries[0]->team_id);
		$this->assertEquals(TEAM_ID_RED, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals(GAME_ID_LADDER_HOME_SCORE_ONLY, $game->spirit_entries[0]->game_id);
		$this->assertEquals(2, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(1, $game->spirit_entries[0]->q3);
		$this->assertEquals(1, $game->spirit_entries[0]->q4);
		$this->assertEquals(2, $game->spirit_entries[0]->q5);
		$this->assertEquals(TEAM_ID_RED, $game->spirit_entries[1]->team_id);
		$this->assertEquals(TEAM_ID_GREEN, $game->spirit_entries[1]->created_team_id);
		$this->assertEquals(GAME_ID_LADDER_HOME_SCORE_ONLY, $game->spirit_entries[1]->game_id);
		$this->assertEquals(0, $game->spirit_entries[1]->q1);
		$this->assertEquals(1, $game->spirit_entries[1]->q2);
		$this->assertEquals(2, $game->spirit_entries[1]->q3);
		$this->assertEquals(3, $game->spirit_entries[1]->q4);
		$this->assertEquals(4, $game->spirit_entries[1]->q5);
	}

	/**
	 * Test submit_score method as a captain, correcting an earlier incorrect submission
	 *
	 * @return void
	 */
	public function testSubmitCorrectScoreAsCaptain() {
		$url = ['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_LADDER_MISMATCHED_SCORES, 'team' => TEAM_ID_YELLOW];

		// Scores can only be submitted after the game, so we need to set "today" for this test to be reliable
		FrozenTime::setTestNow((new FrozenTime('last Monday of May'))->addDays(22));
		$this->assertGetAsAccessOk($url, PERSON_ID_CAPTAIN4);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertPostAsAccessRedirect($url,
			PERSON_ID_CAPTAIN4, [
				'score_entries' => [
					[
						'id' => SCORE_ID_LADDER_MISMATCHED_SCORES_AWAY,
						'team_id' => TEAM_ID_YELLOW,
						'game_id' => GAME_ID_LADDER_MISMATCHED_SCORES,
						'status' => 'normal',
						'score_for' => '14',
						'score_against' => '15',
						'home_carbon_flip' => 1,
					],
				],
				'spirit_entries' => [
					[
						'id' => SPIRIT_ID_LADDER_MISMATCHED_SCORES_AWAY,
						'team_id' => TEAM_ID_GREEN,
						'created_team_id' => TEAM_ID_YELLOW,
						'q1' => 2,
						'q2' => 2,
						'q3' => 2,
						'q4' => 2,
						'q5' => 2,
						'comments' => '',
						'highlights' => '',
					]
				],
				'has_incident' => false,
			], '/', 'This score agrees with the score submitted by your opponent. It will now be posted as an official game result.'
		);

		$this->assertEmpty(Configure::read('test_emails'));

		$game = TableRegistry::get('Games')->get(GAME_ID_LADDER_MISMATCHED_SCORES, ['contain' => ['SpiritEntries']]);
		$this->assertTrue($game->isFinalized());
		$this->assertEquals(15, $game->home_score);
		$this->assertEquals(14, $game->away_score);
		$this->assertEquals(2, count($game->spirit_entries));
		$this->assertEquals(TEAM_ID_YELLOW, $game->spirit_entries[0]->team_id);
		$this->assertEquals(TEAM_ID_GREEN, $game->spirit_entries[0]->created_team_id);
		$this->assertEquals(GAME_ID_LADDER_MISMATCHED_SCORES, $game->spirit_entries[0]->game_id);
		$this->assertEquals(1, $game->spirit_entries[0]->q1);
		$this->assertEquals(2, $game->spirit_entries[0]->q2);
		$this->assertEquals(2, $game->spirit_entries[0]->q3);
		$this->assertEquals(2, $game->spirit_entries[0]->q4);
		$this->assertEquals(1, $game->spirit_entries[0]->q5);
		$this->assertEquals(TEAM_ID_GREEN, $game->spirit_entries[1]->team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $game->spirit_entries[1]->created_team_id);
		$this->assertEquals(GAME_ID_LADDER_MISMATCHED_SCORES, $game->spirit_entries[1]->game_id);
		$this->assertEquals(2, $game->spirit_entries[1]->q1);
		$this->assertEquals(2, $game->spirit_entries[1]->q2);
		$this->assertEquals(2, $game->spirit_entries[1]->q3);
		$this->assertEquals(2, $game->spirit_entries[1]->q4);
		$this->assertEquals(2, $game->spirit_entries[1]->q5);
	}

	/**
	 * Test submit_score method as others
	 *
	 * @return void
	 */
	public function testSubmitScoreAsOthers() {
		// Others are not allowed to submit scores
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_ADMIN);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_LADDER_MATCHED_SCORES, 'team' => TEAM_ID_RED], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1]);
	}

	/**
	 * Test submit_stats method as an admin
	 *
	 * @return void
	 */
	public function testSubmitStatsAsAdmin() {
		// Make sure that we're after the game date
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Admins are allowed to submit stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'submit_stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method as a manager
	 *
	 * @return void
	 */
	public function testSubmitStatsAsManager() {
		// Make sure that we're after the game date
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Managers are allowed to submit stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'submit_stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method as a coordinator
	 *
	 * @return void
	 */
	public function testSubmitStatsAsCoordinator() {
		// Make sure that we're after the game date
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Coordinators are allowed to submit stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'submit_stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method as a captain
	 *
	 * @return void
	 */
	public function testSubmitStatsAsCaptain() {
		// Make sure that we're after the game date
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Captains are allowed to submit stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'submit_stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method as others
	 *
	 * @return void
	 */
	public function testSubmitStatsAsOthers() {
		// Make sure that we're after the game date
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Others are not allowed to submit stats
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'submit_stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Games', 'action' => 'submit_stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'submit_stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN, 'team' => TEAM_ID_CHICKADEES]);
	}

	/**
	 * Test stats method
	 *
	 * @return void
	 */
	public function testStats() {
		// Make sure that we're after the game date
		FrozenDate::setTestNow(new FrozenDate('July 1'));

		// Anyone logged in is allowed to see game stats
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_VISITOR);

		// Others are not allowed to see game stats
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'stats', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test future method
	 *
	 * @return void
	 */
	public function testFuture() {
		// Anyone logged in is allowed to see future games
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'future', '_ext' => 'json'], PERSON_ID_VISITOR);

		// Others are not allowed to see future games
		$this->assertGetAnonymousAccessDenied(['controller' => 'Games', 'action' => 'future', '_ext' => 'json']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test results method
	 *
	 * @return void
	 */
	public function testResults() {
		// Anyone is allowed to see recent results
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Games', 'action' => 'results', '_ext' => 'json']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

}
