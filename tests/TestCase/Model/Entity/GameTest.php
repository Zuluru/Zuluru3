<?php
namespace App\Test\TestCase\Model\Entity;

use App\Core\ModuleRegistry;
use App\Model\Entity\Game;
use App\Test\Factory\GameFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Game Test Case
 */
class GameTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Entity\Game
	 */
	public $Game1;

	/**
	 * Test subject
	 *
	 * @var \App\Model\Entity\Game
	 */
	public $Game2;

	/**
	 * Test subject
	 *
	 * @var \App\Model\Entity\Game
	 */
	public $Game3;

	/**
	 * Test subject
	 *
	 * @var \App\Model\Entity\Game
	 */
	public $Game4;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$games = TableRegistry::get('Games');
		$contain = ['HomeTeam', 'AwayTeam', 'ScoreEntries', 'SpiritEntries', 'Divisions' => ['Leagues']];
		$this->Game1 = $games->get(GAME_ID_LADDER_MATCHED_SCORES, ['contain' => $contain]);
		$this->Game2 = $games->get(GAME_ID_LADDER_MISMATCHED_SCORES, ['contain' => $contain]);
		$this->Game3 = $games->get(GAME_ID_LADDER_HOME_SCORE_ONLY, ['contain' => $contain]);
		$this->Game4 = $games->get(GAME_ID_LADDER_NO_SCORES, ['contain' => $contain]);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Game1);
		unset($this->Game2);
		unset($this->Game3);
		unset($this->Game4);

		parent::tearDown();
	}

	/**
	 * Test finalize method
	 *
	 * @return void
	 */
	public function testFinalize(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test updateDependencies method
	 *
	 * @return void
	 */
	public function testUpdateDependencies(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test adjustScoreAndRatings method
	 *
	 * @return void
	 */
	public function testAdjustScoreAndRatings(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test undoRatings method
	 *
	 * @return void
	 */
	public function testUndoRatings(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test modifyTeamRatings method
	 *
	 * @return void
	 */
	public function testModifyTeamRatings(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test getScoreEntry method
	 *
	 * @return void
	 */
	public function testGetScoreEntry(): void {
		$entry = $this->Game1->getScoreEntry(TEAM_ID_RED);
		$this->assertEquals(SCORE_ID_LADDER_MATCHED_SCORES_HOME, $entry->id);

		$entry = $this->Game1->getScoreEntry(TEAM_ID_BLUE);
		$this->assertEquals(SCORE_ID_LADDER_MATCHED_SCORES_AWAY, $entry->id);

		$entry = $this->Game4->getScoreEntry(TEAM_ID_BLUE);
		$this->assertTrue($entry->isNew());
		$this->assertEquals(TEAM_ID_BLUE, $entry->team_id);
		$this->assertEquals(GAME_ID_LADDER_NO_SCORES, $entry->game_id);
		$this->assertNull($entry->person_id);
	}

	/**
	 * Test getSpiritEntry method
	 *
	 * @return void
	 */
	public function testGetSpiritEntry(): void {
		$this->assertNotNull($this->Game1->division);
		$this->assertNotNull($this->Game1->division->league);
		$spirit_obj = ModuleRegistry::getInstance()->load("Spirit:{$this->Game1->division->league->sotg_questions}");
		$this->assertNotNull($spirit_obj);
		$entry = $this->Game1->getSpiritEntry(TEAM_ID_RED, $spirit_obj);
		$this->assertEquals(SPIRIT_ID_LADDER_MATCHED_SCORES_HOME, $entry->id);

		$entry = $this->Game1->getSpiritEntry(TEAM_ID_BLUE, $spirit_obj);
		$this->assertEquals(SPIRIT_ID_LADDER_MATCHED_SCORES_AWAY, $entry->id);

		$this->assertFalse($this->Game4->getSpiritEntry(TEAM_ID_BLUE, $spirit_obj));

		$entry = $this->Game4->getSpiritEntry(TEAM_ID_BLUE, $spirit_obj, true);
		$this->assertNotEmpty($entry);
		$this->assertEquals($this->Game4->home_team_id, $entry->team_id);
		$this->assertEquals($this->Game4->away_team_id, $entry->created_team_id);
		$this->assertEquals(3, $entry->q1);
		$this->assertEquals(3, $entry->q2);
		$this->assertEquals(3, $entry->q3);
		$this->assertEquals(3, $entry->q4);
		$this->assertEquals(3, $entry->q5);
		$this->assertEquals(0, $entry->q6);
	}

	/**
	 * Test getBestScoreEntry method
	 *
	 * @return void
	 */
	public function testGetBestScoreEntry(): void {
		$entry = $this->Game1->getBestScoreEntry();
		$this->assertNotEmpty($entry);
		$this->assertEquals(SCORE_ID_LADDER_MATCHED_SCORES_HOME, $entry->id);

		$this->assertNull($this->Game2->getBestScoreEntry());

		// TODO: Add fixtures for testing all the other various "in progress" possibilities
		$entry = $this->Game3->getBestScoreEntry();
		$this->assertNotEmpty($entry);
		$this->assertEquals(SCORE_ID_LADDER_HOME_SCORE_ONLY_HOME, $entry->id);

		$this->assertFalse($this->Game4->getBestScoreEntry());
	}

	/**
	 * Test getScoreReminderEmail method
	 *
	 * @return void
	 */
	public function testGetScoreReminderEmail(): void {
		$entity = $this->Game1->getScoreReminderEmail(TEAM_ID_RED);
		$this->assertNotEmpty($entity);
		$this->assertEquals(1, $entity->id);

		$this->assertFalse($this->Game1->getScoreReminderEmail(TEAM_ID_BLUE));
	}

	/**
	 * Test isFinalized method
	 *
	 * @return void
	 */
	public function testIsFinalized(): void {
		$this->assertFalse($this->Game1->isFinalized());
		$this->Game1->finalize();
		$this->Game1->adjustScoreAndRatings();
		$this->assertTrue($this->Game1->isFinalized());

		$this->assertFalse($this->Game2->isFinalized());
	}

	/**
	 * Test readDependencies method
	 *
	 * @return void
	 */
	public function testReadDependencies(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
