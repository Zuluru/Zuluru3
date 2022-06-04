<?php
namespace App\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class SpiritEntryFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'SpiritEntries';
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
				'score_entry_penalty' => 0,
				'q1' => 2,
				'q2' => 2,
				'q3' => 2,
				'q4' => 2,
				'q5' => 2,
				'q6' => 0,
				'q7' => 0,
				'q8' => 0,
				'q9' => 0,
				'q10' => 0,
				'comments' => '',
				'highlights' => '',
			];
		});
	}
}
