<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\League;
use App\Test\Factory\DivisionsDayFactory;
use App\Test\Factory\DivisionsPersonFactory;
use App\Test\Factory\GameSlotFactory;
use App\Test\Factory\LeagueFactory;
use App\Test\Factory\RegionFactory;
use App\Test\Factory\TeamFactory;
use Cake\Chronos\ChronosInterface;
use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;

class LeagueWithMinimalScheduleScenario implements FixtureScenarioInterface {

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

		$open = FrozenDate::now()->next(ChronosInterface::SUNDAY)->subWeeks(3);
		/** @var League $league */
		$league = LeagueFactory::make(['open' => $open, 'close' => $open->addWeeks(8), 'is_open' => true])
			->with("Divisions{$divisions}", ['open' => $open, 'close' => $open->addWeeks(8), 'is_open' => true, 'schedule_type' => 'ratings_ladder'])
			->with('Affiliates', $args['affiliate'] ?? [])
			->persist();

		// Where will the games be played?
		$fields = array_map(static function ($k) { return ['num' => $k]; }, range(1, count($league->divisions) * 2));
		/** @var \App\Model\Entity\Region $region */
		$region = RegionFactory::make()->with('Facilities.Fields', $fields)->persist();
		$fields = $region->facilities[0]->fields;

		foreach ($league->divisions as $key => $division) {
			[$bears, $lions] = $division->teams = TeamFactory::make([
				['name' => 'Bears', 'shirt_colour' => 'Brown'],
				['name' => 'Lions', 'shirt_colour' => 'Yellow'],
			])->with('Divisions', $division)->persist();
			DivisionsDayFactory::make(['day_id' => DAY_ID_SUNDAY, 'division_id' => $division->id])->persist();

			if (array_key_exists('coordinator', $args)) {
				DivisionsPersonFactory::make(['person_id' => $args['coordinator']->id, 'division_id' => $division->id])->persist();
			}

			// Week 1
			GameSlotFactory::make(['game_date' => $open, 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $bears->id, 'away_team_id' => $lions->id,
				])
				->persist();

			// Week 2
			GameSlotFactory::make(['game_date' => $open->addWeek(), 'assigned' => true])
				->with('Fields', $fields[$key * 2])
				->with('Games', [
					'division_id' => $division->id, 'home_team_id' => $bears->id, 'away_team_id' => $lions->id,
				])
				->persist();
		}

		return $league;
	}
}
