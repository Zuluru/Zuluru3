<?php
namespace App\Test\TestCase\Model\Entity;

use App\Test\Factory\LeagueFactory;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\League Test Case
 */
class LeagueTest extends TestCase {

	/**
	 * Test hasSpirit method
	 */
	public function testHasSpirit(): void {
		// Save to later revert
		$spirit = Configure::read('feature.spirit');

		$leagues = LeagueFactory::make([
		    ['numeric_sotg' => true],
            ['sotg_questions' => 'none'],
		    ['sotg_questions' => 'Foo'],
        ])->getEntities();

		Configure::write('feature.spirit', false);
		$this->assertFalse($leagues[0]->hasSpirit());

		Configure::write('feature.spirit', true);
		$this->assertTrue($leagues[0]->hasSpirit());
		$this->assertFalse($leagues[1]->hasSpirit());
		$this->assertTrue($leagues[2]->hasSpirit());

		// Revert the setting
		Configure::write('feature.spirit', $spirit);
	}

	/**
	 * Test hasCarbonFlip method
	 */
	public function testHasCarbonFlip(): void {
		// Save to later revert
		$flip = Configure::read('scoring.carbon_flip');
        $leagues = LeagueFactory::make([
            ['carbon_flip' => true],
            ['carbon_flip' => false],
        ])->getEntities();

		Configure::write('scoring.carbon_flip', false);
		$this->assertFalse($leagues[0]->hasCarbonFlip());

		Configure::write('scoring.carbon_flip', true);
        $this->assertTrue($leagues[0]->hasCarbonFlip());
        $this->assertFalse($leagues[1]->hasCarbonFlip());

		// Revert the setting
		Configure::write('scoring.carbon_flip', $flip);
	}

	/**
	 * Test hasStats method
	 */
	public function testHasStats(): void {
		// Save to later revert
		$stats = Configure::read('scoring.stat_tracking');
        $leagues = LeagueFactory::make([
            ['schedule_type' => 'none'],
            ['stat_tracking' => 'foo'],
            ['stat_tracking' => 'never'],
        ])->getEntities();

		Configure::write('scoring.stat_tracking', false);
		$this->assertFalse($leagues[0]->hasStats());


		Configure::write('scoring.stat_tracking', true);
        $this->assertFalse($leagues[0]->hasStats());
        $this->assertTrue($leagues[1]->hasStats());
        $this->assertFalse($leagues[2]->hasStats());

		// Revert the setting
		Configure::write('scoring.carbon_flip', $stats);
	}

	/**
	 * Test _getLongName()
	 */
	public function testGetLongName(): void {
        $leagues = LeagueFactory::make([
            ['name' => 'Monday Night', 'season' => 'Summer', 'sport' => 'ultimate'],
            ['name' => 'Tuesday Night Summer', 'season' => 'Summer', 'sport' => 'baseball'],
        ])->getEntities();

		Configure::write('options.sport', ['ultimate', 'baseball']);
		$this->assertEquals('Summer Monday Night Ultimate', $leagues[0]->long_name);
		$this->assertEquals('Tuesday Night Summer Baseball', $leagues[1]->long_name);

		Configure::write('options.sport', ['ultimate']);
		$this->assertEquals('Summer Monday Night', $leagues[0]->long_name);
		$this->assertEquals('Tuesday Night Summer', $leagues[1]->long_name);
	}

	/**
	 * Test _getFullName()
	 */
	public function testGetFullName(): void {
		$year = FrozenTime::now()->year;
        $leagues = LeagueFactory::make([
            ['name' => 'Monday Night', 'season' => 'Summer', 'open' => FrozenDate::now()],
            ['name' => $year . ' Monday Night', 'season' => 'Summer', 'open' => FrozenDate::now()],
            ['name' => 'Tuesday Night', 'season' => 'Summer', 'open' => null],
        ])->getEntities();

		Configure::write('options.sport', ['ultimate', 'baseball']);
		$this->assertEquals($year . ' ' . $leagues[0]->long_name, $leagues[0]->full_name);
		$this->assertEquals($leagues[1]->long_name, $leagues[1]->full_name);
		$this->assertEquals($leagues[2]->long_name, $leagues[2]->full_name);
	}

	/**
	 * Test _getLongSeason()
	 */
	public function testGetLongSeason(): void {
		$year = FrozenTime::now()->year;
        $leagues = LeagueFactory::make([
            ['season' => 'Summer', 'open' => FrozenDate::now()],
            ['season' => 'None', 'open' => FrozenDate::now()],
            ['season' => 'Summer', 'open' => null],
            ['season' => null, 'open' => null],
        ])->getEntities();

		$this->assertEquals("$year Summer", $leagues[0]->long_season);
		$this->assertEquals("$year", $leagues[1]->long_season);
		$this->assertEquals("Summer", $leagues[2]->long_season);
		$this->assertEquals(null, $leagues[3]->long_season);
	}

	/**
	 * Test _getGetTieBreakers()
	 */
	public function testGetTieBreakers(): void {
	    $league = LeagueFactory::make(['tie_breaker' => 'win,hth,hthpm,pm,gf,loss',])->getEntity();
		$this->assertEquals(['win', 'hth', 'hthpm', 'pm', 'gf', 'loss'], $league->tie_breakers);
	}

}
