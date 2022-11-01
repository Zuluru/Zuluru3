<?php
namespace App\Test\TestCase\Module;

use App\Core\ModuleRegistry;
use App\Model\Entity\League;
use App\Module\LeagueTypeRoundrobin;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\GameFactory;
use App\Test\Factory\LeagueFactory;
use App\Test\Scenario\LeagueWithFullScheduleScenario;
use Cake\ORM\Entity;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

class LeagueTypeRoundrobinTest extends ModuleTestCase {

	use ScenarioAwareTrait;

	/**
	 * Test subject
	 *
	 * @var \App\Module\LeagueTypeRoundrobin
	 */
	public $LeagueType;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$this->LeagueType = ModuleRegistry::getInstance()->load('LeagueType:roundrobin');
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->LeagueType);

		parent::tearDown();
	}

	/**
	 * Test compareTeams method
	 */
	public function testCompareTeams(): void {
		/** @var League $league */
		$league = LeagueFactory::make()
			->with('Divisions', DivisionFactory::make()
				->with('Teams', [
					['name' => 'Red', 'initial_seed' => 3, 'rating' => 1500],
					['name' => 'Blue', 'initial_seed' => 2, 'rating' => 1500],
					['name' => 'Green', 'initial_seed' => 1, 'rating' => 1450],
					['name' => 'Yellow', 'initial_seed' => 4, 'rating' => 1450],
				])
			)
			->persist();

		$division = $league->divisions[0];
		[$red, $blue, $green, $yellow] = $division->teams;

		$division->games = GameFactory::make([
			[
				'home_team_id' => $red->id,
				'away_team_id' => $yellow->id,
				'home_score' => 17,
				'away_score' => 5,
				'approved_by_id' => APPROVAL_AUTOMATIC,
				'status' => 'normal',
			],
			[
				'home_team_id' => $green->id,
				'away_team_id' => $blue->id,
				'home_score' => null,
				'away_score' => null,
				'approved_by_id' => null,
				'status' => 'cancelled',
			],
			[
				'home_team_id' => $red->id,
				'away_team_id' => $green->id,
				'home_score' => 0,
				'away_score' => 6,
				'approved_by_id' => APPROVAL_AUTOMATIC_HOME,
				'status' => 'home_default',
			],
			[
				'home_team_id' => $yellow->id,
				'away_team_id' => $blue->id,
				'home_score' => 6,
				'away_score' => 0,
				'approved_by_id' => APPROVAL_AUTOMATIC,
				'status' => 'away_default',
			],
			[
				'home_team_id' => $red->id,
				'away_team_id' => $blue->id,
				'home_score' => null,
				'away_score' => null,
				'approved_by_id' => null,
				'status' => 'normal',
			],
		])->persist();

		$sort_context = ['tie_breaker' => ['hth']];

		// Sort the league and confirm standings
		// Points are Green: 2 (1-0), Yellow: 2 (1-1), Red: 1 (1-1 with a default), Blue: -1 (0-1 with a default)
		$this->LeagueType->sort($division, $league, $division->games);

		// Do some team-vs-team comparisons
		$this->assertEquals(-1, LeagueTypeRoundrobin::compareTeams($yellow, $red, $sort_context));
		$this->assertEquals(1, LeagueTypeRoundrobin::compareTeams($blue, $yellow, $sort_context));
		$this->assertEquals(-1, LeagueTypeRoundrobin::compareTeams($green, $yellow, $sort_context));
		$this->assertEquals(1, LeagueTypeRoundrobin::compareTeams($red, $green, $sort_context));
		$this->assertEquals(-1, LeagueTypeRoundrobin::compareTeams($green, $blue, $sort_context));
		$this->assertEquals(-1, LeagueTypeRoundrobin::compareTeams($red, $blue, $sort_context));
	}

	/**
	 * Test schedulingFields method
	 */
	public function testSchedulingFields(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test schedulingFieldsRules method
	 */
	public function testSchedulingFieldsRules(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleOptions method
	 */
	public function testScheduleOptions(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test scheduleRequirements method
	 */
	public function testScheduleRequirements(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test createEmptyGame method
	 */
	public function testCreateEmptyGame(): void {
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['additional_slots' => 1]);
		$division = $league->divisions[0];

		// Games already scheduled for the first 4 weeks
		$date = $division->open->addWeeks(4);
		$division->_options = new Entity(['start_date' => $date]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$game = $this->LeagueType->createEmptyGame($division, $division->_options->start_date);

		$this->assertEquals(SEASON_GAME, $game->type);
		$this->assertEquals('normal', $game->status);
		$this->assertEquals($division->current_round, $game->round);
		$this->assertEquals($division->id, $game->division_id);
		$this->assertNotNull($game->game_slot);
		$this->assertEquals($division->_options->start_date, $game->game_slot->game_date);
		$this->assertNull($game->home_team_id);
		$this->assertNull($game->away_team_id);
	}

	/**
	 * Test createEmptySet method
	 */
	public function testCreateEmptySet(): void {
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['additional_slots' => 4]);
		$division = $league->divisions[0];

		// Games already scheduled for the first 4 weeks
		$date = $division->open->addWeeks(4);
		$division->_options = new Entity(['start_date' => $date]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$games = $this->LeagueType->createEmptySet($division, $division->_options->start_date);

		$this->assertCount(4, $games);

		$i = 0;
		for ($game = 0; $game < 4; ++ $game) {
			$this->assertEquals(SEASON_GAME, $games[$i]->type);
			$this->assertEquals('normal', $games[$i]->status);
			$this->assertEquals($division->current_round, $games[$i]->round);
			$this->assertEquals($division->id, $games[$i]->division_id);
			$this->assertNotNull($games[$i]->game_slot);
			$this->assertEquals($division->_options->start_date, $games[$i]->game_slot->game_date);
			$this->assertNull($games[$i]->home_team_id);
			$this->assertNull($games[$i]->away_team_id);
			++ $i;
		}

		// Ensure that different game slots were chosen for each game
		$this->assertCount(4, array_unique(collection($games)->extract('game_slot_id')->toArray()));
	}

	/**
	 * Test createScheduledSet method
	 */
	public function testCreateScheduledSet(): void {
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['additional_slots' => 4]);
		$division = $league->divisions[0];
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $division->teams;

		// Games already scheduled for the first 4 weeks
		$date = $division->open->addWeeks(4);
		$division->_options = new Entity(['start_date' => $date]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);

		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);
		$games = $this->LeagueType->createScheduledSet($division, $division->_options->start_date);

		$this->assertCount(4, $games);

		$i = 0;
		for ($game = 0; $game < 4; ++ $game) {
			$this->assertEquals(SEASON_GAME, $games[$i]->type);
			$this->assertEquals('normal', $games[$i]->status);
			$this->assertEquals($division->current_round, $games[$i]->round);
			$this->assertEquals($division->id, $games[$i]->division_id);
			$this->assertNotNull($games[$i]->game_slot);
			$this->assertEquals($division->_options->start_date, $games[$i]->game_slot->game_date);
			$this->assertNotNull($games[$i]->home_team_id);
			$this->assertNotNull($games[$i]->away_team_id);
			++ $i;
		}

		// Ensure that different game slots were chosen for each game
		$this->assertCount(4, array_unique(collection($games)->extract('game_slot_id')->toArray()));

		// Blue has not yet had a home game, make sure they have one now, and it's at their designated home field
		$blue_game = collection($games)->firstMatch(['home_team_id' => $blue->id]);
		$this->assertNotNull($blue_game);

		// Black has not yet had an away game, make sure they have one now
		$black_game = collection($games)->firstMatch(['away_team_id' => $black->id]);
		$this->assertNotNull($black_game);
	}

	/**
	 * Test createHalfRoundrobin method, standings split
	 */
	public function testCreateHalfRoundrobinStandings(): void {
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['additional_slots' => 4, 'additional_weeks' => 3]);
		$division = $league->divisions[0];
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $division->teams;

		// TODO: Eliminate this circular redundancy by always passing everything separately instead of assuming the data structure
		$division->league = $league;

		// Games already scheduled for the first 4 weeks
		$date = $division->open->addWeeks(4);
		$division->_options = new Entity(['start_date' => $date]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);

		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);
		$games = $this->LeagueType->createHalfRoundrobin($division, $division->_options->start_date);

		$this->assertCount(12, $games);

		$i = 0;
		for ($half = 0; $half < 2; ++ $half) {
			for ($week = 0; $week < 3; ++$week) {
				for ($game = 0; $game < 2; ++$game) {
					$this->assertEquals(SEASON_GAME, $games[$i]->type);
					$this->assertEquals('normal', $games[$i]->status);
					$this->assertEquals($division->current_round, $games[$i]->round);
					$this->assertEquals($division->id, $games[$i]->division_id);
					$this->assertNotNull($games[$i]->game_slot);
					$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
					$this->assertNotNull($games[$i]->home_team_id);
					$this->assertNotNull($games[$i]->away_team_id);
					++$i;
				}
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertCount(12, array_unique(collection($games)->extract('game_slot_id')->toArray()));

		// Standings coming into this are Purple, Green, Orange, Yellow, Black, Red, White, Blue
		// Purple, Green, Blue and White have less home games, so will be the home teams in week 1.
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.

		// Week 1 games should be 1v4, 2v3.
		$this->assertEquals($purple->id, $games[0]->home_team_id);
		$this->assertEquals($yellow->id, $games[0]->away_team_id);
		$this->assertEquals($green->id, $games[1]->home_team_id);
		$this->assertEquals($orange->id, $games[1]->away_team_id);

		// Week 2 games should be 1v2, 3v4.
		$this->assertEquals($purple->id, $games[2]->home_team_id);
		$this->assertEquals($green->id, $games[2]->away_team_id);
		$this->assertEquals($yellow->id, $games[3]->home_team_id);
		$this->assertEquals($orange->id, $games[3]->away_team_id);

		// Week 3 games should be 1v3, 2v4.
		$this->assertEquals($purple->id, $games[4]->home_team_id);
		$this->assertEquals($orange->id, $games[4]->away_team_id);
		$this->assertEquals($green->id, $games[5]->home_team_id);
		$this->assertEquals($yellow->id, $games[5]->away_team_id);

		// Same schedule for the bottom half
		$this->assertEquals($blue->id, $games[6]->home_team_id);
		$this->assertEquals($black->id, $games[6]->away_team_id);
		$this->assertEquals($white->id, $games[7]->home_team_id);
		$this->assertEquals($red->id, $games[7]->away_team_id);

		$this->assertEquals($black->id, $games[8]->home_team_id);
		$this->assertEquals($red->id, $games[8]->away_team_id);
		$this->assertEquals($blue->id, $games[9]->home_team_id);
		$this->assertEquals($white->id, $games[9]->away_team_id);

		$this->assertEquals($white->id, $games[10]->home_team_id);
		$this->assertEquals($black->id, $games[10]->away_team_id);
		$this->assertEquals($blue->id, $games[11]->home_team_id);
		$this->assertEquals($red->id, $games[11]->away_team_id);
	}

	/**
	 * Test createHalfRoundrobin method, rating split
	 */
	public function testCreateHalfRoundrobinRating(): void {
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['additional_slots' => 4, 'additional_weeks' => 3]);
		$division = $league->divisions[0];
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $division->teams;

		// Games already scheduled for the first 4 weeks
		$date = $division->open->addWeeks(4);
		$division->_options = new Entity(['start_date' => $date]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);

		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);
		$games = $this->LeagueType->createHalfRoundrobin($division, $division->_options->start_date, 'rating');

		$this->assertCount(12, $games);

		$i = 0;
		for ($half = 0; $half < 2; ++ $half) {
			for ($week = 0; $week < 3; ++$week) {
				for ($game = 0; $game < 2; ++$game) {
					$this->assertEquals(SEASON_GAME, $games[$i]->type);
					$this->assertEquals('normal', $games[$i]->status);
					$this->assertEquals($division->current_round, $games[$i]->round);
					$this->assertEquals($division->id, $games[$i]->division_id);
					$this->assertNotNull($games[$i]->game_slot);
					$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
					$this->assertNotNull($games[$i]->home_team_id);
					$this->assertNotNull($games[$i]->away_team_id);
					++$i;
				}
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertCount(12, array_unique(collection($games)->extract('game_slot_id')->toArray()));

		// Standings coming into this are Red Purple Green Blue Orange Black White Yellow
		// Blue, Yellow, Purple and Orange have less home games, so will be the home teams in week 1.
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.

		// Week 1 games should be 1v4, 2v3.
		$this->assertEquals($blue->id, $games[0]->home_team_id);
		$this->assertEquals($red->id, $games[0]->away_team_id);
		$this->assertEquals($purple->id, $games[1]->home_team_id);
		$this->assertEquals($green->id, $games[1]->away_team_id);

		// Week 2 games should be 1v2, 3v4.
		$this->assertEquals($purple->id, $games[2]->home_team_id);
		$this->assertEquals($red->id, $games[2]->away_team_id);
		$this->assertEquals($blue->id, $games[3]->home_team_id);
		$this->assertEquals($green->id, $games[3]->away_team_id);

		// Week 3 games should be 1v3, 2v4.
		$this->assertEquals($green->id, $games[4]->home_team_id);
		$this->assertEquals($red->id, $games[4]->away_team_id);
		$this->assertEquals($blue->id, $games[5]->home_team_id);
		$this->assertEquals($purple->id, $games[5]->away_team_id);

		// Same schedule for the bottom half
		$this->assertEquals($yellow->id, $games[6]->home_team_id);
		$this->assertEquals($orange->id, $games[6]->away_team_id);
		$this->assertEquals($white->id, $games[7]->home_team_id);
		$this->assertEquals($black->id, $games[7]->away_team_id);

		$this->assertEquals($orange->id, $games[8]->home_team_id);
		$this->assertEquals($black->id, $games[8]->away_team_id);
		$this->assertEquals($white->id, $games[9]->home_team_id);
		$this->assertEquals($yellow->id, $games[9]->away_team_id);

		$this->assertEquals($white->id, $games[10]->home_team_id);
		$this->assertEquals($orange->id, $games[10]->away_team_id);
		$this->assertEquals($yellow->id, $games[11]->home_team_id);
		$this->assertEquals($black->id, $games[11]->away_team_id);
	}

	/**
	 * Test createHalfRoundrobin method, mix split
	 */
	public function testCreateHalfRoundrobinMix(): void {
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['additional_slots' => 4, 'additional_weeks' => 3]);
		$division = $league->divisions[0];
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $division->teams;

		// TODO: Eliminate this circular redundancy by always passing everything separately instead of assuming the data structure
		$division->league = $league;

		// Games already scheduled for the first 4 weeks
		$date = $division->open->addWeeks(4);
		$division->_options = new Entity(['start_date' => $date]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);

		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);
		$games = $this->LeagueType->createHalfRoundrobin($division, $division->_options->start_date, 'mix');

		$this->assertCount(12, $games);

		$i = 0;
		for ($half = 0; $half < 2; ++ $half) {
			for ($week = 0; $week < 3; ++$week) {
				for ($game = 0; $game < 2; ++$game) {
					$this->assertEquals(SEASON_GAME, $games[$i]->type);
					$this->assertEquals('normal', $games[$i]->status);
					$this->assertEquals($division->current_round, $games[$i]->round);
					$this->assertEquals($division->id, $games[$i]->division_id);
					$this->assertNotNull($games[$i]->game_slot);
					$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
					$this->assertNotNull($games[$i]->home_team_id);
					$this->assertNotNull($games[$i]->away_team_id);
					++$i;
				}
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertCount(12, array_unique(collection($games)->extract('game_slot_id')->toArray()));

		// Standings coming into this are Purple, Green, Orange, Yellow, Black, Red, White, Blue
		// so the pools will be Purple/Yellow/Black/Blue and Green/Orange/Red/White
		// Purple, Green, Blue and White have less home games, so will be the home teams in week 1.
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.

		// Week 1 games should be 1v4, 2v3.
		$this->assertEquals($blue->id, $games[0]->home_team_id);
		$this->assertEquals($purple->id, $games[0]->away_team_id);
		$this->assertEquals($yellow->id, $games[1]->home_team_id);
		$this->assertEquals($black->id, $games[1]->away_team_id);

		// Week 2 games should be 1v2, 3v3.
		$this->assertEquals($purple->id, $games[2]->home_team_id);
		$this->assertEquals($yellow->id, $games[2]->away_team_id);
		$this->assertEquals($blue->id, $games[3]->home_team_id);
		$this->assertEquals($black->id, $games[3]->away_team_id);

		// Week 3 games should be 1v3, 2v4.
		$this->assertEquals($purple->id, $games[4]->home_team_id);
		$this->assertEquals($black->id, $games[4]->away_team_id);
		$this->assertEquals($blue->id, $games[5]->home_team_id);
		$this->assertEquals($yellow->id, $games[5]->away_team_id);

		// Same schedule for the bottom half
		$this->assertEquals($white->id, $games[6]->home_team_id);
		$this->assertEquals($green->id, $games[6]->away_team_id);
		$this->assertEquals($orange->id, $games[7]->home_team_id);
		$this->assertEquals($red->id, $games[7]->away_team_id);

		$this->assertEquals($green->id, $games[8]->home_team_id);
		$this->assertEquals($orange->id, $games[8]->away_team_id);
		$this->assertEquals($white->id, $games[9]->home_team_id);
		$this->assertEquals($red->id, $games[9]->away_team_id);

		$this->assertEquals($green->id, $games[10]->home_team_id);
		$this->assertEquals($red->id, $games[10]->away_team_id);
		$this->assertEquals($orange->id, $games[11]->home_team_id);
		$this->assertEquals($white->id, $games[11]->away_team_id);
	}

	/**
	 * Test createFullRoundrobin method
	 */
	public function testCreateFullRoundrobin(): void {
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['additional_slots' => 4, 'additional_weeks' => 7]);
		$division = $league->divisions[0];
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $division->teams;

		// TODO: Eliminate this circular redundancy by always passing everything separately instead of assuming the data structure
		$division->league = $league;

		// Games already scheduled for the first 4 weeks
		$date = $division->open->addWeeks(4);
		$division->_options = new Entity(['start_date' => $date]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);

		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);
		$games = $this->LeagueType->createFullRoundrobin($division, $division->_options->start_date);

		$this->assertCount(28, $games);

		$i = 0;
		for ($week = 0; $week < 7; ++ $week) {
			for ($game = 0; $game < 4; ++ $game) {
				$this->assertEquals(SEASON_GAME, $games[$i]->type);
				$this->assertEquals('normal', $games[$i]->status);
				$this->assertEquals($division->current_round, $games[$i]->round);
				$this->assertEquals($division->id, $games[$i]->division_id);
				$this->assertNotNull($games[$i]->game_slot);
				$this->assertEquals($division->_options->start_date->addWeeks($week), $games[$i]->game_slot->game_date);
				$this->assertNotNull($games[$i]->home_team_id);
				$this->assertNotNull($games[$i]->away_team_id);
				++ $i;
			}
		}

		// Ensure that different game slots were chosen for each game
		$this->assertCount(28, array_unique(collection($games)->extract('game_slot_id')->toArray()));

		// Standings coming into this are Purple, Green, Orange, Yellow, Black, Red, White, Blue
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.

		// Week 1 games should be 1v8, 2v7, 3v6, 4v5.
		$this->assertEquals($blue->id, $games[0]->home_team_id);
		$this->assertEquals($purple->id, $games[0]->away_team_id);
		$this->assertEquals($green->id, $games[1]->home_team_id);
		$this->assertEquals($white->id, $games[1]->away_team_id);
		$this->assertEquals($orange->id, $games[2]->home_team_id);
		$this->assertEquals($red->id, $games[2]->away_team_id);
		$this->assertEquals($yellow->id, $games[3]->home_team_id);
		$this->assertEquals($black->id, $games[3]->away_team_id);

		// Week 2 games should be 1v2, 3v8, 4v7, 5v6.
		$this->assertEquals($purple->id, $games[4]->home_team_id);
		$this->assertEquals($green->id, $games[4]->away_team_id);
		$this->assertEquals($blue->id, $games[5]->home_team_id);
		$this->assertEquals($orange->id, $games[5]->away_team_id);
		$this->assertEquals($white->id, $games[6]->home_team_id);
		$this->assertEquals($yellow->id, $games[6]->away_team_id);
		$this->assertEquals($black->id, $games[7]->home_team_id);
		$this->assertEquals($red->id, $games[7]->away_team_id);

		// Week 3 games should be 1v3, 4v2, 5v8, 6v7.
		$this->assertEquals($purple->id, $games[8]->home_team_id);
		$this->assertEquals($orange->id, $games[8]->away_team_id);
		$this->assertEquals($green->id, $games[9]->home_team_id);
		$this->assertEquals($yellow->id, $games[9]->away_team_id);
		$this->assertEquals($blue->id, $games[10]->home_team_id);
		$this->assertEquals($black->id, $games[10]->away_team_id);
		$this->assertEquals($white->id, $games[11]->home_team_id);
		$this->assertEquals($red->id, $games[11]->away_team_id);

		// Week 4 games should be 1v4, 5v3, 6v2, 7v8.
		$this->assertEquals($purple->id, $games[12]->home_team_id);
		$this->assertEquals($yellow->id, $games[12]->away_team_id);
		$this->assertEquals($orange->id, $games[13]->home_team_id);
		$this->assertEquals($black->id, $games[13]->away_team_id);
		$this->assertEquals($green->id, $games[14]->home_team_id);
		$this->assertEquals($red->id, $games[14]->away_team_id);
		$this->assertEquals($blue->id, $games[15]->home_team_id);
		$this->assertEquals($white->id, $games[15]->away_team_id);

		// Week 5 games should be 1v5, 6v4, 7v3, 8v2.
		$this->assertEquals($purple->id, $games[16]->home_team_id);
		$this->assertEquals($black->id, $games[16]->away_team_id);
		$this->assertEquals($yellow->id, $games[17]->home_team_id);
		$this->assertEquals($red->id, $games[17]->away_team_id);
		$this->assertEquals($white->id, $games[18]->home_team_id);
		$this->assertEquals($orange->id, $games[18]->away_team_id);
		$this->assertEquals($green->id, $games[19]->home_team_id);
		$this->assertEquals($blue->id, $games[19]->away_team_id);

		// Week 6 games should be 1v6, 7v5, 8v4, 2v3.
		$this->assertEquals($red->id, $games[20]->home_team_id);
		$this->assertEquals($purple->id, $games[20]->away_team_id);
		$this->assertEquals($black->id, $games[21]->home_team_id);
		$this->assertEquals($white->id, $games[21]->away_team_id);
		$this->assertEquals($yellow->id, $games[22]->home_team_id);
		$this->assertEquals($blue->id, $games[22]->away_team_id);
		$this->assertEquals($orange->id, $games[23]->home_team_id);
		$this->assertEquals($green->id, $games[23]->away_team_id);

		// Week 7 games should be 1v7, 8v6, 2v5, 3v4.
		$this->assertEquals($purple->id, $games[24]->home_team_id);
		$this->assertEquals($white->id, $games[24]->away_team_id);
		$this->assertEquals($blue->id, $games[25]->home_team_id);
		$this->assertEquals($red->id, $games[25]->away_team_id);
		$this->assertEquals($black->id, $games[26]->home_team_id);
		$this->assertEquals($green->id, $games[26]->away_team_id);
		$this->assertEquals($yellow->id, $games[27]->home_team_id);
		$this->assertEquals($orange->id, $games[27]->away_team_id);
	}

}
