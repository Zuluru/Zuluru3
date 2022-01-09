<?php
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
		$admin = PersonFactory::makeAdmin()->with('Affiliates', $affiliates)->persist();
		$manager = PersonFactory::makeManager()
			->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]))
			->persist();
		$volunteer = PersonFactory::makeVolunteer()->with('Affiliates', $affiliates[0])->persist();
		$player = PersonFactory::makePlayer()->with('Affiliates', $affiliates[0])->persist();

		return [$admin, $manager, $volunteer, $player];
	}
}
