<?php
namespace App\Test\TestCase\Model\Entity;

use App\Service\Games\ScoreService;
use App\Test\Factory\ActivityLogFactory;
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
		$score_service = new ScoreService($matched_game->score_entries);
		$this->assertTrue($matched_game->finalize($score_service->getScoreEntryFrom($matched_game->home_team_id), $score_service->getScoreEntryFrom($matched_game->away_team_id)));
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
