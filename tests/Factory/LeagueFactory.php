<?php
namespace App\Test\Factory;

use Cake\I18n\FrozenDate;
use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class LeagueFactory extends BaseFactory
{
	/**
	 * Defines the Table Registry used to generate entities with
	 * @return string
	 */
	protected function getRootTableRegistryName(): string
	{
		return 'Leagues';
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
			$seasons = ['Spring', 'Summer', 'Fall', 'Winter'];
			return [
				'name' => $faker->word,
				'sport' => 'ultimate',
				'season' => $faker->randomElement($seasons),
				'open' => (new FrozenDate('first Monday of June')),
				'close' => (new FrozenDate('first Monday of September')),
				'expected_max_score' => 17,
				'tie_breaker' => 'win,hth,hthpm,pm,gf,loss',
				'sotg_questions' => 'wfdf2',
				'display_sotg' => 'symbols_only',
				'numeric_sotg' => false,
			];
		});
	}
}
