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

		/** @var League $league */
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with("Divisions{$divisions}", ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates')
			->persist();

		foreach ($league->divisions as $division) {
			$division->teams = TeamFactory::make(4)->with('Divisions', $division)->persist();

			if (array_key_exists('coordinator', $args)) {
				DivisionsPersonFactory::make(['person_id' => $args['coordinator']->id, 'division_id' => $division->id])->persist();
			}

			foreach ($division->teams as $team) {
				// Add a captain
				PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'role' => 'captain']))
					->persist();

				// Add two players
				PersonFactory::makePlayer(2)
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'role' => 'player']))
					->persist();

				// Add a sub
				PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'role' => 'substitute']))
					->persist();

				// Add someone that's invited
				PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(['team_id' => $team->id, 'role' => 'player', 'status' => ROSTER_INVITED]))
					->persist();
			}
		}

		return $league;
	}
}
