<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class PriceFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Prices';
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
			$cost = $faker->randomFloat(2, 10, 100);

			return [
				'name' => $faker->word(),
				'open' => FrozenTime::now()->startOfYear(),
				'close' => FrozenTime::now()->endOfYear(),
				'online_payment_option' => ONLINE_MINIMUM_DEPOSIT,
				'cost' => $cost,
				'tax1' => round($cost * 0.08, 2),
				'tax2' => round($cost * 0.07, 2),
			];
		});
	}
}
