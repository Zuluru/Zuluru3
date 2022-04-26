<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\Game;
use App\Model\Entity\League;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\DivisionsDayFactory;
use App\Test\Factory\DivisionsPersonFactory;
use App\Test\Factory\GameFactory;
use App\Test\Factory\GameSlotFactory;
use App\Test\Factory\LeagueFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\PoolFactory;
use App\Test\Factory\ScoreEntryFactory;
use App\Test\Factory\TeamsPersonFactory;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

class SingleGameScenario implements FixtureScenarioInterface {

	use ScenarioAwareTrait;

	/**
	 * Possible arguments are:
	 * - anything that LeagueScenario accepts
	 * - game_date: FrozenDate
	 * - published: bool
	 * - status: string
	 * - approved_by_id: int
	 * The following arguments are currently not supported for tournament games:
	 * - home_score: int
	 * - away_score: int
	 * - home_captain: bool|Person
	 * - away_captain: bool|Person
	 * - home_player: bool|Person
	 * - away_player: bool|Person
	 * - home_sub: bool|Person
	 * - away_sub: bool|Person
	 */
	public function load(...$args): Game {
		switch (count($args)) {
			case 0:
				break;

			case 1:
				$args = $args[0];
				break;

			default:
				throw new \BadMethodCallException('Scenario only accepts an array of named parameters.');
		}

		$args += ['day_id' => FrozenDate::now()->dayOfWeek];

		/** @var League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, $args);
		$division = $league->divisions[0];
		$division->league = $league;

		/** @var Game $game */
		$gameFactory = GameFactory::make([
			'division_id' => $division->id,
			'published' => $args['published'] ?? true,
			'status' => $args['status'] ?? 'normal',
			'home_score' => !empty($args['approved_by_id']) ? ($args['home_score'] ?? null) : null,
			'away_score' => !empty($args['approved_by_id']) ? ($args['away_score'] ?? null) : null,
			'approved_by_id' => $args['approved_by_id'] ?? null,
		])
			->with('GameSlots',
				GameSlotFactory::make(['game_date' => $args['game_date'] ?? FrozenDate::now()])
					->with('Fields.Facilities.Regions', ['affiliate_id' => isset($args['affiliate']) ? $args['affiliate']->id : 1])
			);

		/** @var Game $game */
		if ($division->schedule_type === 'tournament') {
			$pool = PoolFactory::make(['division_id' => $division->id])->persist();

			$game = $gameFactory
				->patchData([
					'home_dependency_type' => 'pool',
					'away_dependency_type' => 'pool',
				])
				->with('HomePoolTeam', ['pool_id' => $pool->id, 'dependency_type' => 'seed', 'dependency_id' => 1, 'alias' => 'A1'])
				->with('AwayPoolTeam', ['pool_id' => $pool->id, 'dependency_type' => 'seed', 'dependency_id' => 4, 'alias' => 'A4'])
				->persist();
		} else {
			$game = $gameFactory
				->with('HomeTeam', ['division_id' => $division->id])
				->with('AwayTeam', ['division_id' => $division->id])
				->persist();

			$home = $game->home_team;
			$home->people = [];
			$away = $game->away_team;
			$away->people = [];
		}

		TableRegistry::getTableLocator()->get('Divisions')->loadInto($division, ['Days']);
		$game->division = $division;

		if (array_key_exists('home_score', $args)) {
			ScoreEntryFactory::make([
				[
					'game_id' => $game->id,
					'team_id' => $home->id,
					'score_for' => $args['home_score'],
					'score_against' => $args['away_score'],
				],
				[
					'game_id' => $game->id,
					'team_id' => $away->id,
					'score_for' => $args['away_score'],
					'score_against' => $args['home_score'],
				],
			])->persist();
		}

		if (array_key_exists('home_captain', $args)) {
			if ($args['home_captain'] === true) {
				$home->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $home->id, 'role' => 'captain']))
					->persist();
			} else {
				$home->people[] = $args['home_captain'];
				TeamsPersonFactory::make(['team_id' => $home->id, 'person_id' => $args['home_captain']->id, 'role' => 'captain'])->persist();
			}
		}

		if (array_key_exists('away_captain', $args)) {
			if ($args['away_captain'] === true) {
				$away->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $away->id, 'role' => 'captain']))
					->persist();
			} else {
				$away->people[] = $args['away_captain'];
				TeamsPersonFactory::make(['team_id' => $away->id, 'person_id' => $args['away_captain']->id, 'role' => 'captain'])->persist();
			}
		}

		if (array_key_exists('home_player', $args)) {
			if ($args['home_player'] === true) {
				$home->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $home->id, 'role' => 'player']))
					->persist();
			} else {
				$home->people[] = $args['home_player'];
				TeamsPersonFactory::make(['team_id' => $home->id, 'person_id' => $args['home_player']->id, 'role' => 'player'])->persist();
			}
		}

		if (array_key_exists('away_player', $args)) {
			if ($args['away_player'] === true) {
				$away->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $away->id, 'role' => 'player']))
					->persist();
			} else {
				$away->people[] = $args['away_player'];
				TeamsPersonFactory::make(['team_id' => $away->id, 'person_id' => $args['away_player']->id, 'role' => 'player'])->persist();
			}
		}

		if (array_key_exists('home_sub', $args)) {
			if ($args['home_sub'] === true) {
				$home->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $home->id, 'role' => 'substitute']))
					->persist();
			} else {
				$home->people[] = $args['home_sub'];
				TeamsPersonFactory::make(['team_id' => $home->id, 'person_id' => $args['home_sub']->id, 'role' => 'substitute'])->persist();
			}
		}

		if (array_key_exists('away_sub', $args)) {
			if ($args['away_sub'] === true) {
				$away->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $away->id, 'role' => 'substitute']))
					->persist();
			} else {
				$away->people[] = $args['away_sub'];
				TeamsPersonFactory::make(['team_id' => $away->id, 'person_id' => $args['away_sub']->id, 'role' => 'substitute'])->persist();
			}
		}

		return $game;
	}
}
