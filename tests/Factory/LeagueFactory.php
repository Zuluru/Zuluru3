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
        return "Leagues";
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
                'name' => $faker->word,
                'sport' => $faker->word,
                'season' => $faker->word,
                'open' => (new FrozenDate('first Monday of June'))->subWeeks(52),
                'close' => (new FrozenDate('first Monday of September'))->subWeeks(52),
                'expected_max_score' => 17,
                'tie_breaker' => 'win,hth,hthpm,pm,gf,loss',
            ];
        });
    }
}
