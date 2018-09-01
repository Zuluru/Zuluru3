<?php
namespace App\Test\TestCase\Controller;

use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\SchedulesController Test Case
 */
class SchedulesControllerTest extends ControllerTestCase {

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
			'app.attendances',
			'app.mailing_lists',
				'app.newsletters',
			'app.activity_logs',
			'app.notes',
			'app.settings',
	];

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins can add to schedules anywhere
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<input type="radio" name="_options\[type\]" value="single" id="options-type-single">\s*single blank, unscheduled game \(2 teams, one field\)#ms');
		$this->assertResponseRegExp('#<input type="radio" name="_options\[type\]" value="oneset_ratings_ladder" id="options-type-oneset_ratings_ladder">\s*set of ratings-scheduled games for all teams \(8 teams, 4 games, one day\)#ms');
		$this->assertResponseRegExp('#<input type="checkbox" name="_options\[publish\]" value="1" id="options-publish">#ms');
		$this->assertResponseRegExp('#<input type="checkbox" name="_options\[double_header\]" value="1" id="options-double-header">#ms');
		$this->assertResponseRegExp('#/schedules/add\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;playoff=1#ms');

		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers can add to schedules in their own affiliate
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Coordinators can add to schedules in their divisions
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test add method as a captain
	 *
	 * @return void
	 */
	public function testAddAsCaptain() {
		// Others cannot add to schedules
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test add method as a player
	 *
	 * @return void
	 */
	public function testAddAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
	}

	/**
	 * Test add method as someone else
	 *
	 * @return void
	 */
	public function testAddAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
	}

	/**
	 * Test add method without being logged in
	 *
	 * @return void
	 */
	public function testAddAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		// Admins can delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>#ms');
		$this->assertResponseRegExp('#/schedules/delete\?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date->toDateString() . '&amp;confirm=1#ms');

		// Or any league
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>#ms');

		// Or any affiliate
		$date = (new FrozenDate('last Sunday of May'))->addWeeks(1);
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()], PERSON_ID_ADMIN);

		// Check the errors for dates with no games
		$date = (new FrozenDate('last Monday of May'))->addWeeks(10);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'#There are no games to delete on that date.#', 'Flash.flash.0.message');

		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_MONDAY],
			'#There are no games to delete on that date.#', 'Flash.flash.0.message');

		// Make sure that deleting games actually deletes them, and frees up game slots
		$slots = TableRegistry::get('game_slots')->find()
			->where(['id IN' => [GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1, GAME_SLOT_ID_MONDAY_SUNNYBROOK_2_WEEK_1]])
			->toArray();
		$this->assertEquals(2, count($slots));
		$this->assertTrue($slots[0]->assigned);
		$this->assertTrue($slots[1]->assigned);

		$date = (new FrozenDate('last Monday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString(), 'confirm' => true],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'#Deleted games on the requested date.#', 'Flash.flash.0.message');

		$games = TableRegistry::get('games')->find()
			->where(['id IN' => [GAME_ID_LADDER_FINALIZED_HOME_WIN, GAME_ID_LADDER_CANCELLED]]);
		$this->assertEquals(0, $games->count());
		$slots = TableRegistry::get('game_slots')->find()
			->where(['id IN' => [GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1, GAME_SLOT_ID_MONDAY_SUNNYBROOK_2_WEEK_1]])
			->toArray();
		$this->assertEquals(2, count($slots));
		$this->assertFalse($slots[0]->assigned);
		$this->assertFalse($slots[1]->assigned);
	}

	/**
	 * Test delete method as a manager
	 *
	 * @return void
	 */
	public function testDeleteAsManager() {
		// Managers can delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>#ms');

		// Or any league
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
		$this->assertResponseRegExp('#<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>#ms');

		// But not other affiliates
		$date = (new FrozenDate('last Sunday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsCoordinator() {
		// Coordinators can delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
		$this->assertResponseRegExp('#<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>#ms');

		// Or any league where they coordinate all of the divisions
		$this->assertAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
		$this->assertResponseRegExp('#<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>#ms');

		// But not other divisions
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test delete method as a captain
	 *
	 * @return void
	 */
	public function testDeleteAsCaptain() {
		// Others cannot delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_CAPTAIN);

		// Or any league
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test delete method as a player
	 *
	 * @return void
	 */
	public function testDeleteAsPlayer() {
		// Others cannot delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_PLAYER);

		// Or any league
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_PLAYER);
	}

	/**
	 * Test delete method as someone else
	 *
	 * @return void
	 */
	public function testDeleteAsVisitor() {
		// Others cannot delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_VISITOR);

		// Or any league
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_VISITOR);
	}

	/**
	 * Test delete method without being logged in
	 *
	 * @return void
	 */
	public function testDeleteAsAnonymous() {
		// Others cannot delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()]);

		// Or any league
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()]);
	}

	/**
	 * Test reschedule method as an admin
	 *
	 * @return void
	 */
	public function testRescheduleAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reschedule method as a manager
	 *
	 * @return void
	 */
	public function testRescheduleAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reschedule method as a coordinator
	 *
	 * @return void
	 */
	public function testRescheduleAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reschedule method as a captain
	 *
	 * @return void
	 */
	public function testRescheduleAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reschedule method as a player
	 *
	 * @return void
	 */
	public function testRescheduleAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reschedule method as someone else
	 *
	 * @return void
	 */
	public function testRescheduleAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reschedule method without being logged in
	 *
	 * @return void
	 */
	public function testRescheduleAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test publish method as an admin
	 *
	 * @return void
	 */
	public function testPublishAsAdmin() {
		$game = TableRegistry::get('games')->get(GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2);
		$this->assertFalse($game->published);

		// Admins can publish schedules anywhere
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN],
			'#Published games on the requested date.#', 'Flash.flash.0.message');

		$game = TableRegistry::get('games')->get(GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2);
		$this->assertTrue($game->published);

		$date = (new FrozenDate('last Sunday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_SUNDAY_SUB],
			'#Published games on the requested date.#', 'Flash.flash.0.message');
	}

	/**
	 * Test publish method as a manager
	 *
	 * @return void
	 */
	public function testPublishAsManager() {
		// Managers can publish schedules anywhere
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()],
			PERSON_ID_MANAGER, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN],
			'#Published games on the requested date.#', 'Flash.flash.0.message');

		// But not in other affiliates
		$date = (new FrozenDate('last Sunday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
	}

	/**
	 * Test publish method as a coordinator
	 *
	 * @return void
	 */
	public function testPublishAsCoordinator() {
		// Coordinators can publish schedules in their own divisions
		$date = (new FrozenDate('last Thursday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN, 'date' => $date->toDateString()],
			PERSON_ID_COORDINATOR, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN],
			// There are no unpublished games on this date, so the message will be a failure. But at least it passed permission checks...
			'#Failed to publish games on the requested date.#', 'Flash.flash.0.message');

		// But not in other divisions
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test publish method as a captain
	 *
	 * @return void
	 */
	public function testPublishAsCaptain() {
		// Others can't publish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test publish method as a player
	 *
	 * @return void
	 */
	public function testPublishAsPlayer() {
		// Others can't publish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_PLAYER);
	}

	/**
	 * Test publish method as someone else
	 *
	 * @return void
	 */
	public function testPublishAsVisitor() {
		// Others can't publish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_VISITOR);
	}

	/**
	 * Test publish method without being logged in
	 *
	 * @return void
	 */
	public function testPublishAsAnonymous() {
		// Others can't publish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()]);
	}

	/**
	 * Test unpublish method as an admin
	 *
	 * @return void
	 */
	public function testUnpublishAsAdmin() {
		$game = TableRegistry::get('games')->get(GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertTrue($game->published);

		// Admins can unpublish schedules anywhere
		$date = (new FrozenDate('last Monday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'#Unpublished games on the requested date.#', 'Flash.flash.0.message');

		$game = TableRegistry::get('games')->get(GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertFalse($game->published);

		$date = (new FrozenDate('last Sunday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_SUNDAY_SUB],
			'#Unpublished games on the requested date.#', 'Flash.flash.0.message');
	}

	/**
	 * Test unpublish method as a manager
	 *
	 * @return void
	 */
	public function testUnpublishAsManager() {
		// Managers can unpublish schedules anywhere
		$date = (new FrozenDate('last Monday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()],
			PERSON_ID_MANAGER, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'#Unpublished games on the requested date.#', 'Flash.flash.0.message');

		// But not in other affiliates
		$date = (new FrozenDate('last Sunday of May'))->addWeeks(2);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
	}

	/**
	 * Test unpublish method as a coordinator
	 *
	 * @return void
	 */
	public function testUnpublishAsCoordinator() {
		// Coordinators can unpublish schedules in their own divisions
		$date = (new FrozenDate('last Thursday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN, 'date' => $date->toDateString()],
			PERSON_ID_COORDINATOR, 'get', [], ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN],
			// There are no unpublished games on this date, so the message will be a failure. But at least it passed permission checks...
			'#Unpublished games on the requested date.#', 'Flash.flash.0.message');

		// But not in other divisions
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test unpublish method as a captain
	 *
	 * @return void
	 */
	public function testUnpublishAsCaptain() {
		// Others can't unpublish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_CAPTAIN);
	}

	/**
	 * Test unpublish method as a player
	 *
	 * @return void
	 */
	public function testUnpublishAsPlayer() {
		// Others can't unpublish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_PLAYER);
	}

	/**
	 * Test unpublish method as someone else
	 *
	 * @return void
	 */
	public function testUnpublishAsVisitor() {
		// Others can't unpublish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_VISITOR);
	}

	/**
	 * Test unpublish method without being logged in
	 *
	 * @return void
	 */
	public function testUnpublishAsAnonymous() {
		// Others can't unpublish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(1);
		$this->assertAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()]);
	}

	/**
	 * Test today method as an admin
	 *
	 * @return void
	 */
	public function testTodayAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test today method as a manager
	 *
	 * @return void
	 */
	public function testTodayAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test today method as a coordinator
	 *
	 * @return void
	 */
	public function testTodayAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test today method as a captain
	 *
	 * @return void
	 */
	public function testTodayAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test today method as a player
	 *
	 * @return void
	 */
	public function testTodayAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test today method as someone else
	 *
	 * @return void
	 */
	public function testTodayAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test today method without being logged in
	 *
	 * @return void
	 */
	public function testTodayAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method as an admin
	 *
	 * @return void
	 */
	public function testDayAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method as a manager
	 *
	 * @return void
	 */
	public function testDayAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method as a coordinator
	 *
	 * @return void
	 */
	public function testDayAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method as a captain
	 *
	 * @return void
	 */
	public function testDayAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method as a player
	 *
	 * @return void
	 */
	public function testDayAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method as someone else
	 *
	 * @return void
	 */
	public function testDayAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method without being logged in
	 *
	 * @return void
	 */
	public function testDayAsAnonymous() {
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

}
