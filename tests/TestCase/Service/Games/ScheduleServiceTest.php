<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Games;

use App\Controller\Component\LockComponent;
use App\Middleware\ConfigurationLoader;
use App\Service\Games\ScheduleService;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\LeagueWithFullScheduleScenario;
use Bootstrap\Controller\Component\FlashComponent;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

class ScheduleServiceTest extends TestCase
{
	use ScenarioAwareTrait;

	private ScheduleService $Service;
	private FlashComponent $Flash;
	private LockComponent $Lock;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.UserGroups',
		'app.Settings',
	];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		ConfigurationLoader::loadConfiguration();
		$this->Flash = $this->createMock(FlashComponent::class);
		$this->Lock = $this->createMock(LockComponent::class);
		$this->Lock->method('lock')->willReturn(true);
		$this->Service = new ScheduleService(TableRegistry::getTableLocator()->get('Games'), $this->Flash, $this->Lock);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->Service);
		unset($this->Flash);
		Cache::clear('long_term');

		parent::tearDown();
	}

	/**
	 * Test a good edit
	 */
	public function testGoodEdit(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);

		// We're only interested in the "Week 4" games from this schedule.
		$games = array_slice($league->divisions[0]->games, 12);
		$game_slots = collection($games)->extract('game_slot')->toArray();

		$data = [
			'games' => [
				// Swap the game slots of the two games
				$games[0]->id => [
					'id' => $games[0]->id,
					'type' => $games[0]->type,
					'game_slot_id' => $games[1]->game_slot_id,
					'home_team_id' => $games[0]->home_team_id,
					'away_team_id' => $games[0]->away_team_id,
				],
				$games[1]->id => [
					'id' => $games[1]->id,
					'type' => $games[1]->type,
					'game_slot_id' => $games[0]->game_slot_id,
					'home_team_id' => $games[1]->home_team_id,
					'away_team_id' => $games[1]->away_team_id,
				],
			],
			'options' => [
				'publish' => '0',
				'double_header' => '0',
				'multiple_days' => '0',
			],
		];
		$this->assertTrue($this->Service->update($league, $games, $game_slots, $data));
	}

	/**
	 * Test a duplicated game slot
	 */
	public function testDuplicateGameSlot(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithFullScheduleScenario::class, ['affiliate' => $admin->affiliates[0]]);

		// We're only interested in the "Week 4" games from this schedule.
		$games = array_slice($league->divisions[0]->games, 12);
		$game_slots = collection($games)->extract('game_slot')->toArray();

		$data = [
			'games' => [
				// Use the same game slot for both
				$games[0]->id => [
					'id' => $games[0]->id,
					'type' => $games[0]->type,
					'game_slot_id' => $games[0]->game_slot_id,
					'home_team_id' => $games[0]->home_team_id,
					'away_team_id' => $games[0]->away_team_id,
				],
				$games[1]->id => [
					'id' => $games[1]->id,
					'type' => $games[1]->type,
					'game_slot_id' => $games[0]->game_slot_id,
					'home_team_id' => $games[1]->home_team_id,
					'away_team_id' => $games[1]->away_team_id,
				],
			],
			'options' => [
				'publish' => '0',
				'double_header' => '0',
				'multiple_days' => '0',
			],
		];
		$this->assertFalse($this->Service->update($league, $games, $game_slots, $data));
	}
}
