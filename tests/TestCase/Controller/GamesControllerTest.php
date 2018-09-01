<?php
namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
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
	 * Test view method as an admin
	 *
	 * @return void
	 */
	public function testViewAsAdmin() {
		// Admins are allowed to view games, with full edit permissions
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#currently rated#ms');
		$this->assertResponseRegExp('#chance to win#ms');
		$this->assertResponseRegExp('#<dt>Game Status</dt>\s*<dd>Normal</dd>#ms');
		$this->assertResponseNotRegExp('#<dt>Round</dt>#ms');
		$this->assertResponseRegExp('#Captain Emails#ms');
		$this->assertResponseRegExp('#Ratings Table#ms');
		$this->assertResponseNotRegExp('#/games/attendance\?team=' . TEAM_ID_RED . '&amp;game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseNotRegExp('#/games/note\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseRegExp('#/games/edit\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseRegExp('#/games/delete\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseNotRegExp('#/games/stats\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseNotRegExp('#<dt>Score Approved By</dt>#ms');
		$this->assertResponseRegExp('#<p>The score of this game has not yet been finalized.</p>#ms');
		$this->assertResponseRegExp('#Score as entered#ms');
		$this->assertResponseRegExp('#<th>Red \(home\)</th>\s*<th>Blue \(away\)</th>#ms');
		$this->assertResponseRegExp('#<td>Home Score</td>\s*<td>17</td>\s*<td>17</td>#ms');
		$this->assertResponseRegExp('#<td>Away Score</td>\s*<td>12</td>\s*<td>12</td>#ms');
		$this->assertResponseRegExp('#<td>Defaulted\?</td>\s*<td>no</td>\s*<td>no</td>#ms');

		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_SUB], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#chance to win#ms');
		$this->assertResponseRegExp('#Captain Emails#ms');
		$this->assertResponseRegExp('#Ratings Table#ms');
		$this->assertResponseRegExp('#/games/edit\?game=' . GAME_ID_SUB . '#ms');
		$this->assertResponseRegExp('#/games/delete\?game=' . GAME_ID_SUB . '#ms');
		$this->assertResponseNotRegExp('#/games/stats\?game=' . GAME_ID_SUB . '#ms');
	}

	/**
	 * Test view method as a manager
	 *
	 * @return void
	 */
	public function testViewAsManager() {
		// Managers are allowed to view games
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#Captain Emails#ms');
		$this->assertResponseRegExp('#Ratings Table#ms');
		$this->assertResponseNotRegExp('#/games/attendance\?team=' . TEAM_ID_RED . '&amp;game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseNotRegExp('#/games/note\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseRegExp('#/games/edit\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseRegExp('#/games/delete\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');

		// But cannot edit ones in other affiliates
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_SUB], PERSON_ID_MANAGER);
		$this->assertResponseNotRegExp('#Captain Emails#ms');
		$this->assertResponseRegExp('#Ratings Table#ms');
		$this->assertResponseNotRegExp('#/games/edit\?game=' . GAME_ID_SUB . '#ms');
		$this->assertResponseNotRegExp('#/games/delete\?game=' . GAME_ID_SUB . '#ms');
	}

	/**
	 * Test view method as a coordinator
	 *
	 * @return void
	 */
	public function testViewAsCoordinator() {
		// Coordinators are allowed to view games
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_COORDINATOR);
		$this->assertResponseRegExp('#Captain Emails#ms');
		$this->assertResponseNotRegExp('#/games/attendance\?team=' . TEAM_ID_RED . '&amp;game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseNotRegExp('#/games/note\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseRegExp('#/games/edit\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseRegExp('#/games/delete\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');

		// But cannot edit ones in other divisions
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1], PERSON_ID_COORDINATOR);
		$this->assertResponseRegExp('#<dt>Round</dt>#ms');
		$this->assertResponseNotRegExp('#Captain Emails#ms');
		// Round robin games don't have ratings tables
		$this->assertResponseNotRegExp('#Ratings Table#ms');
		$this->assertResponseNotRegExp('#/games/edit\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '#ms');
		$this->assertResponseNotRegExp('#/games/delete\?game=' . GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1 . '#ms');
	}

	/**
	 * Test view method as a captain
	 *
	 * @return void
	 */
	public function testViewAsCaptain() {
		// Captains are allowed to view games, perhaps with slightly more permission than players
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#Captain Emails#ms');
		$this->assertResponseRegExp('#/games/attendance\?team=' . TEAM_ID_RED . '&amp;game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseRegExp('#/games/note\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseNotRegExp('#/games/edit\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseNotRegExp('#/games/delete\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');

		// Confirm different output for finalized games
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_THURSDAY_ROUND_ROBIN], PERSON_ID_CAPTAIN);
		$this->assertResponseNotRegExp('#chance to win#ms');
		$this->assertResponseNotRegExp('#Ratings Table#ms');
		$this->assertResponseRegExp('#/games/stats\?game=' . GAME_ID_THURSDAY_ROUND_ROBIN . '#ms');
		$this->assertResponseRegExp('#<dt>Chickadees</dt>\s*<dd>15</dd>#ms');
		$this->assertResponseRegExp('#<dt>Sparrows</dt>\s*<dd>14</dd>#ms');
		$this->assertResponseRegExp('#<dt>Score Approved By</dt>\s*<dd>automatic approval</dd>#ms');

		// Confirm different output for playoff games
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_PLAYOFFS], PERSON_ID_CAPTAIN);
		$this->assertResponseRegExp('#<dt>Home Team</dt>\s*<dd>1st seed</dd>#ms');
		$this->assertResponseNotRegExp('#currently rated#ms');
		$this->assertResponseNotRegExp('#chance to win#ms');
		$this->assertResponseNotRegExp('#Captain Emails#ms');
		// Uninitialized playoff games don't have ratings tables
		$this->assertResponseNotRegExp('#Ratings Table#ms');
		$this->assertResponseNotRegExp('#/games/edit\?game=' . GAME_ID_PLAYOFFS . '#ms');
		$this->assertResponseNotRegExp('#/games/delete\?game=' . GAME_ID_PLAYOFFS . '#ms');

		// TODO: All the different options for carbon flips, spirit, rating points, approved by. We need more games, in various states, across all divisions to fully test all of these.
		//$this->assertResponseRegExp('#<dt>Carbon Flip</dt>\s*<dd>Red won</dd>#ms');
		//$this->assertResponseRegExp('#<dt>Rating Points</dt>\s*<dd>.*gain 5 points\s*</dd>#ms');
	}

	/**
	 * Test view method as a player
	 *
	 * @return void
	 */
	public function testViewAsPlayer() {
		// Others are allowed to view games, but have no edit permissions
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_PLAYER);
		$this->assertResponseNotRegExp('#Captain Emails#ms');
		$this->assertResponseRegExp('#/games/attendance\?team=' . TEAM_ID_RED . '&amp;game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseRegExp('#/games/note\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseNotRegExp('#/games/edit\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
		$this->assertResponseNotRegExp('#/games/delete\?game=' . GAME_ID_LADDER_MATCHED_SCORES . '#ms');
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
		// Everyone is allowed to view game tooltips
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_ADMIN, 'getajax');
		$this->assertResponseRegExp('#/facilities\\\\/view\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/teams\\\\/view\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/teams\\\\/view\?team=' . TEAM_ID_BLUE . '#ms');

		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'tooltip', 'game' => 0],
			PERSON_ID_ADMIN, 'getajax', [], null,
			'Invalid game.');
	}

	/**
	 * Test tooltip method as a manager
	 *
	 * @return void
	 */
	public function testTooltipAsManager() {
		// Everyone is allowed to view game tooltips
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_MANAGER, 'getajax');
		$this->assertResponseRegExp('#/facilities\\\\/view\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/teams\\\\/view\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/teams\\\\/view\?team=' . TEAM_ID_BLUE . '#ms');
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
		// Everyone is allowed to view game tooltips
		$this->assertAccessOk(['controller' => 'Games', 'action' => 'tooltip', 'game' => GAME_ID_LADDER_MATCHED_SCORES], PERSON_ID_PLAYER, 'getajax');
		$this->assertResponseRegExp('#/facilities\\\\/view\?facility=' . FACILITY_ID_SUNNYBROOK . '#ms');
		$this->assertResponseRegExp('#/teams\\\\/view\?team=' . TEAM_ID_RED . '#ms');
		$this->assertResponseRegExp('#/teams\\\\/view\?team=' . TEAM_ID_BLUE . '#ms');
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
	 * Test ratings_table method as an admin
	 *
	 * @return void
	 */
	public function testRatingsTableAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings_table method as a manager
	 *
	 * @return void
	 */
	public function testRatingsTableAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings_table method as a coordinator
	 *
	 * @return void
	 */
	public function testRatingsTableAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings_table method as a captain
	 *
	 * @return void
	 */
	public function testRatingsTableAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings_table method as a player
	 *
	 * @return void
	 */
	public function testRatingsTableAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings_table method as someone else
	 *
	 * @return void
	 */
	public function testRatingsTableAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test ratings_table method without being logged in
	 *
	 * @return void
	 */
	public function testRatingsTableAsAnonymous() {
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
	 * Test edit_boxscore method as an admin
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_boxscore method as a manager
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_boxscore method as a coordinator
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_boxscore method as a captain
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_boxscore method as a player
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_boxscore method as someone else
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test edit_boxscore method without being logged in
	 *
	 * @return void
	 */
	public function testEditBoxscoreAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method as an admin
	 *
	 * @return void
	 */
	public function testDeleteScoreAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method as a manager
	 *
	 * @return void
	 */
	public function testDeleteScoreAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteScoreAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method as a captain
	 *
	 * @return void
	 */
	public function testDeleteScoreAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method as a player
	 *
	 * @return void
	 */
	public function testDeleteScoreAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method as someone else
	 *
	 * @return void
	 */
	public function testDeleteScoreAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test delete_score method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteScoreAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method as an admin
	 *
	 * @return void
	 */
	public function testAddScoreAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method as a manager
	 *
	 * @return void
	 */
	public function testAddScoreAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method as a coordinator
	 *
	 * @return void
	 */
	public function testAddScoreAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method as a captain
	 *
	 * @return void
	 */
	public function testAddScoreAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method as a player
	 *
	 * @return void
	 */
	public function testAddScoreAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method as someone else
	 *
	 * @return void
	 */
	public function testAddScoreAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_score method without being logged in
	 *
	 * @return void
	 */
	public function testAddScoreAsAnonymous() {
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

		// Admins are allowed to delete games
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'The game has been deleted.', 'Flash.flash.0.message');

		// But not ones with dependencies
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY],
			'A score has already been submitted for this game.', 'Flash.flash.0.message.0');
		$this->assertEquals('If you are absolutely sure that you want to delete it anyway, {0}. <b>This cannot be undone!</b>', $this->_requestSession->read('Flash.flash.0.message.1'));
		$this->assertEquals(['action' => 'delete', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY, 'force' => true], $this->_requestSession->read('Flash.flash.0.params.replacements.0.target'));

		// Unless we force it
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_HOME_SCORE_ONLY, 'force' => true],
			PERSON_ID_ADMIN, 'post', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'The game has been deleted.', 'Flash.flash.0.message');

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

		// Managers can delete games in their affiliate
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES],
			PERSON_ID_MANAGER, 'post', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'The game has been deleted.', 'Flash.flash.0.message');

		// But not ones in other affiliates
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_SUB],
			PERSON_ID_MANAGER, 'post');
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsCoordinator() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		// Coordinators can delete their own games
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_LADDER_NO_SCORES],
			PERSON_ID_COORDINATOR, 'post', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'The game has been deleted.', 'Flash.flash.0.message');

		// But not ones in other divisions
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'delete', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1],
			PERSON_ID_COORDINATOR, 'post');
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
	 * Test add_sub method as an admin
	 *
	 * @return void
	 */
	public function testAddSubAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_sub method as a manager
	 *
	 * @return void
	 */
	public function testAddSubAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_sub method as a coordinator
	 *
	 * @return void
	 */
	public function testAddSubAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_sub method as a captain
	 *
	 * @return void
	 */
	public function testAddSubAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_sub method as a player
	 *
	 * @return void
	 */
	public function testAddSubAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_sub method as someone else
	 *
	 * @return void
	 */
	public function testAddSubAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_sub method without being logged in
	 *
	 * @return void
	 */
	public function testAddSubAsAnonymous() {
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
	 * Test live_score method as an admin
	 *
	 * @return void
	 */
	public function testLiveScoreAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test live_score method as a manager
	 *
	 * @return void
	 */
	public function testLiveScoreAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test live_score method as a coordinator
	 *
	 * @return void
	 */
	public function testLiveScoreAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test live_score method as a captain
	 *
	 * @return void
	 */
	public function testLiveScoreAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test live_score method as a player
	 *
	 * @return void
	 */
	public function testLiveScoreAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test live_score method as someone else
	 *
	 * @return void
	 */
	public function testLiveScoreAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test live_score method without being logged in
	 *
	 * @return void
	 */
	public function testLiveScoreAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_up method as an admin
	 *
	 * @return void
	 */
	public function testScoreUpAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_up method as a manager
	 *
	 * @return void
	 */
	public function testScoreUpAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_up method as a coordinator
	 *
	 * @return void
	 */
	public function testScoreUpAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_up method as a captain
	 *
	 * @return void
	 */
	public function testScoreUpAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_up method as a player
	 *
	 * @return void
	 */
	public function testScoreUpAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_up method as someone else
	 *
	 * @return void
	 */
	public function testScoreUpAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_up method without being logged in
	 *
	 * @return void
	 */
	public function testScoreUpAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_down method as an admin
	 *
	 * @return void
	 */
	public function testScoreDownAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_down method as a manager
	 *
	 * @return void
	 */
	public function testScoreDownAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_down method as a coordinator
	 *
	 * @return void
	 */
	public function testScoreDownAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_down method as a captain
	 *
	 * @return void
	 */
	public function testScoreDownAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_down method as a player
	 *
	 * @return void
	 */
	public function testScoreDownAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_down method as someone else
	 *
	 * @return void
	 */
	public function testScoreDownAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test score_down method without being logged in
	 *
	 * @return void
	 */
	public function testScoreDownAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeout method as an admin
	 *
	 * @return void
	 */
	public function testTimeoutAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeout method as a manager
	 *
	 * @return void
	 */
	public function testTimeoutAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeout method as a coordinator
	 *
	 * @return void
	 */
	public function testTimeoutAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeout method as a captain
	 *
	 * @return void
	 */
	public function testTimeoutAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeout method as a player
	 *
	 * @return void
	 */
	public function testTimeoutAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeout method as someone else
	 *
	 * @return void
	 */
	public function testTimeoutAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeout method without being logged in
	 *
	 * @return void
	 */
	public function testTimeoutAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test play method as an admin
	 *
	 * @return void
	 */
	public function testPlayAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test play method as a manager
	 *
	 * @return void
	 */
	public function testPlayAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test play method as a coordinator
	 *
	 * @return void
	 */
	public function testPlayAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test play method as a captain
	 *
	 * @return void
	 */
	public function testPlayAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test play method as a player
	 *
	 * @return void
	 */
	public function testPlayAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test play method as someone else
	 *
	 * @return void
	 */
	public function testPlayAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test play method without being logged in
	 *
	 * @return void
	 */
	public function testPlayAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tweet method as an admin
	 *
	 * @return void
	 */
	public function testTweetAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tweet method as a manager
	 *
	 * @return void
	 */
	public function testTweetAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tweet method as a coordinator
	 *
	 * @return void
	 */
	public function testTweetAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tweet method as a captain
	 *
	 * @return void
	 */
	public function testTweetAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tweet method as a player
	 *
	 * @return void
	 */
	public function testTweetAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tweet method as someone else
	 *
	 * @return void
	 */
	public function testTweetAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test tweet method without being logged in
	 *
	 * @return void
	 */
	public function testTweetAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_score method as an admin
	 *
	 * @return void
	 */
	public function testSubmitScoreAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_score method as a manager
	 *
	 * @return void
	 */
	public function testSubmitScoreAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_score method as a coordinator
	 *
	 * @return void
	 */
	public function testSubmitScoreAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
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
		$this->assertAccessRedirect($url,
			PERSON_ID_CAPTAIN2, 'get', [], ['controller' => 'Games', 'action' => 'view', 'game' => GAME_ID_LADDER_NO_SCORES],
			'That game has not yet occurred!', 'Flash.flash.0.message');

		FrozenTime::setTestNow((new FrozenTime('last Monday of May'))->addDays(29));
		$this->assertAccessOk($url, PERSON_ID_CAPTAIN2);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertAccessRedirect($url,
			PERSON_ID_CAPTAIN2, 'post', [
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
			], '/', 'This score has been saved. Once your opponent has entered their score, it will be officially posted. The score you have submitted indicates that this game was {0}. If this is incorrect, you can {1} to correct it.', 'Flash.flash.0.message'
		);
		$this->assertEquals('a win for your team', $this->_requestSession->read('Flash.flash.0.params.replacements.0.text'));

		$messages = Configure::read('test_emails');
		$this->assertEquals(1, count($messages));
		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#Reply-To: &quot;Chuck Captain&quot; &lt;chuck@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Carl Captain&quot; &lt;carl@zuluru.org&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		$this->assertRegExp('#Subject: Opponent score submission#ms', $messages[0]);
		$this->assertRegExp('#Your opponent has indicated that the game between your team Yellow and Blue, starting at 7:00PM on Jun 25, 2018 in 2018 Summer Monday Night Ultimate Competitive was a 17-10 loss for your team.#ms', $messages[0]);

		$game = TableRegistry::get('Games')->get(GAME_ID_LADDER_NO_SCORES, ['contain' => ['SpiritEntries']]);
		$this->assertFalse($game->isFinalized());
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
		$this->assertAccessOk($url, PERSON_ID_CAPTAIN3);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertAccessRedirect($url,
			PERSON_ID_CAPTAIN3, 'post', [
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
			], '/', 'This score agrees with the score submitted by your opponent. It will now be posted as an official game result.', 'Flash.flash.0.message'
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
		$this->assertAccessOk($url, PERSON_ID_CAPTAIN3);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertAccessRedirect($url,
			PERSON_ID_CAPTAIN3, 'post', [
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
			], '/', 'This score doesn\'t agree with the one your opponent submitted. Because of this, the score will not be posted until your coordinator approves it. Alternately, whichever coach or captain made an error can {0}.', 'Flash.flash.0.message'
		);

		$messages = Configure::read('test_emails');
		$this->assertEquals(2, count($messages));

		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[0]);
		$this->assertRegExp('#To: &quot;Cindy Coordinator&quot; &lt;cindy@zuluru.org&gt;#ms', $messages[0]);
		$this->assertNotRegExp('#CC: #ms', $messages[0]);
		$this->assertNotRegExp('#Reply-To: #ms', $messages[0]);
		$this->assertRegExp('#Subject: Score entry mismatch#ms', $messages[0]);
		$this->assertRegExp('#The Jun 25, 2018 game between Red and Green in Monday Night has score entries which do not match. You can edit the game here:#ms', $messages[0]);

		$this->assertRegExp('#From: &quot;Admin&quot; &lt;admin@zuluru.org&gt;#ms', $messages[1]);
		$this->assertRegExp('#Reply-To: &quot;Carolyn Captain&quot; &lt;carolyn@zuluru.org&gt;#ms', $messages[1]);
		$this->assertRegExp('#To: &quot;Crystal Captain&quot; &lt;crystal@zuluru.org&gt;#ms', $messages[1]);
		$this->assertNotRegExp('#CC: #ms', $messages[1]);
		$this->assertRegExp('#Subject: Opponent score submission#ms', $messages[1]);
		$this->assertRegExp('#Your opponent has indicated that the game between your team Red and Green, starting at 7:00PM on Jun 25, 2018 in 2018 Summer Monday Night Ultimate Competitive was a 5-5 tie.#ms', $messages[1]);

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
		$this->assertAccessOk($url, PERSON_ID_CAPTAIN4);

		$this->enableCsrfToken();
		$this->enableSecurityToken();
		$this->assertAccessRedirect($url,
			PERSON_ID_CAPTAIN4, 'post', [
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
			], '/', 'This score agrees with the score submitted by your opponent. It will now be posted as an official game result.', 'Flash.flash.0.message'
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
	 * Test submit_score method as a player
	 *
	 * @return void
	 */
	public function testSubmitScoreAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1],
			PERSON_ID_PLAYER);
	}

	/**
	 * Test submit_score method as someone else
	 *
	 * @return void
	 */
	public function testSubmitScoreAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1],
			PERSON_ID_VISITOR);
	}

	/**
	 * Test submit_score method without being logged in
	 *
	 * @return void
	 */
	public function testSubmitScoreAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Games', 'action' => 'submit_score', 'game' => GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_1]);
	}

	/**
	 * Test submit_stats method as an admin
	 *
	 * @return void
	 */
	public function testSubmitStatsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method as a manager
	 *
	 * @return void
	 */
	public function testSubmitStatsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method as a coordinator
	 *
	 * @return void
	 */
	public function testSubmitStatsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method as a captain
	 *
	 * @return void
	 */
	public function testSubmitStatsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method as a player
	 *
	 * @return void
	 */
	public function testSubmitStatsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method as someone else
	 *
	 * @return void
	 */
	public function testSubmitStatsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test submit_stats method without being logged in
	 *
	 * @return void
	 */
	public function testSubmitStatsAsAnonymous() {
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
	 * Test future method as an admin
	 *
	 * @return void
	 */
	public function testFutureAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test future method as a manager
	 *
	 * @return void
	 */
	public function testFutureAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test future method as a coordinator
	 *
	 * @return void
	 */
	public function testFutureAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test future method as a captain
	 *
	 * @return void
	 */
	public function testFutureAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test future method as a player
	 *
	 * @return void
	 */
	public function testFutureAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test future method as someone else
	 *
	 * @return void
	 */
	public function testFutureAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test future method without being logged in
	 *
	 * @return void
	 */
	public function testFutureAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test results method as an admin
	 *
	 * @return void
	 */
	public function testResultsAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test results method as a manager
	 *
	 * @return void
	 */
	public function testResultsAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test results method as a coordinator
	 *
	 * @return void
	 */
	public function testResultsAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test results method as a captain
	 *
	 * @return void
	 */
	public function testResultsAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test results method as a player
	 *
	 * @return void
	 */
	public function testResultsAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test results method as someone else
	 *
	 * @return void
	 */
	public function testResultsAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test results method without being logged in
	 *
	 * @return void
	 */
	public function testResultsAsAnonymous() {
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
