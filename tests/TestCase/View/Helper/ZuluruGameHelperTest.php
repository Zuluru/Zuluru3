<?php
namespace App\Test\TestCase\View\Helper;

use App\Test\Factory\TeamsPersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\LeagueWithMinimalScheduleScenario;
use App\TestSuite\AuthorizationHelperTrait;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use App\View\Helper\ZuluruGameHelper;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

/**
 * App\Model\Helper\ZuluruGameHelper Test Case
 */
class ZuluruGameHelperTest extends TestCase {

	use AuthorizationHelperTrait;
	use ScenarioAwareTrait;
	use TruncateDirtyTables;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.RosterRoles',
		'app.UserGroups',
	];

	public function setUp(): void {
		parent::setUp();

		Configure::load('options');
		$config = TableRegistry::getTableLocator()->exists('Configuration') ? [] : ['className' => 'App\Model\Table\ConfigurationTable'];
		$configurationTable = TableRegistry::getTableLocator()->get('Configuration', $config);
		$configurationTable->loadSystem();

		$this->loadRoutes();
	}

	public function tearDown(): void {
		// Clear any cached user details
		Cache::clear('long_term');

		parent::tearDown();
	}

	/**
	 * Test score method
	 */
	public function testScore(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test actionLinks method for games in the future
	 */
	public function testFutureActionLinks(): void {
		[$admin, $manager, $volunteer, $player] = $this->loadFixtureScenario(DiverseUsersScenario::class);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, [
			'affiliate' => $affiliate, 'coordinator' => $volunteer,
		]);
		$division = $league->divisions[0];
		$game = $division->games[0];
	}

	public function dataForPastActionLinks(): array
	{
		return [
			[SCORE_BY_CAPTAIN, true, false],
			[SCORE_BY_OFFICIAL, false, true],
			[SCORE_BY_BOTH, true, true],
		];
	}

	/**
	 * Test actionLinks method for games in the past
	 *
	 * @dataProvider dataForPastActionLinks
	 */
	public function testPastActionLinks($score_by, $score_by_captain, $score_by_official): void {
		Configure::write('scoring.score_entry_by', $score_by);

		[$admin, $volunteer, $captain1, $player, $captain2, $official] = $this->loadFixtureScenario(DiverseUsersScenario::class, [
			'admin',
			'volunteer',
			'player',
			'player',
			'player',
			'official',
		]);
		$affiliate = $admin->affiliates[0];
		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueWithMinimalScheduleScenario::class, [
			'affiliate' => $affiliate,
			'coordinator' => $volunteer,
			'league_details' => ['officials' => OFFICIALS_ADMIN],
		]);
		$division = $league->divisions[0];
		$game = $division->games[0];
		$game->officials = [$official];

		$game->home_team->teams_people = [
			TeamsPersonFactory::make(['role' => 'captain', 'team_id' => $game->home_team_id])->with('People', $captain1)->persist(),
			TeamsPersonFactory::make(['role' => 'player', 'team_id' => $game->home_team_id])->with('People', $player)->persist(),
		];
		$game->away_team->teams_people = [
			TeamsPersonFactory::make(['role' => 'captain', 'team_id' => $game->away_team_id])->with('People', $captain2)->persist(),
		];

		// Unauthorized user gets no links at all
		$helper = new ZuluruGameHelper(new View());
		$links = $helper->actionLinks($game, $division, $league);
		$this->assertEmpty($links);

		// Check links for admin
		$helper = $this->createHelper(ZuluruGameHelper::class, $admin->id);
		$links = $helper->actionLinks($game, $division, $league);
		$this->assertCount(1, $links);
		$this->assertArrayHasKey('', $links);
		$this->assertCount(1, $links['']);
		$this->assertStringContainsString('/games/edit', $links[''][0]);

		// Check links for coordinator
		$helper = $this->createHelper(ZuluruGameHelper::class, $volunteer->id);
		$links = $helper->actionLinks($game, $division, $league);
		$this->assertCount(1, $links);
		$this->assertArrayHasKey('', $links);
		$this->assertCount(1, $links['']);
		$this->assertStringContainsString('/games/edit', $links[''][0]);

		// Check links for captain
		$helper = $this->createHelper(ZuluruGameHelper::class, $captain1->id);
		$links = $helper->actionLinks($game, $division, $league);
		if ($score_by_captain) {
			$this->assertCount(1, $links);
			$this->assertArrayHasKey('', $links);
			$this->assertCount(1, $links['']);
			$this->assertStringContainsString('/games/submit', $links[''][0]);
			$this->assertStringContainsString("team={$game->home_team_id}", $links[''][0]);
		} else {
			$this->assertEmpty($links);
		}

		// Check links for other captain
		$helper = $this->createHelper(ZuluruGameHelper::class, $captain2->id);
		$links = $helper->actionLinks($game, $division, $league);
		if ($score_by_captain) {
			$this->assertCount(1, $links);
			$this->assertArrayHasKey('', $links);
			$this->assertCount(1, $links['']);
			$this->assertStringContainsString('/games/submit', $links[''][0]);
			$this->assertStringContainsString("team={$game->away_team_id}", $links[''][0]);
		} else {
			$this->assertEmpty($links);
		}

		// Check links for player
		$helper = $this->createHelper(ZuluruGameHelper::class, $player->id);
		$links = $helper->actionLinks($game, $division, $league);
		$this->assertEmpty($links);

		// Check links for official
		$helper = $this->createHelper(ZuluruGameHelper::class, $official->id);
		$links = $helper->actionLinks($game, $division, $league);
		if ($score_by_official) {
			$this->assertCount(1, $links);
			$this->assertArrayHasKey('', $links);
			$this->assertCount(1, $links['']);
			$this->assertStringContainsString('/games/submit', $links[''][0]);
			$this->assertStringNotContainsString('team=', $links[''][0]);
		} else {
			$this->assertEmpty($links);
		}
	}
}
