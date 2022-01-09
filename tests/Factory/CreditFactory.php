<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class CreditFactory  extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Credits';
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
			return [
				'amount' => $faker->randomFloat(2, 1, 100),
				'created' => FrozenTime::now(),
			];
		});
	}
}
