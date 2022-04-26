<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\League;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

class LeagueWithRostersScenario implements FixtureScenarioInterface {

	use ScenarioAwareTrait;

	// Constants to make finding the relevant players on rosters reliable
	public static $CAPTAIN = 0;
	public static $PLAYER1 = 1;
	public static $PLAYER2 = 2;
	public static $SUB = 3;
	public static $INVITED = 4;

	/**
	 * Possible arguments are:
	 * - anything that LeagueScenario accepts
	 * - teams: int
	 */
	public function load(...$args): League {
		switch (count($args)) {
			case 0:
				break;

			case 1:
				$args = $args[0];
				break;

			default:
				throw new \BadMethodCallException('Scenario only accepts an array of named parameters.');
		}

		/** @var League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, $args);

		if (array_key_exists('teams', $args)) {
			$teams = $args['teams'];
		} else {
			$teams = 4;
		}

		foreach ($league->divisions as $division) {
			$division->teams = TeamFactory::make($teams)->with('Divisions', $division)->persist();
			if ($teams === 1) {
				// If there's only one team, it's just an entity right now, not an array.
				$division->teams = [$division->teams];
			}

			foreach ($division->teams as $team) {
				$team->people = [];

				// Add a captain
				$team->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'role' => 'captain']))
					->with('Affiliates', $league->affiliate)
					->persist();

				// Add two players
				$players = PersonFactory::makePlayer(2)
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'role' => 'player']))
					->with('Affiliates', $league->affiliate)
					->persist();
				$team->people[] = $players[0];
				$team->people[] = $players[1];

				// Add a sub
				$team->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'role' => 'substitute']))
					->with('Affiliates', $league->affiliate)
					->persist();

				// Add someone that's invited
				$team->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'role' => 'player', 'status' => ROSTER_INVITED]))
					->with('Affiliates', $league->affiliate)
					->persist();
			}
		}

		return $league;
	}
}
