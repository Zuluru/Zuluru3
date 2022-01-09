<?php
namespace App\Test\Scenario;

use App\Model\Entity\League;
use App\Test\Factory\LeagueFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;

class LeagueScenario implements FixtureScenarioInterface {

	/**
	 * @return League
	 */
	public function load(...$args) {
		$league = LeagueFactory::make(['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Divisions', ['open' => FrozenDate::now()->subMonth(), 'close' => FrozenDate::now()->addMonth(), 'is_open' => true])
			->with('Affiliates')
			->persist();
		$league->divisions[0]->teams = TeamFactory::make(4)->with('Divisions', $league->divisions[0])->persist();

		foreach ($league->divisions[0]->teams as $team) {
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

		return $league;
	}
}
