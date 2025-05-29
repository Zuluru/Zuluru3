<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\League;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\DivisionsDayFactory;
use App\Test\Factory\DivisionsPersonFactory;
use App\Test\Factory\LeagueFactory;
use Cake\Chronos\ChronosInterface;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;

class LeagueScenario implements FixtureScenarioInterface {

	/**
	 * Possible arguments are:
	 * - affiliate: Affiliate
	 * - league_details: array
	 * - division_details: array
	 * - coordinator: Person|Person[]
	 * - divisions: int
	 * - day_id: ChronosInterface constant
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

		$args += [
			'league_details' => [],
			'division_details' => [],
			'day_id' => ChronosInterface::SUNDAY,
		];
		$open = FrozenDate::now()->next($args['day_id'])->subWeeks(3);

		$args['league_details'] += [
			'open' => $open, 'close' => $open->addWeeks(8), 'is_open' => true,
			'games_before_repeat' => 4, 'schedule_attempts' => 10,
			'officials' => OFFICIALS_NONE,
		];

		$args['division_details'] += [
			'open' => $open, 'close' => $open->addWeeks(8), 'is_open' => true,
			'schedule_type' => 'ratings_ladder', 'rating_calculator' => 'wager',
			'allstars' => 'optional', 'allstars_from' => 'opponent',
			'email_after' => 24, 'finalize_after' => 48,
		];

		if (array_key_exists('divisions', $args)) {
			$divisions = $args['divisions'];
		} else {
			$divisions = 1;
		}

		/** @var League $league */
		$league = LeagueFactory::make($args['league_details'])
			->with("Divisions", DivisionFactory::make($args['division_details'], $divisions)->with('Days', ['id' => $args['day_id'], 'name' => 'Testday']))
			->with('Affiliates', $args['affiliate'] ?? [])
			->persist();

		foreach ($league->divisions as $key => $division) {
			if (array_key_exists('coordinator', $args)) {
				$coordinator = null;

				if (!is_array($args['coordinator'])) {
					$coordinator = $args['coordinator'];
				} else if (!empty($args['coordinator'][$key])) {
					$coordinator = $args['coordinator'][$key];
				}

				if ($coordinator) {
					DivisionsPersonFactory::make(['person_id' => $coordinator->id, 'division_id' => $division->id])->persist();
				}
			}

			$division->games = $division->teams = [];
		}

		return $league;
	}
}
