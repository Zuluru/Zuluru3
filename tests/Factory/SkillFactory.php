<?php
namespace App\Test\Factory;

use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class SkillFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Skills';
	}

	/**
	 * Defines the default values of you factory. Useful for
	 * not nullable fields.
	 * Use the patchData method to set the field values.
	 * You may use methods of the factory here
	 * @return void
	 */
	protected function setDefaultTemplate() {
		$this->setDefaultData(function (Generator $faker) {
			return [
				'sport' => 'ultimate',
				'enabled' => true,
				'skill_level' => 5,
				'year_started' => FrozenDate::now()->year,
			];
		});
	}

}
