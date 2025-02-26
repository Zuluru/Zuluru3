<?php
namespace App\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class FieldFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Fields';
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
				'num' => $faker->word(),
				'sport' => 'ultimate',
				'is_open' => true,
				'latitude' => $faker->randomFloat(6, -90, 90),
				'longitude' => $faker->randomFloat(6, -180, 180),
				'angle' => $faker->randomFloat(0, -180, 180),
				'length' => $faker->randomFloat(0, 50, 110),
				'width' => $faker->randomFloat(0, 20, 40),
				'zoom' => $faker->randomFloat(0, 15, 19),
				'rating' => 'B',
			];
		});
	}
}
