<?php
namespace App\Test\TestCase\Module;

use App\Core\ModuleRegistry;
use Cake\I18n\FrozenDate;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class LeagueTypeRatingsLadderTest extends ModuleTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_facilities',
					'app.divisions_days',
					'app.game_slots',
						'app.divisions_gameslots',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.spirit_entries',
		'app.settings',
		'app.i18n',
	];

	/**
	 * Test subject
	 *
	 * @var \App\Module\LeagueTypeRatingsLadder
	 */
	public $LeagueType;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->LeagueType = ModuleRegistry::getInstance()->load('LeagueType:ratings_ladder');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LeagueType);

		parent::tearDown();
	}

	/**
	 * loadDivision method
	 *
	 * We usually don't need to do any containment, because startSchedule does that for us.
	 */
	public function loadDivision($id, $contain = []) {
		if ($contain === true) {
			$contain = [
				'Teams' => [
					'queryBuilder' => function (Query $q) {
						return $q->order(['initial_seed']);
					},
				],
				'Games',
			];
		}
		$contain[] = 'Leagues';
		return TableRegistry::get('Divisions')->get($id, ['contain' => $contain]);
	}

	/**
	 * Test links method
	 *
	 * @return void
	 */
	public function testLinks() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareTeams method
	 *
	 * @return void
	 */
	public function testCompareTeams() {
		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER, true);

		$this->assertEquals(8, count($division->teams));

		$green = $division->teams[0];
		$this->assertEquals(TEAM_ID_GREEN, $green->id);
		$blue = $division->teams[1];
		$this->assertEquals(TEAM_ID_BLUE, $blue->id);
		$red = $division->teams[2];
		$this->assertEquals(TEAM_ID_RED, $red->id);
		$yellow = $division->teams[3];
		$this->assertEquals(TEAM_ID_YELLOW, $yellow->id);

		$sort_context = [];

		// Initial ratings have Red and Blue at 1500, Green and Yellow at 1450; with no other results, ties are broken by initial seed
		$this->assertEquals(0, $this->LeagueType->compareTeams($red, $red, $sort_context));
		$this->assertEquals(1, $this->LeagueType->compareTeams($red, $blue, $sort_context));
		$this->assertEquals(-1, $this->LeagueType->compareTeams($green, $yellow, $sort_context));
		$this->assertEquals(-1, $this->LeagueType->compareTeams($red, $yellow, $sort_context));
		$this->assertEquals(1, $this->LeagueType->compareTeams($green, $blue, $sort_context));
	}

	/**
	 * Test schedulingFields method
	 *
	 * @return void
	 */
	public function testSchedulingFields() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedulingFieldsRules method
	 *
	 * @return void
	 */
	public function testSchedulingFieldsRules() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleOptions method
	 *
	 * @return void
	 */
	public function testScheduleOptions() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleRequirements method
	 *
	 * @return void
	 */
	public function testScheduleRequirements() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test createScheduledSet method
	 *
	 * @return void
	 */
	public function testCreateScheduledSet() {
		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);

		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER);
		// Fixtures already have games scheduled for the first 4 weeks
		$division->_options = new Entity(['start_date' => (new FrozenDate('first Monday of June'))->addWeeks(4)]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$games = $this->LeagueType->createScheduledSet($division, $division->_options->start_date);

		$this->assertEquals(4, count($games));

		foreach ($games as $game) {
			$this->assertEquals(SEASON_GAME, $game->type);
			$this->assertEquals('normal', $game->status);
			$this->assertEquals($division->current_round, $game->round);
			$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $game->division_id);
			$this->assertNotNull($game->game_slot);
			$this->assertEquals($division->_options->start_date, $game->game_slot->game_date);
			$this->assertNotNull($game->home_team_id);
			$this->assertNotNull($game->away_team_id);
		}

		// Ensure that different game slots were chosen for each game
		$this->assertEquals(4, count(array_unique(collection($games)->extract('game_slot_id')->toArray())));

		// Standings coming into this are Red, Blue, Green, Yellow, Purple, Orange, Black, White.
		// Because of previous matchups, Red/Blue/Green/Yellow can't play, and Purple/Orange/Black/White can't play.
		// This gives us Red v Purple, Blue v Orange, Green v Black and Yellow v White
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.
		$this->assertEquals(TEAM_ID_BLUE, $games[0]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[0]->away_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[1]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[1]->away_team_id);
		$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1, $games[1]->game_slot->field_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[2]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[2]->away_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[3]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[3]->away_team_id);
	}

}
