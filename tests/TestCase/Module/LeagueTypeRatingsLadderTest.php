<?php
namespace App\Test\TestCase\Module;

use App\Core\ModuleRegistry;
use App\Model\Entity\Team;
use App\Module\LeagueTypeRatingsLadder;
use App\Test\Factory\TeamFactory;
use App\Test\Scenario\LeagueWithFullScheduleScenario;
use Cake\ORM\Entity;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

class LeagueTypeRatingsLadderTest extends ModuleTestCase {

	use ScenarioAwareTrait;

	/**
	 * Test subject
	 *
	 * @var \App\Module\LeagueTypeRatingsLadder
	 */
	public $LeagueType;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$this->LeagueType = ModuleRegistry::getInstance()->load('LeagueType:ratings_ladder');
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->LeagueType);

		parent::tearDown();
	}

	/**
	 * Test links method
	 */
	public function testLinks(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareTeams method
	 */
	public function testCompareTeams(): void {
		/** @var Team[] $teams */
		$teams = TeamFactory::make([
			['name' => 'Red', 'initial_seed' => 3, 'rating' => 1500],
			['name' => 'Blue', 'initial_seed' => 2, 'rating' => 1500],
			['name' => 'Green', 'initial_seed' => 1, 'rating' => 1450],
			['name' => 'Yellow', 'initial_seed' => 4, 'rating' => 1450],
		])->getEntities();

		[$red, $blue, $green, $yellow] = $teams;
		$sort_context = [];

		// Initial ratings have Red and Blue at 1500, Green and Yellow at 1450; with no other results, ties are broken by initial seed
		$this->assertEquals(0, LeagueTypeRatingsLadder::compareTeams($red, $red, $sort_context));
		$this->assertEquals(1, LeagueTypeRatingsLadder::compareTeams($red, $blue, $sort_context));
		$this->assertEquals(-1, LeagueTypeRatingsLadder::compareTeams($green, $yellow, $sort_context));
		$this->assertEquals(-1, LeagueTypeRatingsLadder::compareTeams($red, $yellow, $sort_context));
		$this->assertEquals(1, LeagueTypeRatingsLadder::compareTeams($green, $blue, $sort_context));
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
	 * Test createScheduledSet method
	 */
	public function testCreateScheduledSet(): void {
		// Seed the random number generator with a fixed value, so that random determinations in field selection become fixed.
		mt_srand(123);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['additional_slots' => 4]);
		$division = $league->divisions[0];
		[$red, $yellow, $green, $blue, $orange, $purple, $black, $white] = $division->teams;

		// TODO: Eliminate this circular redundancy by always passing everything separately instead of assuming the data structure
		$division->league = $league;

		// Scenario already has games scheduled for the first 4 weeks
		$division->_options = new Entity(['start_date' => $division->open->addWeeks(4)]);
		$this->LeagueType->startSchedule($division, $division->_options->start_date);
		$games = $this->LeagueType->createScheduledSet($division, $division->_options->start_date);

		$this->assertCount(4, $games);

		foreach ($games as $game) {
			$this->assertEquals(SEASON_GAME, $game->type);
			$this->assertEquals('normal', $game->status);
			$this->assertEquals($division->current_round, $game->round);
			$this->assertEquals($division->id, $game->division_id);
			$this->assertNotNull($game->game_slot);
			$this->assertEquals($division->_options->start_date, $game->game_slot->game_date);
			$this->assertNotNull($game->home_team_id);
			$this->assertNotNull($game->away_team_id);
		}

		// Ensure that different game slots were chosen for each game
		$this->assertCount(4, array_unique(collection($games)->extract('game_slot_id')->toArray()));

		// Standings coming into this are Red, Blue, Green, Yellow, Purple, Orange, Black, White.
		// Because of previous matchups, Red/Blue/Green/Yellow can't play, and Purple/Orange/Black/White can't play.
		// This gives us Red v Purple, Green v Orange, Blue v Black and Yellow v White
		// Note that these tests will fail on any PHP version 7.1 or lower due to changes in the RNG as of 7.2.
		$this->assertEquals($purple->id, $games[0]->home_team_id);
		$this->assertEquals($red->id, $games[0]->away_team_id);
		$this->assertEquals($green->id, $games[1]->home_team_id);
		$this->assertEquals($orange->id, $games[1]->away_team_id);
		$this->assertEquals($blue->id, $games[2]->home_team_id);
		$this->assertEquals($black->id, $games[2]->away_team_id);
		$this->assertEquals($white->id, $games[3]->home_team_id);
		$this->assertEquals($yellow->id, $games[3]->away_team_id);
	}

}
