<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class WaiversPersonFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'WaiversPeople';
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
				'created' => FrozenTime::now(),
				'valid_from' => FrozenDate::now()->startOfYear(),
				'valid_until' => FrozenDate::now()->endOfYear(),
			];
		});
	}
}
