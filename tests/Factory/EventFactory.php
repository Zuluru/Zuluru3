<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class EventFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Events';
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
				'name' => $faker->words(2, true),
				'open' => FrozenTime::now()->startOfYear(),
				'close' => FrozenTime::now()->endOfYear(),
				'open_cap' => -1,
				'women_cap' => -1,
			];
		});
	}

	public function setCustom(array $customFields)
	{
		return $this->patchData(['custom' => serialize($customFields)]);
	}
}
