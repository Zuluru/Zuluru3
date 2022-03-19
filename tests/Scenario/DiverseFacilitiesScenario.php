<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\Region;
use App\Test\Factory\FacilityFactory;
use App\Test\Factory\RegionFactory;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;

class DiverseFacilitiesScenario implements FixtureScenarioInterface {

	public function load(...$args): Region {
		switch (count($args)) {
			case 0:
				break;

			case 1:
				$args = $args[0];
				break;

			default:
				throw new \BadMethodCallException('Scenario only accepts an array of named parameters.');
		}

		$open_fields = [
			['num' => 1],
			['num' => 2],
			['num' => 3, 'latitude' => null, 'longitude' => null],
			['num' => 4, 'is_open' => false],
		];
		$closed_fields = [
			['num' => 1, 'is_open' => false],
			['num' => 2, 'is_open' => false],
		];

		/** @var \App\Model\Entity\Region $region */
		$region = RegionFactory::make(['affiliate_id' => isset($args['affiliate']) ? $args['affiliate']->id : 1])
			->with('Facilities', FacilityFactory::make()->with('Fields', $open_fields))
			->with('Facilities', FacilityFactory::make(['is_open' => false])->with('Fields', $closed_fields))
			->persist();

		return $region;
	}
}
