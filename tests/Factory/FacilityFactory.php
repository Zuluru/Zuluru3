<?php
namespace App\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class FacilityFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Facilities';
	}

	/**
	 * Defines the default values of you factory. Useful for
	 * not nullable fields.
	 * Use the patchData method to set the field values.
	 * You may use methods of the factory here
	 */
	protected function setDefaultTemplate(): void
	{
		$this->setDefaultData(function(Generator $faker) {
			return [
				'name' => $faker->lastName,
				'code' => strtoupper($faker->randomLetter() . $faker->randomLetter() . $faker->randomLetter()),
				'location_street' => $faker->streetAddress,
				'location_city' => $faker->city,
				'location_province' => 'Ontario',
				'location_postal_code' => $faker->postcode,
				'location_country' => 'Canada',
				'sport' => 'ultimate',
				'is_open' => true,
			];
		});
	}
}
