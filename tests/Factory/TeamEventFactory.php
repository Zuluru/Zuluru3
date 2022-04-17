<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class TeamEventFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'TeamEvents';
	}

	/**
	 * Defines the default values of you factory. Useful for
	 * not nullable fields.
	 * Use the patchData method to set the field values.
	 * You may use methods of the factory here
	 * @return void
	 */
	protected function setDefaultTemplate()
	{
		$this->setDefaultData(function(Generator $faker) {
			$date = new FrozenDate('tomorrow');
			return [
				'name' => $faker->words(3, true),
				'description' => $faker->sentence(),
				'date' => $date,
				'start' => FrozenTime::createFromTime(19),
				'end'=> FrozenTime::createFromTime(21),
				'location_name' => $faker->words(3, true),
				'location_street' => $faker->streetAddress,
				'location_city' => $faker->city,
				'location_province' => 'Ontario',
			];
		});
	}
}
