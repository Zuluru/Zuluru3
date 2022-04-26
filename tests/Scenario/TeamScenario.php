<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\Division;
use App\Model\Entity\League;
use App\Model\Entity\Person;
use App\Model\Entity\Team;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

class TeamScenario implements FixtureScenarioInterface {

	use ScenarioAwareTrait;

	/**
	 * Possible arguments are:
	 * - anything that LeagueScenario accepts
	 * - team_details: array
	 * - division: Division object or null to make an unassigned team
	 * - roles: array with keys as the roles to create and values one of:
	 *     true to create a new person in that role
	 *     int to create that many new people in that role
	 *     Person object to add that person in that role
	 *     array with specifics (e.g. roster status) of a new person in that role
	 *     zero-indexed array of any number of the above
	 */
	public function load(...$args): Team {
		switch (count($args)) {
			case 0:
				break;

			case 1:
				$args = $args[0];
				break;

			default:
				throw new \BadMethodCallException('Scenario only accepts an array of named parameters.');
		}

		$args += [
			'team_details' => [],
		];

		/** @var Team $team */
		if (array_key_exists('division', $args)) {
			if ($args['division'] === null) {
				// Make an unassigned team
				$team = TeamFactory::make($args['team_details'] + ['affiliate_id' => isset($args['affiliate']) ? $args['affiliate']->id : 1])
					->persist();
			} else if (is_a($args['division'], Division::class)) {
				$team = TeamFactory::make($args['team_details'])->with('Divisions', $args['division'])
					->persist();
			} else {
				throw new \BadMethodCallException('Division must be a Division entity or null.');
			}
		} else {
			/** @var League $league */
			$league = $this->loadFixtureScenario(LeagueScenario::class, $args);

			// Rearrange to fit this structure's expectations
			$division = $league->divisions[0];
			unset($league->divisions);
			$division->league = $league;

			$team = TeamFactory::make($args['team_details'])->with('Divisions', $division)
				->persist();
		}

		// Add captains, players, invitees, etc.
		$team->people = [];

		if (array_key_exists('roles', $args)) {
			foreach ($args['roles'] as $role => $details) {
				$this->addPlayer($team, $args['affiliate'] ?? [], $role, $details);
			}
		}

		return $team;
	}

	private function addPlayer(Team $team, $affiliate, string $role, $details): void {
		$roster_details = ['team_id' => $team->id, 'role' => $role];

		if ($details === true) {
			$team->people[] = PersonFactory::makePlayer()
				->with('TeamsPeople', TeamsPersonFactory::make($roster_details))
				->with('Affiliates', $affiliate)
				->persist();
		} else if (is_numeric($details)) {
			$team->people += PersonFactory::makePlayer($details)
				->with('TeamsPeople', TeamsPersonFactory::make($roster_details))
				->with('Affiliates', $affiliate)
				->persist();
		} else if (is_a($details, Person::class)) {
			$team->people[] = $details;
			TeamsPersonFactory::make(array_merge($roster_details, ['person_id' => $details->id]))->persist();
		} else if (is_array($details)) {
			// Might be an array of things to add, or an array with details of a single thing to add. Check if there's a 0 key.
			if (array_key_exists(0, $details)) {
				foreach ($details as $detail) {
					$this->addPlayer($team, $affiliate, $role, $detail);
				}
			} else {
				$team->people[] = PersonFactory::makePlayer()
					->with('TeamsPeople', TeamsPersonFactory::make(array_merge($roster_details, $details)))
					->with('Affiliates', $affiliate)
					->persist();
			}
		} else {
			throw new \BadMethodCallException('Role detail must be true or number of people to create or a Person entity or array of entities.');
		}
	}

}
