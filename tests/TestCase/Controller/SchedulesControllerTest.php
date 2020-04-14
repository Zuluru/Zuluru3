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
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
					'app.PeoplePeople',
			'app.Groups',
				'app.GroupsPeople',
			'app.Regions',
				'app.Facilities',
					'app.Fields',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
						'app.TeamsPeople',
						'app.TeamEvents',
					'app.DivisionsDays',
					'app.DivisionsPeople',
					'app.GameSlots',
						'app.DivisionsGameslots',
					'app.Pools',
						'app.PoolsTeams',
					'app.Games',
						'app.GamesAllstars',
						'app.ScoreEntries',
						'app.SpiritEntries',
						'app.Incidents',
						'app.Stats',
						'app.ScoreDetails',
			'app.Attendances',
			'app.MailingLists',
				'app.Newsletters',
			'app.ActivityLogs',
			'app.Notes',
			'app.Settings',
		'app.I18n',
	];

	/**
	 * Test add method as an admin
	 *
	 * @return void
	 */
	public function testAddAsAdmin() {
		// Admins are allowed to add to schedules anywhere
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_ADMIN);
		$this->assertResponseRegExp('#<input type="radio" name="_options\[type\]" value="single" id="options-type-single">\s*Single blank, unscheduled game \(2 teams, one field\)#ms');
		$this->assertResponseRegExp('#<input type="radio" name="_options\[type\]" value="oneset_ratings_ladder" id="options-type-oneset_ratings_ladder">\s*Set of ratings-scheduled games for all teams \(8 teams, 4 games, one day\)#ms');
		$this->assertResponseContains('<input type="checkbox" name="_options[publish]" value="1" id="options-publish">');
		$this->assertResponseContains('<input type="checkbox" name="_options[double_header]" value="1" id="options-double-header">');
		$this->assertResponseContains('/schedules/add?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;playoff=1');

		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_ADMIN);
	}

	/**
	 * Test add method as a manager
	 *
	 * @return void
	 */
	public function testAddAsManager() {
		// Managers are allowed to add to schedules in their own affiliate
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_MANAGER);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_SUNDAY_SUB], PERSON_ID_MANAGER);
	}

	/**
	 * Test add method as a coordinator
	 *
	 * @return void
	 */
	public function testAddAsCoordinator() {
		// Coordinators are allowed to add to schedules in their divisions
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test add method as others
	 *
	 * @return void
	 */
	public function testAddAsOthers() {
		// Others are not allowed to add to schedules
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'add', 'division' => DIVISION_ID_MONDAY_LADDER]);
	}

	/**
	 * Test delete method as an admin
	 *
	 * @return void
	 */
	public function testDeleteAsAdmin() {
		// Admins are allowed to delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_ADMIN);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>');
		$this->assertResponseContains('/schedules/delete?division=' . DIVISION_ID_MONDAY_LADDER . '&amp;date=' . $date->toDateString() . '&amp;confirm=1');

		// Or any league
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_ADMIN);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>');

		// Or any affiliate
		$date = (new FrozenDate('last Sunday of May'))->addWeeks(1);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()], PERSON_ID_ADMIN);

		// Check the errors for dates with no games
		$date = (new FrozenDate('last Monday of May'))->addWeeks(10);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'#There are no games to delete on that date.#');

		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, ['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_MONDAY],
			'#There are no games to delete on that date.#');

		// Make sure that deleting games actually deletes them, and frees up game slots
		$slots = TableRegistry::get('GameSlots')->find()
			->where(['GameSlots.id IN' => [GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1, GAME_SLOT_ID_MONDAY_SUNNYBROOK_2_WEEK_1]])
			->toArray();
		$this->assertEquals(2, count($slots));
		$this->assertTrue($slots[0]->assigned);
		$this->assertTrue($slots[1]->assigned);

		$date = (new FrozenDate('last Monday of May'))->addWeeks(1);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString(), 'confirm' => true],
			PERSON_ID_ADMIN, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'#Deleted games on the requested date.#');

		$games = TableRegistry::get('Games')->find()
			->where(['Games.id IN' => [GAME_ID_LADDER_FINALIZED_HOME_WIN, GAME_ID_LADDER_CANCELLED]]);
		$this->assertEquals(0, $games->count());
		$slots = TableRegistry::get('GameSlots')->find()
			->where(['GameSlots.id IN' => [GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1, GAME_SLOT_ID_MONDAY_SUNNYBROOK_2_WEEK_1]])
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
		// Managers are allowed to delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>');

		// Or any league
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>');

		// But not other affiliates
		$date = (new FrozenDate('last Sunday of May'))->addWeeks(1);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
	}

	/**
	 * Test delete method as a coordinator
	 *
	 * @return void
	 */
	public function testDeleteAsCoordinator() {
		// Coordinators are allowed to delete schedules for any or their divisions
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('<p>You have requested to delete games on ' . $date->i18nFormat('MMMM d, yyyy') . '.</p><p>This will remove 2 games, of which 2 are published.</p>');

		// Or any league where they coordinate all of the divisions
		$date = (new FrozenDate('last Thursday of May'))->addWeeks(4);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_THURSDAY, 'date' => $date->toDateString()],
			PERSON_ID_COORDINATOR, ['controller' => 'Leagues', 'action' => 'schedule', 'league' => LEAGUE_ID_THURSDAY],
			'#There are no games to delete on that date.#');

		// But not other divisions
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(1);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);

		// And not leagues where they coordinate only some divisions
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test delete method as others
	 *
	 * @return void
	 */
	public function testDeleteAsOthers() {
		// Others are not allowed to delete schedules for any division
		$date = (new FrozenDate('last Monday of May'))->addWeeks(4);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()]);

		// Or any league
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'delete', 'league' => LEAGUE_ID_MONDAY, 'date' => $date->toDateString()]);
	}

	/**
	 * Test reschedule method as an admin
	 *
	 * @return void
	 */
	public function testRescheduleAsAdmin() {
		$date = (new FrozenDate('last Monday of May'))->addWeeks(3);

		// Admins are allowed to reschedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'reschedule', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reschedule method as a manager
	 *
	 * @return void
	 */
	public function testRescheduleAsManager() {
		$date = (new FrozenDate('last Monday of May'))->addWeeks(3);

		// Managers are allowed to reschedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'reschedule', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reschedule method as a coordinator
	 *
	 * @return void
	 */
	public function testRescheduleAsCoordinator() {
		$date = (new FrozenDate('last Monday of May'))->addWeeks(3);

		// Coordinators are allowed to reschedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'reschedule', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test reschedule method as others
	 *
	 * @return void
	 */
	public function testRescheduleAsOthers() {
		$date = (new FrozenDate('last Monday of May'))->addWeeks(3);

		// Others are not allowed to reschedule
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'reschedule', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'reschedule', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'reschedule', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'reschedule', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()]);
	}

	/**
	 * Test publish method as an admin
	 *
	 * @return void
	 */
	public function testPublishAsAdmin() {
		$game = TableRegistry::get('Games')->get(GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2);
		$this->assertFalse($game->published);

		// Admins are allowed to publish schedules anywhere
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN],
			'#Published games on the requested date.#');

		$game = TableRegistry::get('Games')->get(GAME_ID_TUESDAY_ROUND_ROBIN_WEEK_2);
		$this->assertTrue($game->published);

		$date = (new FrozenDate('last Sunday of May'))->addWeeks(2);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_SUNDAY_SUB],
			'#Published games on the requested date.#');
	}

	/**
	 * Test publish method as a manager
	 *
	 * @return void
	 */
	public function testPublishAsManager() {
		// Managers are allowed to publish schedules anywhere
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()],
			PERSON_ID_MANAGER, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN],
			'#Published games on the requested date.#');

		// But not in other affiliates
		$date = (new FrozenDate('last Sunday of May'))->addWeeks(2);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
	}

	/**
	 * Test publish method as a coordinator
	 *
	 * @return void
	 */
	public function testPublishAsCoordinator() {
		// Coordinators are allowed to publish schedules in their own divisions
		$date = (new FrozenDate('last Thursday of May'))->addWeeks(1);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN, 'date' => $date->toDateString()],
			PERSON_ID_COORDINATOR, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN],
			// There are no unpublished games on this date, so the message will be a failure. But at least it passed permission checks...
			'#Failed to publish games on the requested date.#');

		// But not in other divisions
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test publish method as others
	 *
	 * @return void
	 */
	public function testPublishAsOthers() {
		// Others are not allowed to publish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(2);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'publish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()]);
	}

	/**
	 * Test unpublish method as an admin
	 *
	 * @return void
	 */
	public function testUnpublishAsAdmin() {
		$game = TableRegistry::get('Games')->get(GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertTrue($game->published);

		// Admins are allowed to unpublish schedules anywhere
		$date = (new FrozenDate('last Monday of May'))->addWeeks(1);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'#Unpublished games on the requested date.#');

		$game = TableRegistry::get('Games')->get(GAME_ID_LADDER_FINALIZED_HOME_WIN);
		$this->assertFalse($game->published);

		$date = (new FrozenDate('last Sunday of May'))->addWeeks(1);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()],
			PERSON_ID_ADMIN, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_SUNDAY_SUB],
			'#Unpublished games on the requested date.#');
	}

	/**
	 * Test unpublish method as a manager
	 *
	 * @return void
	 */
	public function testUnpublishAsManager() {
		// Managers are allowed to unpublish schedules anywhere
		$date = (new FrozenDate('last Monday of May'))->addWeeks(1);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_MONDAY_LADDER, 'date' => $date->toDateString()],
			PERSON_ID_MANAGER, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_MONDAY_LADDER],
			'#Unpublished games on the requested date.#');

		// But not in other affiliates
		$date = (new FrozenDate('last Sunday of May'))->addWeeks(2);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_SUNDAY_SUB, 'date' => $date->toDateString()], PERSON_ID_MANAGER);
	}

	/**
	 * Test unpublish method as a coordinator
	 *
	 * @return void
	 */
	public function testUnpublishAsCoordinator() {
		// Coordinators are allowed to unpublish schedules in their own divisions
		$date = (new FrozenDate('last Thursday of May'))->addWeeks(1);
		$this->assertGetAsAccessRedirect(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN, 'date' => $date->toDateString()],
			PERSON_ID_COORDINATOR, ['controller' => 'Divisions', 'action' => 'schedule', 'division' => DIVISION_ID_THURSDAY_ROUND_ROBIN],
			// There are no unpublished games on this date, so the message will be a failure. But at least it passed permission checks...
			'#Unpublished games on the requested date.#');

		// But not in other divisions
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(1);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_COORDINATOR);
	}

	/**
	 * Test unpublish method as others
	 *
	 * @return void
	 */
	public function testUnpublishAsOthers() {
		// Others are not allowed to unpublish schedules at all
		$date = (new FrozenDate('last Tuesday of May'))->addWeeks(1);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Schedules', 'action' => 'unpublish', 'division' => DIVISION_ID_TUESDAY_ROUND_ROBIN, 'date' => $date->toDateString()]);
	}

	/**
	 * Test today method
	 *
	 * @return void
	 */
	public function testToday() {
		// Anyone is allowed to get today's schedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'today'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Schedules', 'action' => 'today']);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method
	 *
	 * @return void
	 */
	public function testDay() {
		// Anyone is allowed to get a day's schedule
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day'], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessOk(['controller' => 'Schedules', 'action' => 'day'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessOk(['controller' => 'Schedules', 'action' => 'day']);

		$this->markTestIncomplete('Not implemented yet.');
	}

}
