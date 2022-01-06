<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class GameSlotFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return "GameSlots";
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
				'start_date' => new FrozenDate('last Monday of May'),
				'game_date' => $date,
				'game_start' => $date->hour(9),
				'game_end'=> $date->hour(18),
			];
		});
	}
}
