<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\GameSlot;
use App\Test\Factory\GameSlotFactory;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;

use function App\Lib\local_sunset_for_date;

class GameSlotTest extends TestCase {

	/**
	 * Test _getDisplayGameEnd method
	 */
	public function testGetDisplayGameEnd(): void {
		/** @var GameSlot $gameSlot */
		$gameSlot = GameSlotFactory::make()->getEntity();
		$this->assertEquals($gameSlot->get('game_end'), $gameSlot->display_game_end);

		$gameSlot->game_end = null;
		$this->assertEquals(local_sunset_for_date($gameSlot->game_date), $gameSlot->display_game_end);
	}

	/**
	 * Test _getStartTime method
	 */
	public function testGetStartTime(): void {
		/** @var GameSlot $gameSlot */
		$gameSlot = GameSlotFactory::make()->getEntity();
		$this->assertEquals((new FrozenTime('tomorrow'))->addHours(19), $gameSlot->start_time);
	}

	/**
	 * Test _getEndTime method
	 */
	public function testGetEndTime(): void {
		/** @var GameSlot $gameSlot */
		$gameSlot = GameSlotFactory::make()->getEntity();
		$this->assertEquals((new FrozenTime('tomorrow'))->addHours(21), $gameSlot->end_time);
	}

	/**
	 * Test overlaps method
	 */
	public function testOverlaps(): void {
		/** @var GameSlot[] $gameSlots */
		$gameSlots = GameSlotFactory::make([
			['game_start' => '18:00:00', 'game_end' => '19:00:00'],
			['game_start' => '18:30:00', 'game_end' => '19:30:00'],
			['game_start' => '19:00:00', 'game_end' => '20:00:00'],
			['game_start' => '18:15:00', 'game_end' => '18:45:00'],
		])->getEntities();

		$this->assertTrue($gameSlots[0]->overlaps($gameSlots[0]));
		$this->assertTrue($gameSlots[0]->overlaps($gameSlots[1]));
		$this->assertFalse($gameSlots[0]->overlaps($gameSlots[2]));
		$this->assertTrue($gameSlots[0]->overlaps($gameSlots[3]));

		$this->assertTrue($gameSlots[1]->overlaps($gameSlots[0]));
		$this->assertTrue($gameSlots[1]->overlaps($gameSlots[2]));
		$this->assertTrue($gameSlots[1]->overlaps($gameSlots[3]));

		$this->assertFalse($gameSlots[2]->overlaps($gameSlots[0]));
		$this->assertTrue($gameSlots[2]->overlaps($gameSlots[1]));
		$this->assertFalse($gameSlots[2]->overlaps($gameSlots[3]));
	}

}
