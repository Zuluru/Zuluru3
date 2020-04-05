<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\PoolsTeam;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\PoolsTeam Test Case
 */
class PoolsTeamTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.pools',
						'app.pools_teams',
		'app.i18n',
	];

	/**
	 * Test dependency method
	 *
	 * @return void
	 */
	public function testDependency() {
		$poolsTeams = TableRegistry::get('PoolsTeams');

		$poolsTeam2 = $poolsTeams->get(POOL_TEAM_ID_MONDAY_SEEDED_1, ['contain' => ['DependencyPool']]);
		$this->assertEquals('winner of B', $poolsTeam2->dependency());

		$poolsTeam3 = $poolsTeams->get(POOL_TEAM_ID_MONDAY_SEEDED_2, ['contain' => ['DependencyPool']]);
		$this->assertEquals('loser of B', $poolsTeam3->dependency());

		$poolsTeam1 = $poolsTeams->get(POOL_TEAM_ID_MONDAY_SEEDED_TODO, ['contain' => ['DependencyPool']]);
		$this->assertEquals('1st in pool A', $poolsTeam1->dependency());

		$poolsTeam4 = $poolsTeams->get(POOL_TEAM_ID_MONDAY_SEEDED_3, ['contain' => ['DependencyPool']]);
		$this->assertEquals('1st among 50th place teams', $poolsTeam4->dependency());

		$poolsTeam5 = $poolsTeams->get(POOL_TEAM_ID_MONDAY_SEEDED_4, ['contain' => ['DependencyPool']]);
		$this->assertEquals('1st seed', $poolsTeam5->dependency());
	}

}
