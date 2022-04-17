<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\League;
use App\Test\Factory\DivisionsPersonFactory;
use App\Test\Factory\LeagueFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;

class LeagueWithRostersScenario implements FixtureScenarioInterface {

	// Constants to make finding the relevant players on rosters reliable
	public static $CAPTAIN = 0;
	public static $PLAYER1 = 1;
	public static $PLAYER2 = 2;
	public static $SUB = 3;
	public static $INVITED = 4;

	/**
	 * Possible arguments are:
	 * - affiliate Affiliate
	 * - coordinator Person
	 * - divisions int
	 * - teams int
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

		if (array_key_exists('divisions', $args)) {
			$divisions = "[{$args['divisions']}]";
		} else {
			$divisions = '';
		}

		if (array_key_exists('teams', $args)) {
			$teams = $args['teams'];
		} else {
			$teams = 4;
		}

		/** @var League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with("Divisions{$divisions}", ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates', $args['affiliate'] ?? [])
			->persist();

		foreach ($league->divisions as $division) {
			$division->teams = TeamFactory::make($teams)->with('Divisions', $division)->persist();
			if ($teams === 1) {
				// If there's only one team, it's just an entity right now, not an array.
				$division->teams = [$division->teams];
			}

			if (array_key_exists('coordinator', $args)) {
				DivisionsPersonFactory::make(['person_id' => $args['coordinator']->id, 'division_id' => $division->id])->persist();
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
