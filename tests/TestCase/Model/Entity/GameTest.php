<?php
namespace App\Test\TestCase\Model\Entity;

use App\Core\ModuleRegistry;
use App\Test\Factory\ActivityLogFactory;
use App\Test\Factory\ScoreEntryFactory;
use App\Test\Scenario\SingleGameScenario;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Model\Entity\Game Test Case
 */
class GameTest extends TestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.UserGroups',
	];

	public $autoFixtures = false;

	/**
	 * Test finalize method
	 */
	public function testFinalize(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test updateDependencies method
	 */
	public function testUpdateDependencies(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test adjustScoreAndRatings method
	 */
	public function testAdjustScoreAndRatings(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test undoRatings method
	 */
	public function testUndoRatings(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test modifyTeamRatings method
	 */
	public function testModifyTeamRatings(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test getScoreEntry method
	 */
	public function testGetScoreEntry(): void {
		/** @var \App\Model\Entity\Game $matched_game */
		$matched_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'home_score' => 17,
			'away_score' => 12,
		]);

		$entry = $matched_game->getScoreEntry($matched_game->home_team_id);
		$this->assertFalse($entry->isNew());
		$this->assertEquals($matched_game->id, $entry->game_id);
		$this->assertEquals($matched_game->home_team_id, $entry->team_id);
		$this->assertEquals(17, $entry->score_for);
		$this->assertEquals(12, $entry->score_against);

		$entry = $matched_game->getScoreEntry($matched_game->away_team_id);
		$this->assertFalse($entry->isNew());
		$this->assertEquals($matched_game->id, $entry->game_id);
		$this->assertEquals($matched_game->away_team_id, $entry->team_id);
		$this->assertEquals(12, $entry->score_for);
		$this->assertEquals(17, $entry->score_against);

		/** @var \App\Model\Entity\Game $unscored_game */
		$unscored_game = $this->loadFixtureScenario(SingleGameScenario::class);

		$entry = $unscored_game->getScoreEntry($unscored_game->home_team_id);
		$this->assertTrue($entry->isNew());
		$this->assertEquals($unscored_game->id, $entry->game_id);
		$this->assertEquals($unscored_game->home_team_id, $entry->team_id);
		$this->assertNull($entry->person_id);
	}

	/**
	 * Test getSpiritEntry method
	 */
	public function testGetSpiritEntry(): void {
		/** @var \App\Model\Entity\Game $spirit_game */
		$spirit_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'spirit' => true,
		]);

		$spirit_obj = ModuleRegistry::getInstance()->load("Spirit:{$spirit_game->division->league->sotg_questions}");
		$this->assertNotNull($spirit_obj);

		$entry = $spirit_game->getSpiritEntry($spirit_game->home_team_id, $spirit_obj);
		$this->assertFalse($entry->isNew());
		$this->assertEquals($spirit_game->id, $entry->game_id);
		$this->assertEquals($spirit_game->home_team_id, $entry->created_team_id);
		$this->assertEquals($spirit_game->away_team_id, $entry->team_id);

		$entry = $spirit_game->getSpiritEntry($spirit_game->away_team_id, $spirit_obj);
		$this->assertFalse($entry->isNew());
		$this->assertEquals($spirit_game->id, $entry->game_id);
		$this->assertEquals($spirit_game->away_team_id, $entry->created_team_id);
		$this->assertEquals($spirit_game->home_team_id, $entry->team_id);

		/** @var \App\Model\Entity\Game $unscored_game */
		$unscored_game = $this->loadFixtureScenario(SingleGameScenario::class);

		$this->assertFalse($unscored_game->getSpiritEntry($unscored_game->home_team_id, $spirit_obj));

		$entry = $unscored_game->getSpiritEntry($unscored_game->home_team_id, $spirit_obj, true);
		$this->assertNotEmpty($entry);
		$this->assertTrue($entry->isNew());
		$this->assertEquals($unscored_game->home_team_id, $entry->created_team_id);
		$this->assertEquals($unscored_game->away_team_id, $entry->team_id);
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
	 * 		$contain = ['HomeTeam', 'AwayTeam', 'ScoreEntries', 'SpiritEntries', 'Divisions' => ['Leagues']];
	$this->Game1 = $games->get(GAME_ID_LADDER_MATCHED_SCORES, ['contain' => $contain]);
	$this->Game2 = $games->get(GAME_ID_LADDER_MISMATCHED_SCORES, ['contain' => $contain]);
	$this->Game3 = $games->get(GAME_ID_LADDER_HOME_SCORE_ONLY, ['contain' => $contain]);
	$this->Game4 = $games->get(GAME_ID_LADDER_NO_SCORES, ['contain' => $contain]);
	 */
	public function testGetBestScoreEntry(): void {
		/** @var \App\Model\Entity\Game $matched_game */
		$matched_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'home_score' => 17,
			'away_score' => 12,
		]);

		$entry = $matched_game->getBestScoreEntry();
		$this->assertNotEmpty($entry);
		$this->assertFalse($entry->isNew());
		$this->assertEquals($matched_game->id, $entry->game_id);
		$this->assertEquals($matched_game->home_team_id, $entry->team_id);
		$this->assertEquals(17, $entry->score_for);
		$this->assertEquals(12, $entry->score_against);

		/** @var \App\Model\Entity\Game $mismatched_game */
		$mismatched_game = $this->loadFixtureScenario(SingleGameScenario::class);
		$mismatched_game->score_entries = ScoreEntryFactory::make([
			[
				'game_id' => $mismatched_game->id,
				'team_id' => $mismatched_game->home_team_id,
				'score_for' => 17,
				'score_against' => 15,
			],
			[
				'game_id' => $mismatched_game->id,
				'team_id' => $mismatched_game->away_team_id,
				'score_for' => 16,
				'score_against' => 17,
			]
		])->persist();
		$this->assertNull($mismatched_game->getBestScoreEntry());

		/** @var \App\Model\Entity\Game $home_score_game */
		$home_score_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'home_score' => 17,
			'away_score' => 12,
			'home_score_only' => true,
		]);
		$entry = $home_score_game->getBestScoreEntry();
		$this->assertNotEmpty($entry);
		$this->assertEquals($home_score_game->home_team_id, $entry->team_id);

		/** @var \App\Model\Entity\Game $unscored_game */
		$unscored_game = $this->loadFixtureScenario(SingleGameScenario::class);
		$this->assertFalse($unscored_game->getBestScoreEntry());

		$this->markTestIncomplete('Test all the other various "in progress" possibilities.');
	}

	/**
	 * Test getScoreReminderEmail method
	 */
	public function testGetScoreReminderEmail(): void {
		/** @var \App\Model\Entity\Game $game */
		$game = $this->loadFixtureScenario(SingleGameScenario::class);
		$reminder = ActivityLogFactory::make(['type' => 'email_score_reminder', 'team_id' => $game->home_team_id, 'game_id' => $game->id])->persist();

		$entity = $game->getScoreReminderEmail($game->home_team_id);
		$this->assertNotEmpty($entity);
		$this->assertEquals($reminder->id, $entity->id);

		$this->assertFalse($game->getScoreReminderEmail($game->away_team_id));
	}

	/**
	 * Test isFinalized method
	 */
	public function testIsFinalized(): void {
		$this->setupFixtures();

		/** @var \App\Model\Entity\Game $finalized_game */
		$finalized_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'home_score' => 17,
			'away_score' => 12,
			'approved_by_id' => 1,
		]);
		$this->assertTrue($finalized_game->isFinalized());

		/** @var \App\Model\Entity\Game $matched_game */
		$matched_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'home_score' => 17,
			'away_score' => 12,
			'home_captain' => true,
			'away_captain' => true,
		]);

		$this->assertFalse($matched_game->isFinalized());
		$this->assertTrue($matched_game->finalize());
		$matched_game->adjustScoreAndRatings();
		$this->assertTrue($matched_game->isFinalized());
	}

	/**
	 * Test readDependencies method
	 */
	public function testReadDependencies(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
