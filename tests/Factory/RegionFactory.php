<?php
namespace App\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class RegionFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Regions';
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
				'name' => $faker->word(),
			];
		});
	}
}
