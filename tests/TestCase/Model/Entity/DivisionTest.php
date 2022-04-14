<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Division;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\LeagueFactory;
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
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		Configure::load('options');
		Configure::load('sports');
	}

	/**
	 * Test _getLeagueName, _getLongLeagueName(), _getFullLeagueName(),
	 */
	public function testGetVirtualLeagueNames(): void {
		$open = (new FrozenDate('next Monday'));
		$division = DivisionFactory::make(['name' => 'Competitive'])
			->with('Leagues', [
				'season' => 'Summer',
				'name' => 'Monday Night',
				'sport' => 'ultimate',
				'open' => $open,
			])
			->getEntity();

		$this->assertEquals('Monday Night Competitive', $division->league_name,'Wrong league name');
		$this->assertEquals('Summer Monday Night Ultimate Competitive', $division->long_league_name, 'Wrong long league name');
		$this->assertEquals($open->year . ' Summer Monday Night Ultimate Competitive', $division->full_league_name, 'Wrong long league name');
	}

	/**
	 * Test _getPlayoffDivisions()
	 */
	public function testGetPlayoffDivisions(): void {
		$league = LeagueFactory::make()
			->with('Divisions', DivisionFactory::make()->inPlayoff())
			->with('Divisions')
			->persist();

		$playoffDivision = $league->divisions[0];
		$noPlayoffDivisionInLeague = $league->divisions[1];
		$noPlayoffDivisionInOtherLeague = DivisionFactory::make()->persist();

		// Round 1 means you get to know about others in the playoffs
		$this->assertEquals([$playoffDivision->id], $noPlayoffDivisionInLeague->playoff_divisions, 'Should have gotten info');
		// Round 1 in a different league means you get nothing
		$this->assertEquals([], $noPlayoffDivisionInOtherLeague->playoff_divisions, 'In a league with no playoffs happening');
		// Playoffs means you get no info about other divisions
		$this->assertEquals([], $playoffDivision->playoff_divisions, 'In playoffs, shouldn\'t have gotten any info');
	}

	/**
	 * Get _getSeasonDivisions
	 */
	public function testGetSeasonDivisions(): void {
		$league = LeagueFactory::make()
			->with('Divisions', DivisionFactory::make()->inPlayoff())
			->with('Divisions', 2)
			->persist();

		$result = $league->divisions[0]->season_divisions;
		$expect = [$league->divisions[1]->id, $league->divisions[2]->id];
		$this->assertEquals($expect, $result);
	}

	/**
	 * Test _getSeasonDays()
	 */
	public function testGetSeasonDays(): void {
		$league = LeagueFactory::make()
			->with('Divisions',
				DivisionFactory::make()
					->inPlayoff()
					->with('Days')
			)
			->with('Divisions.Days')
			->persist();

		$playoffDivision = $league->divisions[0];
		$notPlayoffDivision = $league->divisions[1];

		$this->assertEquals([], $notPlayoffDivision->season_days);
		$this->assertEquals([$notPlayoffDivision->days[0]->id], $playoffDivision->season_days);
	}

	/**
	 * Test _getSisterDivisions()
	 */
	public function testGetSisterDivisions(): void {
		$league = LeagueFactory::make()
			->with('Divisions', DivisionFactory::make()->inPlayoff())
			->with('Divisions', 2)
			->persist();

		$playoffDivision = $league->divisions[0];
		$sisterDivisionsNotPlayoff1 = $league->divisions[1];
		$sisterDivisionsNotPlayoff2 = $league->divisions[2];

		$this->assertEquals([$sisterDivisionsNotPlayoff1->id, $sisterDivisionsNotPlayoff2->id], $sisterDivisionsNotPlayoff1->sister_divisions);
		$this->assertEquals([$playoffDivision->id], $playoffDivision->sister_divisions);
	}

	/**
	 * Test _getIsPlayoff()
	 */
	public function testGetIsPlayoff(): void {
		$this->assertFalse(DivisionFactory::make()->getEntity()->is_playoff);
		$this->assertTrue(DivisionFactory::make()->with('Leagues.Divisions')->inPlayoff()->persist()->is_playoff);
	}

	/**
	 * Test _getRosterDeadline method
	 */
	public function testGetRosterDeadline(): void {
		$this->assertEquals(
			FrozenDate::now(),
			DivisionFactory::make(['roster_deadline' => FrozenDate::now()])->getEntity()->rosterDeadline(),
			'Wrong deadline provided');
		$this->assertEquals(
			FrozenDate::now(),
			DivisionFactory::make(['close' => FrozenDate::now()])->getEntity()->rosterDeadline(),
			'Wrong deadline provided'
		);
	}

	/**
	 * Test _getRosterDeadlinePassed method
	 */
	public function testGetRosterDeadlinePassed(): void {
		$division = DivisionFactory::make(['close' => FrozenDate::now()->subDays(5)])->getEntity();
		$this->assertTrue($division->roster_deadline_passed, 'Deadline should have passed');
		$division = DivisionFactory::make(['close' => FrozenDate::now()->addDays(5)])->getEntity();
		$this->assertFalse($division->roster_deadline_passed, 'Deadline shouldn\'t have passed');
		$division = DivisionFactory::make(['roster_deadline' => FrozenDate::now()->addDays(5)])->getEntity();
		$this->assertFalse($division->roster_deadline_passed, 'Deadline shouldn\'t have passed');
	}

	/**
	 * Test addGameResult method
	 */
	public function testAddGameResult(): void {
		$this->markTestIncomplete('Not implemented yet. Very complex under the hood.');
	}

}
