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
	 * Possible arguments are:
	 * - admin
	 * - manager
	 * - volunteer
	 * - player
	 *
	 * Each of these can be:
	 * - true to create a new person in that role
	 * - int to create that many new people in that role
	 * - array with specifics (e.g. roster designation) of a new person in that role
	 * - zero-indexed array of any number of the above
	 *
	 * @return Person[]
	 */
	public function load(...$args) {
		switch (count($args)) {
			case 0:
				break;

			case 1:
				$args = $args[0];
				break;

			default:
				throw new \BadMethodCallException('Scenario only accepts an array of named parameters.');
		}

		$affiliates = AffiliateFactory::make(2)->persist();
		$this->people = [];

		if (empty($args)) {
			$args = ['admin', 'manager', 'volunteer', 'player'];
		}

		// The different types of users, and how to create them
		$types = [
			'admin' => function (PersonFactory $factory, array $details = []) use ($affiliates) {
				return $factory->admin($details)
					->with('Affiliates', $affiliates);
			},
			'manager' => function (PersonFactory $factory, array $details = []) use ($affiliates) {
				return $factory->manager($details)
					->with('AffiliatesPeople', AffiliatesPersonFactory::make(['position' => 'manager', 'affiliate_id' => $affiliates[0]->id]));
				},
			'volunteer' => function (PersonFactory $factory, array $details = []) use ($affiliates) {
				return $factory->volunteer($details)
					->with('Affiliates', $affiliates[0]);
			},
			'player' => function (PersonFactory $factory, array $details = []) use ($affiliates) {
				return $factory->player($details)
					->with('Affiliates', $affiliates[0]);
			},
		];

		foreach ($args as $type => $details) {
			if (is_numeric($type)) {
				$type = $details;
				$details = true;
			}

			$this->addUser($types[$type], $details);
		}

		return $this->people;
	}

	private function addUser(callable $func, $details): void {
		if ($details === true) {
			$this->people[] = $func(PersonFactory::make())->persist();
		} else if (is_numeric($details)) {
			$this->people = array_merge($this->people, $func(PersonFactory::make([], $details))->persist());
		} else if (is_array($details)) {
			// Might be an array of things to add, or an array with details of a single thing to add. Check if there's a 0 key.
			if (array_key_exists(0, $details)) {
				foreach ($details as $detail) {
					$this->addUser($func, $detail);
				}
			} else {
				$this->people[] = $func(PersonFactory::make(), $details)->persist();
			}
		} else {
			throw new \BadMethodCallException('User detail must be true or number of users to create or array of entity details or array of those things.');
		}
	}

}
