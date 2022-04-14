<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class TaskSlotFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'TaskSlots';
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
				'task_date' => FrozenDate::now()->addDay(),
				'task_start' => '17:00:00',
				'task_end' => '19:00:00',
				'modified' => FrozenTime::now(),
			];
		});
	}
}
