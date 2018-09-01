<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Division;
use Cake\Chronos\ChronosInterface;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Model\Entity\Division Test Case
 */
class DivisionTest extends TestCase {

	/**
	 * Test subjects
	 *
	 * @var \App\Model\Entity\Division
	 */
	public $LadderDivision;

	/**
	 * @var \App\Model\Entity\Division
	 */
	public $RoundRobinDivision;

	/**
	 * @var \App\Model\Entity\Division
	 */
	public $PlayoffDivision;

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
			'app.groups',
				'app.groups_people',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.divisions_days',
					'app.pools',
						'app.pools_teams',
					'app.games',
			'app.settings',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		Configure::load('options');
		Configure::load('sports');

		$divisions_table = TableRegistry::get('Divisions');
		$this->LadderDivision = $divisions_table->get(DIVISION_ID_MONDAY_LADDER, ['contain' => ['Leagues', 'Days']]);
		$this->RoundRobinDivision = $divisions_table->get(DIVISION_ID_TUESDAY_ROUND_ROBIN, ['contain' => ['Leagues', 'Days']]);
		$this->PlayoffDivision = $divisions_table->get(DIVISION_ID_MONDAY_PLAYOFF, ['contain' => ['Leagues', 'Days']]);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LadderDivision);
		unset($this->RoundRobinDivision);
		unset($this->PlayoffDivision);

		parent::tearDown();
	}

	/**
	 * Test _getLeagueName
	 */
	public function testGetLeagueName() {
		$result = $this->LadderDivision->league_name;
		$this->assertEquals('Monday Night Competitive', $result, 'Wrong league name');
	}

	/**
	 * Test _getLongLeagueName()
	 */
	public function testGetLongLeagueName() {
		$result = $this->LadderDivision->long_league_name;
		$this->assertEquals('Summer Monday Night Ultimate Competitive', $result, 'Wrong long league name');
	}

	/**
	 * Test _getFullLeagueName()
	 */
	public function testGetFullLeagueName() {
		$result = $this->LadderDivision->full_league_name;
		$year = (new FrozenDate('next Monday'))->year;
		$this->assertEquals("$year Summer Monday Night Ultimate Competitive", $result, 'Wrong full league name');
	}

	/**
	 * Test _getPlayoffDivisions()
	 */
	public function testGetPlayoffDivisions() {
		// Round 1 means you get to know about others in the playoffs
		$this->assertEquals([$this->PlayoffDivision->id], $this->LadderDivision->playoff_divisions, 'Should have gotten info');
		// Round 1 in a different league means you get nothing
		$this->assertEquals([], $this->RoundRobinDivision->playoff_divisions, 'In a league with no playoffs happening');
		// Playoffs means you get no info about other divisions
		$this->assertEquals([], $this->PlayoffDivision->playoff_divisions, 'In playoffs, shouldn\'t have gotten any info');
	}

	/**
	 * Get _getSeasonDivisions
	 */
	public function testGetSeasonDivisions() {
		$result = $this->PlayoffDivision->season_divisions;
		$this->assertEquals([DIVISION_ID_MONDAY_LADDER, DIVISION_ID_MONDAY_LADDER2], $result);
	}

	/**
	 * Test _getSeasonDays()
	 */
	public function testGetSeasonDays() {
		$this->assertEquals([], $this->LadderDivision->season_days);
		$this->assertEquals([ChronosInterface::MONDAY], $this->PlayoffDivision->season_days);
	}

	/**
	 * Test _getSisterDivisions()
	 */
	public function testGetSisterDivisions() {
		$this->assertEquals([DIVISION_ID_MONDAY_LADDER, DIVISION_ID_MONDAY_LADDER2], $this->LadderDivision->sister_divisions);
		$this->assertEquals([DIVISION_ID_MONDAY_PLAYOFF], $this->PlayoffDivision->sister_divisions);
	}

	/**
	 * Test _getIsPlayoff()
	 */
	public function testGetIsPlayoff() {
		$this->assertFalse($this->LadderDivision->is_playoff);
		$this->assertTrue($this->PlayoffDivision->is_playoff);
	}

	/**
	 * Test _getRosterDeadline method
	 *
	 * @return void
	 */
	public function testGetRosterDeadline() {
		$this->assertEquals(new FrozenDate('last Monday of August'), $this->LadderDivision->rosterDeadline(), 'Wrong deadline provided');
		$this->assertEquals(new FrozenDate('first Monday of September'), $this->PlayoffDivision->rosterDeadline(), 'Wrong deadline provided');
	}

	/**
	 * Test _getRosterDeadlinePassed method
	 *
	 * @return void
	 */
	public function testGetRosterDeadlinePassed() {
		$this->PlayoffDivision->close = FrozenDate::now()->subDays(5);
		$this->assertTrue($this->PlayoffDivision->roster_deadline_passed, 'Deadline should have passed');
		$this->PlayoffDivision->close = FrozenDate::now()->addDays(5);
		$this->assertFalse($this->PlayoffDivision->roster_deadline_passed, 'Deadline shouldn\'t have passed');
		$this->LadderDivision->roster_deadline = FrozenDate::now()->addDays(5);
		$this->assertFalse($this->LadderDivision->roster_deadline_passed, 'Deadline shouldn\'t have passed');
	}

	/**
	 * Test addGameResult method
	 *
	 * @return void
	 */
	public function testAddGameResult() {
		$this->markTestIncomplete('Not implemented yet. Very complex under the hood.');
	}

}
