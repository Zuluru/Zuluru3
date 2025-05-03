<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Games;

use App\Core\ModuleRegistry;
use App\Service\Games\SpiritService;
use App\Test\Scenario\SingleGameScenario;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use PHPUnit\Framework\TestCase;

class SpiritServiceTest extends TestCase
{
	use ScenarioAwareTrait;

	/**
	 * Test getEntry method
	 */
	public function testgetAverageEntryFor(): void {
		/** @var \App\Model\Entity\Game $spirit_game */
		$spirit_game = $this->loadFixtureScenario(SingleGameScenario::class, [
			'spirit' => true,
		]);

		$spirit_obj = ModuleRegistry::getInstance()->load("Spirit:{$spirit_game->division->league->sotg_questions}");
		$this->assertNotNull($spirit_obj);
		$spirit_service = new SpiritService($spirit_game->spirit_entries ?? [], $spirit_obj);

		// @todo: Handle the questions parameter here, and more complex scenarios
		$entry = $spirit_service->getAverageEntryFor($spirit_game->away_team_id, []);
		$this->assertFalse($entry->isNew());
		$this->assertEquals($spirit_game->id, $entry->game_id);
		$this->assertEquals($spirit_game->home_team_id, $entry->created_team_id);
		$this->assertEquals($spirit_game->away_team_id, $entry->team_id);

		$entry = $spirit_service->getAverageEntryFor($spirit_game->home_team_id, []);
		$this->assertFalse($entry->isNew());
		$this->assertEquals($spirit_game->id, $entry->game_id);
		$this->assertEquals($spirit_game->away_team_id, $entry->created_team_id);
		$this->assertEquals($spirit_game->home_team_id, $entry->team_id);

		/** @var \App\Model\Entity\Game $unscored_game */
		$unscored_game = $this->loadFixtureScenario(SingleGameScenario::class);
		$spirit_service = new SpiritService($unscored_game->spirit_entries ?? [], $spirit_obj);

		$this->assertNull($spirit_service->getAverageEntryFor($unscored_game->home_team_id, []));
		$this->assertNull($spirit_service->getAverageEntryFor($unscored_game->away_team_id, []));
	}

	/**
	 * Test getEntry method
	 */
	public function testBla(): void {
		/** @var \App\Model\Entity\Game $unscored_game */
		$unscored_game = $this->loadFixtureScenario(SingleGameScenario::class);
		$spirit_obj = ModuleRegistry::getInstance()->load("Spirit:{$unscored_game->division->league->sotg_questions}");
		$this->assertNotNull($spirit_obj);
		$spirit_service = new SpiritService($unscored_game->spirit_entries ?? [], $spirit_obj);

		$entry = $spirit_service->getDefaultEntryFor($unscored_game->away_team_id, $unscored_game->home_team_id);
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
}
