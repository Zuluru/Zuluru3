<?php
namespace App\Test\TestCase\Module;

use App\Core\ModuleRegistry;
use App\Test\Factory\GameFactory;
use Cake\I18n\FrozenDate;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

class LeagueTypeRoundrobinTest extends ModuleTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Module\LeagueTypeRoundrobin
	 */
	public $LeagueType;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->LeagueType = ModuleRegistry::getInstance()->load('LeagueType:roundrobin');
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
	 * Test compareTeams method
	 *
	 * @return void
	 */
	public function testCompareTeams() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
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
		$orange = $division->teams[4];
		$this->assertEquals(TEAM_ID_ORANGE, $orange->id);
		$purple = $division->teams[5];
		$this->assertEquals(TEAM_ID_PURPLE, $purple->id);
		$black = $division->teams[6];
		$this->assertEquals(TEAM_ID_BLACK, $black->id);
		$white = $division->teams[7];
		$this->assertEquals(TEAM_ID_WHITE, $white->id);

		$sort_context = ['tie_breaker' => ['hth']];

		// Sort the league and confirm standings
		// Points are Green: 2 (1-0), Yellow: 2 (1-1), Red: 1 (1-1 with a default), Blue: -1 (0-1 with a default)
		$this->LeagueType->sort($division, $division->league, $division->games);

		// Do some team-vs-team comparisons
		$this->assertEquals(-1, $this->LeagueType->compareTeams($yellow, $red, $sort_context));
		$this->assertEquals(1, $this->LeagueType->compareTeams($blue, $yellow, $sort_context));
		$this->assertEquals(-1, $this->LeagueType->compareTeams($green, $yellow, $sort_context));
		$this->assertEquals(1, $this->LeagueType->compareTeams($red, $green, $sort_context));
		$this->assertEquals(-1, $this->LeagueType->compareTeams($green, $blue, $sort_context));
		$this->assertEquals(-1, $this->LeagueType->compareTeams($red, $blue, $sort_context));
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
	 * Test createEmptyGame method
	 *
	 * @return void
	 */
	public function testCreateEmptyGame() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER);
		// Fixtures already have games scheduled for the first 4 weeks
		$division->_options = new Entity(['start_date' => (new FrozenDate('first Monday of June'))->addWeeks(4)]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$game = $this->LeagueType->createEmptyGame($division, $division->_options->start_date);

		$this->assertEquals(SEASON_GAME, $game->type);
		$this->assertEquals('normal', $game->status);
		$this->assertEquals($division->current_round, $game->round);
		$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $game->division_id);
		$this->assertNotNull($game->game_slot);
		$this->assertEquals($division->_options->start_date, $game->game_slot->game_date);
		$this->assertNull($game->home_team_id);
		$this->assertNull($game->away_team_id);
	}

	/**
	 * Test createEmptySet method
	 *
	 * @return void
	 */
	public function testCreateEmptySet() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER);
		// Fixtures already have games scheduled for the first 4 weeks
		$division->_options = new Entity(['start_date' => (new FrozenDate('first Monday of June'))->addWeeks(4)]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$games = $this->LeagueType->createEmptySet($division, $division->_options->start_date);

		$this->assertEquals(4, count($games));

		$i = 0;
		for ($week = 0; $week < 1; ++ $week) {
			for ($game = 0; $game < 4; ++ $game) {
				$this->assertEquals(SEASON_GAME, $games[$i]->type);
				$this->assertEquals('normal', $games[$i]->status);
				$this->assertEquals($division->current_round, $games[$i]->round);
				$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $games[$i]->division_id);
				$this->assertNotNull($games[$i]->game_slot);
				$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
				$this->assertNull($games[$i]->home_team_id);
				$this->assertNull($games[$i]->away_team_id);
				++ $i;
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertEquals(4, count(array_unique(collection($games)->extract('game_slot_id')->toArray())));
	}

	/**
	 * Test createScheduledSet method
	 *
	 * @return void
	 */
	public function testCreateScheduledSet() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);

		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER);
		// Fixtures already have games scheduled for the first 4 weeks
		$division->_options = new Entity(['start_date' => (new FrozenDate('first Monday of June'))->addWeeks(4)]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$games = $this->LeagueType->createScheduledSet($division, $division->_options->start_date);

		$this->assertEquals(4, count($games));

		$i = 0;
		for ($week = 0; $week < 1; ++ $week) {
			for ($game = 0; $game < 4; ++ $game) {
				$this->assertEquals(SEASON_GAME, $games[$i]->type);
				$this->assertEquals('normal', $games[$i]->status);
				$this->assertEquals($division->current_round, $games[$i]->round);
				$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $games[$i]->division_id);
				$this->assertNotNull($games[$i]->game_slot);
				$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
				$this->assertNotNull($games[$i]->home_team_id);
				$this->assertNotNull($games[$i]->away_team_id);
				++ $i;
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertEquals(4, count(array_unique(collection($games)->extract('game_slot_id')->toArray())));

		// Blue has not yet had a home game, make sure they have one now, and it's at their designated home field
		$blue_game = collection($games)->firstMatch(['home_team_id' => TEAM_ID_BLUE]);
		$this->assertNotNull($blue_game);
		$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2, $blue_game->game_slot->field_id);

		// Black has not yet had an away game, make sure they have one now
		$black_game = collection($games)->firstMatch(['away_team_id' => TEAM_ID_BLACK]);
		$this->assertNotNull($black_game);
	}

	/**
	 * Test createHalfRoundrobin method, standings split
	 *
	 * @return void
	 */
	public function testCreateHalfRoundrobinStandings() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);

		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER);
		// Fixtures already have games scheduled for the first 4 weeks
		$division->_options = new Entity(['start_date' => (new FrozenDate('first Monday of June'))->addWeeks(4)]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$games = $this->LeagueType->createHalfRoundrobin($division, $division->_options->start_date, 'standings');

		$this->assertEquals(12, count($games));

		$i = 0;
		for ($half = 0; $half < 2; ++ $half) {
			for ($week = 0; $week < 3; ++$week) {
				for ($game = 0; $game < 2; ++$game) {
					$this->assertEquals(SEASON_GAME, $games[$i]->type);
					$this->assertEquals('normal', $games[$i]->status);
					$this->assertEquals($division->current_round, $games[$i]->round);
					$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $games[$i]->division_id);
					$this->assertNotNull($games[$i]->game_slot);
					$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
					$this->assertNotNull($games[$i]->home_team_id);
					$this->assertNotNull($games[$i]->away_team_id);
					++$i;
				}
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertEquals(12, count(array_unique(collection($games)->extract('game_slot_id')->toArray())));

		// Standings coming into this are Purple, Green, Orange, Yellow, Black, Red, White, Blue
		// Purple, Green, Blue and White have less home games, so will be the home teams in week 1.
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.

		// Week 1 games should be 1v4, 2v3.
		$this->assertEquals(TEAM_ID_PURPLE, $games[0]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[0]->away_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[1]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[1]->away_team_id);

		// Week 2 games should be 1v2, 3v4.
		$this->assertEquals(TEAM_ID_PURPLE, $games[2]->home_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[2]->away_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[3]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[3]->away_team_id);

		// Week 3 games should be 1v3, 2v4.
		$this->assertEquals(TEAM_ID_PURPLE, $games[4]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[4]->away_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[5]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[5]->away_team_id);

		// Same schedule for the bottom half
		$this->assertEquals(TEAM_ID_BLUE, $games[6]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[6]->away_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[7]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[7]->away_team_id);

		$this->assertEquals(TEAM_ID_BLACK, $games[8]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[8]->away_team_id);
		$this->assertEquals(TEAM_ID_BLUE, $games[9]->home_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[9]->away_team_id);

		$this->assertEquals(TEAM_ID_BLUE, $games[10]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[10]->away_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[11]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[11]->away_team_id);

		// TODO: We can't do these assertions until we resolve the todo in LeagueTypeRoundrobin::createHalfRoundrobin
		//$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2, $games[7]->game_slot->field_id);
		//$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2, $games[8]->game_slot->field_id);
		//$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2, $games[10]->game_slot->field_id);
	}

	/**
	 * Test createHalfRoundrobin method, rating split
	 *
	 * @return void
	 */
	public function testCreateHalfRoundrobinRating() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);

		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER);
		// Fixtures already have games scheduled for the first 4 weeks
		$division->_options = new Entity(['start_date' => (new FrozenDate('first Monday of June'))->addWeeks(4)]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$games = $this->LeagueType->createHalfRoundrobin($division, $division->_options->start_date, 'rating');

		$this->assertEquals(12, count($games));

		$i = 0;
		for ($half = 0; $half < 2; ++ $half) {
			for ($week = 0; $week < 3; ++$week) {
				for ($game = 0; $game < 2; ++$game) {
					$this->assertEquals(SEASON_GAME, $games[$i]->type);
					$this->assertEquals('normal', $games[$i]->status);
					$this->assertEquals($division->current_round, $games[$i]->round);
					$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $games[$i]->division_id);
					$this->assertNotNull($games[$i]->game_slot);
					$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
					$this->assertNotNull($games[$i]->home_team_id);
					$this->assertNotNull($games[$i]->away_team_id);
					++$i;
				}
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertEquals(12, count(array_unique(collection($games)->extract('game_slot_id')->toArray())));

		// Standings coming into this are Blue, Red, Green, Yellow, Orange, Purple, Black, White
		// Blue, Yellow, Purple and Orange have less home games, so will be the home teams in week 1.
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.

		// Week 1 games should be 1v4, 2v3.
		$this->assertEquals(TEAM_ID_BLUE, $games[0]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[0]->away_team_id);
		$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2, $games[0]->game_slot->field_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[1]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[1]->away_team_id);
		$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1, $games[1]->game_slot->field_id);

		// Week 2 games should be 1v2, 3v3.
		$this->assertEquals(TEAM_ID_BLUE, $games[2]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[2]->away_team_id);
		$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2, $games[2]->game_slot->field_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[3]->home_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[3]->away_team_id);

		// Week 3 games should be 1v3, 2v4.
		$this->assertEquals(TEAM_ID_GREEN, $games[4]->home_team_id);
		$this->assertEquals(TEAM_ID_BLUE, $games[4]->away_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[5]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[5]->away_team_id);

		// Same schedule for the bottom half
		$this->assertEquals(TEAM_ID_WHITE, $games[6]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[6]->away_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[7]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[7]->away_team_id);

		$this->assertEquals(TEAM_ID_PURPLE, $games[8]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[8]->away_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[9]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[9]->away_team_id);

		$this->assertEquals(TEAM_ID_ORANGE, $games[10]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[10]->away_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[11]->home_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[11]->away_team_id);
	}

	/**
	 * Test createHalfRoundrobin method, mix split
	 *
	 * @return void
	 */
	public function testCreateHalfRoundrobinMix() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);

		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER);
		// Fixtures already have games scheduled for the first 4 weeks
		$division->_options = new Entity(['start_date' => (new FrozenDate('first Monday of June'))->addWeeks(4)]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$games = $this->LeagueType->createHalfRoundrobin($division, $division->_options->start_date, 'mix');

		$this->assertEquals(12, count($games));

		$i = 0;
		for ($half = 0; $half < 2; ++ $half) {
			for ($week = 0; $week < 3; ++$week) {
				for ($game = 0; $game < 2; ++$game) {
					$this->assertEquals(SEASON_GAME, $games[$i]->type);
					$this->assertEquals('normal', $games[$i]->status);
					$this->assertEquals($division->current_round, $games[$i]->round);
					$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $games[$i]->division_id);
					$this->assertNotNull($games[$i]->game_slot);
					$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
					$this->assertNotNull($games[$i]->home_team_id);
					$this->assertNotNull($games[$i]->away_team_id);
					++$i;
				}
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertEquals(12, count(array_unique(collection($games)->extract('game_slot_id')->toArray())));

		// Standings coming into this are Purple, Green, Orange, Yellow, Black, Red, White, Blue
		// so the pools will be Purple/Yellow/Black/Blue and Green/Orange/Red/White
		// Purple, Green, Blue and White have less home games, so will be the home teams in week 1.
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.

		// Week 1 games should be 1v4, 2v3.
		$this->assertEquals(TEAM_ID_BLUE, $games[0]->home_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[0]->away_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[1]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[1]->away_team_id);

		// Week 2 games should be 1v2, 3v3.
		$this->assertEquals(TEAM_ID_BLUE, $games[2]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[2]->away_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[3]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[3]->away_team_id);

		// Week 3 games should be 1v3, 2v4.
		$this->assertEquals(TEAM_ID_BLUE, $games[4]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[4]->away_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[5]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[5]->away_team_id);

		// Same schedule for the bottom half
		$this->assertEquals(TEAM_ID_ORANGE, $games[6]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[6]->away_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[7]->home_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[7]->away_team_id);

		$this->assertEquals(TEAM_ID_WHITE, $games[8]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[8]->away_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[9]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[9]->away_team_id);

		$this->assertEquals(TEAM_ID_GREEN, $games[10]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[10]->away_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[11]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[11]->away_team_id);

		$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2, $games[0]->game_slot->field_id);
		$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2, $games[2]->game_slot->field_id);
		$this->assertEquals(FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2, $games[4]->game_slot->field_id);
	}

	/**
	 * Test createFullRoundrobin method
	 *
	 * @return void
	 */
	public function testCreateFullRoundrobin() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);

		$division = $this->loadDivision(DIVISION_ID_MONDAY_LADDER);
		// Fixtures already have games scheduled for the first 4 weeks
		$division->_options = new Entity(['start_date' => (new FrozenDate('first Monday of June'))->addWeeks(4)]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$games = $this->LeagueType->createFullRoundrobin($division, $division->_options->start_date);

		$this->assertEquals(28, count($games));

		$i = 0;
		for ($week = 0; $week < 7; ++ $week) {
			for ($game = 0; $game < 4; ++ $game) {
				$this->assertEquals(SEASON_GAME, $games[$i]->type);
				$this->assertEquals('normal', $games[$i]->status);
				$this->assertEquals($division->current_round, $games[$i]->round);
				$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $games[$i]->division_id);
				$this->assertNotNull($games[$i]->game_slot);
				$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
				$this->assertNotNull($games[$i]->home_team_id);
				$this->assertNotNull($games[$i]->away_team_id);
				++ $i;
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertEquals(28, count(array_unique(collection($games)->extract('game_slot_id')->toArray())));

		// Standings coming into this are Purple, Green, Orange, Yellow, Black, Red, White, Blue
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.

		// Week 1 games should be 1v8, 2v7, 3v6, 4v5.
		$this->assertEquals(TEAM_ID_BLUE, $games[0]->home_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[0]->away_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[1]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[1]->away_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[2]->home_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[2]->away_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[3]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[3]->away_team_id);

		// Week 2 games should be 1v2, 3v8, 4v7, 5v6.
		$this->assertEquals(TEAM_ID_BLUE, $games[4]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[4]->away_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[5]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[5]->away_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[6]->home_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[6]->away_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[7]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[7]->away_team_id);

		// Week 3 games should be 1v3, 4v2, 5v8, 6v7.
		$this->assertEquals(TEAM_ID_BLUE, $games[8]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[8]->away_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[9]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[9]->away_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[10]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[10]->away_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[11]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[11]->away_team_id);

		// Week 4 games should be 1v4, 5v3, 6v2, 7v8.
		$this->assertEquals(TEAM_ID_GREEN, $games[12]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[12]->away_team_id);
		$this->assertEquals(TEAM_ID_BLUE, $games[13]->home_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[13]->away_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[14]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[14]->away_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[15]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[15]->away_team_id);

		// Standings coming into this are Purple, Green, Orange, Yellow, Black, Red, White, Blue
		// Week 5 games should be 1v5, 6v4, 7v3, 8v2.
		$this->assertEquals(TEAM_ID_YELLOW, $games[16]->home_team_id);
		$this->assertEquals(TEAM_ID_RED, $games[16]->away_team_id);
		$this->assertEquals(TEAM_ID_BLUE, $games[17]->home_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[17]->away_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[18]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[18]->away_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[19]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[19]->away_team_id);

		// Week 6 games should be 1v6, 7v5, 8v4, 2v3.
		$this->assertEquals(TEAM_ID_RED, $games[20]->home_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[20]->away_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[21]->home_team_id);
		$this->assertEquals(TEAM_ID_BLUE, $games[21]->away_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[22]->home_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[22]->away_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[23]->home_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[23]->away_team_id);

		// Week 7 games should be 1v7, 8v6, 2v5, 3v4.
		$this->assertEquals(TEAM_ID_RED, $games[24]->home_team_id);
		$this->assertEquals(TEAM_ID_BLUE, $games[24]->away_team_id);
		$this->assertEquals(TEAM_ID_WHITE, $games[25]->home_team_id);
		$this->assertEquals(TEAM_ID_PURPLE, $games[25]->away_team_id);
		$this->assertEquals(TEAM_ID_GREEN, $games[26]->home_team_id);
		$this->assertEquals(TEAM_ID_BLACK, $games[26]->away_team_id);
		$this->assertEquals(TEAM_ID_ORANGE, $games[27]->home_team_id);
		$this->assertEquals(TEAM_ID_YELLOW, $games[27]->away_team_id);
	}

}
