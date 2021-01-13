<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\GameSlot;
use App\Test\Factory\GameFactory;
use App\Test\Factory\GameSlotFactory;
use Cake\Chronos\Date;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;
use function App\Lib\local_sunset_for_date;

class GameSlotTest extends TestCase {

	/**
	 * Test _getDisplayGameEnd method
	 *
	 * @return void
	 */
	public function testGetDisplayGameEnd() {
        $gameSlot = GameSlotFactory::make([
            'game_date' => Date::now(),
            'game_end' => null,
        ])->getEntity();
		$this->assertEquals(local_sunset_for_date($gameSlot->game_date), $gameSlot->displayGameEnd);

        $gameSlot = GameSlotFactory::make([
            'game_end' => FrozenTime::now(),
        ])->getEntity();
        $this->assertEquals($gameSlot->get('game_end'), $gameSlot->displayGameEnd);
	}

	/**
	 * Test _getStartTime method
	 *
	 * @return void
	 */
	public function testGetStartTime() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$this->assertEquals((new FrozenTime('first Monday of June'))->addHours(19), $this->GameSlot->start_time);
	}

	/**
	 * Test _getEndTime method
	 *
	 * @return void
	 */
	public function testGetEndTime() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$this->assertEquals((new FrozenTime('first Monday of June'))->addHours(21), $this->GameSlot->end_time);
	}

	/**
	 * Test overlaps method
	 *
	 * @return void
	 */
	public function testOverlaps() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
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
