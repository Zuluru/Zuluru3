<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\GameSlot;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class GameSlotTest extends TestCase {

	/**
	 * Test Entity to use
	 *
	 * @var \App\Model\Entity\GameSlot
	 */
	public $GameSlot;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.game_slots'
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$gameSlots = TableRegistry::get('GameSlots');
		$this->GameSlot = $gameSlots->get(GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->GameSlot);

		parent::tearDown();
	}

	/**
	 * Test _getDisplayGameEnd method
	 *
	 * @return void
	 */
	public function testGetDisplayGameEnd() {
		// TODOLATER: This test breaks if the phpunit run starts before midnight, but this test isn't executed until after.
		// Probably some others in similar situations...
		$this->assertEquals(new FrozenTime('21:00:00'), $this->GameSlot->displayGameEnd);
	}

	/**
	 * Test _getStartTime method
	 *
	 * @return void
	 */
	public function testGetStartTime() {
		$this->assertEquals((new FrozenTime('first Monday of June'))->addHours(19), $this->GameSlot->start_time);
	}

	/**
	 * Test _getEndTime method
	 *
	 * @return void
	 */
	public function testGetEndTime() {
		$this->assertEquals((new FrozenTime('first Monday of June'))->addHours(21), $this->GameSlot->end_time);
	}

	/**
	 * Test overlaps method
	 *
	 * @return void
	 */
	public function testOverlaps() {
		$slot1 = new GameSlot(['game_date' => FrozenTime::now(), 'game_start' => new FrozenTime('18:00:00'), 'game_end' => new FrozenTime('19:00:00')]);
		$slot2 = new GameSlot(['game_date' => FrozenTime::now(), 'game_start' => new FrozenTime('18:30:00'), 'game_end' => new FrozenTime('19:30:00')]);
		$slot3 = new GameSlot(['game_date' => FrozenTime::now(), 'game_start' => new FrozenTime('19:00:00'), 'game_end' => new FrozenTime('20:00:00')]);
		$slot4 = new GameSlot(['game_date' => FrozenTime::now(), 'game_start' => new FrozenTime('18:15:00'), 'game_end' => new FrozenTime('18:45:00')]);

		$this->assertTrue($slot1->overlaps($slot1));
		$this->assertTrue($slot1->overlaps($slot2));
		$this->assertFalse($slot1->overlaps($slot3));
		$this->assertTrue($slot1->overlaps($slot4));

		$this->assertTrue($slot2->overlaps($slot1));
		$this->assertTrue($slot2->overlaps($slot3));
		$this->assertTrue($slot2->overlaps($slot4));

		$this->assertFalse($slot3->overlaps($slot1));
		$this->assertTrue($slot3->overlaps($slot2));
		$this->assertFalse($slot3->overlaps($slot4));
	}

}
