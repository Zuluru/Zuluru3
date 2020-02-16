<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\League;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\League Test Case
 */
class LeagueTest extends TestCase {

	/**
	 * Test subject 1
	 *
	 * @var \App\Model\Entity\League
	 */
	public $League1;

	/**
	 * Test subject 2
	 *
	 * @var \App\Model\Entity\League
	 */
	public $League2;

	/**
	 * Test subject 3
	 *
	 * @var \App\Model\Entity\League
	 */
	public $League3;

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
			'app.leagues',
			'app.settings',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$leagues = TableRegistry::get('Leagues');
		$this->League1 = $leagues->get(LEAGUE_ID_MONDAY);
		$this->League2 = $leagues->get(LEAGUE_ID_TUESDAY);
		$this->League3 = $leagues->get(LEAGUE_ID_THURSDAY);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->League1);
		unset($this->League2);
		unset($this->League3);

		parent::tearDown();
	}

	/**
	 * Test hasSpirit method
	 *
	 * @return void
	 */
	public function testHasSpirit() {
		// Save to later revert
		$spirit = Configure::read('feature.spirit');

		Configure::write('feature.spirit', false);
		$this->assertFalse($this->League1->hasSpirit());
		$this->assertFalse($this->League2->hasSpirit());
		$this->assertFalse($this->League3->hasSpirit());

		Configure::write('feature.spirit', true);
		$this->assertTrue($this->League1->hasSpirit());
		$this->assertFalse($this->League2->hasSpirit());
		$this->assertTrue($this->League3->hasSpirit());

		// Revert the setting
		Configure::write('feature.spirit', $spirit);
	}

	/**
	 * Test hasCarbonFlip method
	 *
	 * @return void
	 */
	public function testHasCarbonFlip() {
		// Save to later revert
		$flip = Configure::read('scoring.carbon_flip');

		Configure::write('scoring.carbon_flip', false);
		$this->assertFalse($this->League2->hasCarbonFlip());
		$this->assertFalse($this->League3->hasCarbonFlip());

		Configure::write('scoring.carbon_flip', true);
		$this->assertTrue($this->League2->hasCarbonFlip());
		$this->assertFalse($this->League3->hasCarbonFlip());

		// Revert the setting
		Configure::write('scoring.carbon_flip', $flip);
	}

	/**
	 * Test hasStats method
	 *
	 * @return void
	 */
	public function testHasStats() {
		// Save to later revert
		$stats = Configure::read('scoring.stat_tracking');

		Configure::write('scoring.stat_tracking', false);
		$this->assertFalse($this->League2->hasStats());
		$this->assertFalse($this->League3->hasStats());

		Configure::write('scoring.stat_tracking', true);
		$this->assertFalse($this->League2->hasStats());
		$this->assertTrue($this->League3->hasStats());

		// Revert the setting
		Configure::write('scoring.carbon_flip', $stats);
	}

	/**
	 * Test _getLongName()
	 */
	public function testGetLongName() {
		Configure::write('options.sport', ['ultimate', 'baseball']);
		$this->assertEquals('Summer Monday Night Ultimate', $this->League1->long_name);
		$this->assertEquals('Tuesday Night Baseball', $this->League2->long_name);

		Configure::write('options.sport', ['ultimate']);
		$this->assertEquals('Summer Monday Night', $this->League1->long_name);
		$this->assertEquals('Tuesday Night', $this->League2->long_name);
	}

	/**
	 * Test _getFullName()
	 */
	public function testGetFullName() {
		$year = FrozenTime::now()->year;
		Configure::write('options.sport', ['ultimate', 'baseball']);
		$this->assertEquals("$year Summer Monday Night Ultimate", $this->League1->full_name);
		$this->assertEquals("$year Tuesday Night Baseball", $this->League2->full_name);

		Configure::write('options.sport', ['ultimate']);
		$this->assertEquals("$year Summer Monday Night", $this->League1->full_name);
		$this->assertEquals("$year Tuesday Night", $this->League2->full_name);
	}

	/**
	 * Test _getLongSeason()
	 */
	public function testGetLongSeason() {
		$year = FrozenTime::now()->year;
		$this->assertEquals("$year Summer", $this->League1->long_season);
		$this->assertEquals($year, $this->League2->long_season);
		$this->assertEquals("$year Fall", $this->League3->long_season);

	}

	/**
	 * Test _getGetTieBreakers()
	 */
	public function testGetTieBreakers() {
		$this->assertEquals(['win', 'hth', 'hthpm', 'pm', 'gf', 'loss'], $this->League1->tie_breakers);
	}

}
