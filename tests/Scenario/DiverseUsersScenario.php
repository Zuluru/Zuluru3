<?php
declare(strict_types=1);

namespace App\Test\Scenario;

use App\Model\Entity\Person;
use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\AffiliatesPersonFactory;
use App\Test\Factory\PersonFactory;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;

class DiverseUsersScenario implements FixtureScenarioInterface {

	/**
	 * @return Person[]
	 */
	public function load(...$args) {
		$affiliates = AffiliateFactory::make(2)->persist();
		$admin = PersonFactory::make()->admin()->with('Affiliates', $affiliates)->persist();
		$manager = PersonFactory::make()->manager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$volunteer = PersonFactory::make()->volunteer()->with('Affiliates', $affiliates[0])->persist();
		$player = PersonFactory::make()->player()->with('Affiliates', $affiliates[0])->persist();

		return [$admin, $manager, $volunteer, $player];
	}
}
