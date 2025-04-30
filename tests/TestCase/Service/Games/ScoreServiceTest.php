<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Games;

use App\Service\Games\ScoreService;
use App\Test\Factory\ScoreEntryFactory;
use App\Test\Scenario\SingleGameScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use PHPUnit\Framework\TestCase;

class ScoreServiceTest extends TestCase
{
	use ScenarioAwareTrait;

	/**
	 * Test getScoreEntry method
	 */
	public function testGetScoreEntry(): void {
		/** @var \App\Model\Entity\Game $matched_game */
		$matched_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'home_score' => 17,
			'away_score' => 12,
		]);

		$service = new ScoreService($matched_game->score_entries ?? []);
		$entry = $service->getScoreEntryFrom($matched_game->home_team_id);
		$this->assertFalse($entry->isNew());
		$this->assertEquals($matched_game->id, $entry->game_id);
		$this->assertEquals($matched_game->home_team_id, $entry->team_id);
		$this->assertEquals(17, $entry->score_for);
		$this->assertEquals(12, $entry->score_against);

		$entry = $service->getScoreEntryFrom($matched_game->away_team_id);
		$this->assertFalse($entry->isNew());
		$this->assertEquals($matched_game->id, $entry->game_id);
		$this->assertEquals($matched_game->away_team_id, $entry->team_id);
		$this->assertEquals(12, $entry->score_for);
		$this->assertEquals(17, $entry->score_against);

		/** @var \App\Model\Entity\Game $unscored_game */
		$unscored_game = $this->loadFixtureScenario(SingleGameScenario::class);

		$service = new ScoreService($unscored_game->score_entries ?? []);
		$this->assertNull($service->getScoreEntryFrom($unscored_game->home_team_id));
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

		$service = new ScoreService($matched_game->score_entries ?? []);
		$entry = $service->getBestScoreEntry();
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
		])->getEntities();
		$service = new ScoreService($mismatched_game->score_entries ?? []);
		$this->assertNull($service->getBestScoreEntry());

		/** @var \App\Model\Entity\Game $home_score_game */
		$home_score_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'home_score' => 17,
			'away_score' => 12,
			'home_score_only' => true,
		]);
		$service = new ScoreService($home_score_game->score_entries ?? []);
		$entry = $service->getBestScoreEntry();
		$this->assertNotEmpty($entry);
		$this->assertEquals($home_score_game->home_team_id, $entry->team_id);

		/** @var \App\Model\Entity\Game $unscored_game */
		$unscored_game = $this->loadFixtureScenario(SingleGameScenario::class);
		$service = new ScoreService($unscored_game->score_entries ?? []);
		$this->assertFalse($service->getBestScoreEntry());

		$this->markTestIncomplete('Test all the other various "in progress" possibilities.');
	}
}
