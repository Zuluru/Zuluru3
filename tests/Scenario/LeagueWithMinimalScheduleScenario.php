<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\League;
use App\Test\Factory\GameFactory;
use App\Test\Factory\GameSlotFactory;
use App\Test\Factory\RegionFactory;
use App\Test\Factory\TeamFactory;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

class LeagueWithMinimalScheduleScenario implements FixtureScenarioInterface {

	use ScenarioAwareTrait;

	/**
	 * Possible arguments are:
	 * - anything that LeagueScenario accepts
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

		// Where will the games be played?
		$fields = array_map(static function ($k) { return ['num' => $k]; }, range(1, count($league->divisions) * 2));
		/** @var \App\Model\Entity\Region $region */
		$region = RegionFactory::make(['affiliate_id' => isset($args['affiliate']) ? $args['affiliate']->id : 1])
			->with('Facilities.Fields', $fields)
			->persist();
		$facility = $region->facilities[0];
		$fields = $facility->fields;

		foreach ($league->divisions as $key => $division) {
			[$bears, $lions] = $division->teams = TeamFactory::make([
				['name' => 'Bears', 'shirt_colour' => 'Brown'],
				['name' => 'Lions', 'shirt_colour' => 'Yellow'],
			])->with('Divisions', $division)->persist();
			$open = $division->open;

			// Week 1
			$division->games[] = GameFactory::make([
				'division_id' => $division->id, 'home_team_id' => $bears->id, 'away_team_id' => $lions->id,
			])
				->with('GameSlots', GameSlotFactory::make(['game_date' => $open, 'assigned' => true])
					->with('Fields', $fields[$key * 2])
				)
				->persist();

			// Week 2
			$division->games[] = GameFactory::make([
				'division_id' => $division->id, 'home_team_id' => $bears->id, 'away_team_id' => $lions->id,
			])
				->with('GameSlots', GameSlotFactory::make(['game_date' => $open->addWeek(), 'assigned' => true])
					->with('Fields', $fields[$key * 2])
				)
				->persist();
		}

		return $league;
	}
}
