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

	public function dataForTestDependency()
	{
		return [
			[
				[],
				['name' => 'B', 'type' => 'crossover'],
				'winner of B',
			],
			[
				['dependency_id' => 2],
				['name' => 'B', 'type' => 'crossover'],
				'loser of B',
			],
			[
				['dependency_id' => 1],
				['name' => 'A'],
				'1st in pool A',
			],
			[
				['dependency_id' => 1, 'dependency_ordinal' => 50],
				[],
				'1st among 50th place teams',
			],
			[
				['dependency_id' => 1],
				[],
				'1st seed',
			],
		];
	}

	/**
	 * Test dependency method
	 *
	 * @dataProvider dataForTestDependency
	 * @return void
	 */
	public function testDependency(array $poolsTeamArgs, array $dependencyPoolArgs, string $expectedDependency) {
		$factory = PoolsTeamFactory::make($poolsTeamArgs);
		if (!empty($dependencyPoolArgs)) {
			$factory = $factory->with('DependencyPool', $dependencyPoolArgs);
		}

		$this->assertEquals($expectedDependency, $factory->persist()->dependency());
	}

}
