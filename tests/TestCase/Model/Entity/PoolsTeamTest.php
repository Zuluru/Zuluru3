<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\PoolsTeam;
use App\Test\Factory\PoolsTeamFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\PoolsTeam Test Case
 */
class PoolsTeamTest extends TestCase {

    public function dataForDependencyTest()
    {
        return [ [
                PoolsTeamFactory::make()
                    ->with('DependencyPool', [
                        'name' => 'B',
                        'type' => 'crossover',
                    ]),
                'winner of B',
            ], [
                PoolsTeamFactory::make(['dependency_id' => 2,])
                    ->with('DependencyPool', [
                        'name' => 'B',
                        'type' => 'crossover',
                    ]),
                'loser of B',
            ], [
                PoolsTeamFactory::make(['dependency_id' => 1,])
                    ->with('DependencyPool', [
                        'name' => 'A',
                    ]),
                '1st in pool A',
            ], [
                PoolsTeamFactory::make([ 'dependency_id' => 1, 'dependency_ordinal' => 50]),
                '1st among 50th place teams',
            ], [
                PoolsTeamFactory::make(['dependency_id' => 1,]),
                '1st seed',
            ],
        ];
    }

	/**
	 * Test dependency method
	 *
     * @dataProvider dataForDependencyTest
	 * @return void
	 */
	public function testDependency(PoolsTeamFactory $factory, $expectedDependency) {
		$this->assertEquals($expectedDependency, $factory->persist()->dependency());
	}

}
