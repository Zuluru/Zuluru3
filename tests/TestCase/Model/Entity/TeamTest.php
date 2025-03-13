<?php
namespace App\Test\TestCase\Model\Entity;

use App\Middleware\ConfigurationLoader;
use App\Model\Entity\Team;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use App\Test\Scenario\TeamScenario;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Model\Entity\Team Test Case
 */
class TeamTest extends TestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.UserGroups',
		'app.RosterRoles',
	];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		EventManager::instance()->setEventList(new EventList());
		foreach (Configure::read('App.globalListeners') as $listener) {
			EventManager::instance()->on($listener);
		}
		ConfigurationLoader::loadConfiguration();
	}

	public function tearDown(): void {
		parent::tearDown();
		Cache::clear('long_term');
	}

	/**
	 * Test consolidateRoster method
	 */
	public function testConsolidateRoster(): void {
		/** @var Team $team */
		$team = TeamFactory::make()
			->with('TeamsPeople', TeamsPersonFactory::make(['role' => 'captain'])
				->with('People.Skills', ['skill_level' => 8])
			)
			->with('TeamsPeople', TeamsPersonFactory::make(['role' => 'player'])
				->with('People.Skills', ['skill_level' => 6])
			)
			->with('TeamsPeople', TeamsPersonFactory::make(['role' => 'substitute'])
				->with('People.Skills', ['skill_level' => 9])
			)
			->with('TeamsPeople', TeamsPersonFactory::make(['role' => 'player', 'status' => ROSTER_INVITED])
				->with('People.Skills', ['skill_level' => 3])
			)
			->getEntity();

		$this->assertEquals(0, $team->roster_count);
		$this->assertEquals(0, $team->skill_count);
		$this->assertEquals(0, $team->skill_total);
		$team->consolidateRoster('ultimate');
		$this->assertEquals(2, $team->roster_count);
		$this->assertEquals(2, $team->skill_count);
		$this->assertEquals(14, $team->skill_total);
	}

	/**
	 * Test addGameResult method
	 */
	public function testAddGameResult(): void {
		$this->markTestIncomplete('Not implemented yet. Pretty complex.');
	}

	/**
	 * Test _getRoster();
	 */
	public function testGetRoster(): void {
		$team = $this->loadFixtureScenario(TeamScenario::class, ['division' => null, 'roles' => [
			'captain' => true,
			'player' => [true, ['status' => ROSTER_INVITED]],
			'substitute' => true,
		]]);

		$people = $team->roster;
		$ids = [];
		foreach ($people as $person) {
			$ids[] = $person->id;
		}
		$this->assertNotFalse(array_search($team->people[0]->id, $ids, true), 'Missing captain on roster');
		$this->assertNotFalse(array_search($team->people[1]->id, $ids, true), 'Missing player on roster');
		$this->assertCount(2, $ids, 'Too many people on roster');
	}

	/**
	 * Test _getFullRoster();
	 */
	public function testGetFullRoster(): void {
		$team = $this->loadFixtureScenario(TeamScenario::class, ['division' => null, 'roles' => [
			'captain' => true,
			'player' => [true, ['status' => ROSTER_INVITED]],
			'substitute' => true,
		]]);

		$people = $team->full_roster;
		$ids = [];
		foreach ($people as $person) {
			$ids[] = $person->id;
		}

		$this->assertNotFalse(array_search($team->people[0]->id, $ids, true), 'Missing captain on roster');
		$this->assertNotFalse(array_search($team->people[1]->id, $ids, true), 'Missing player on roster');
		$this->assertNotFalse(array_search($team->people[2]->id, $ids, true), 'Missing invited player on roster');
		$this->assertNotFalse(array_search($team->people[3]->id, $ids, true), 'Missing sub on roster');
		$this->assertCount(4, $ids, 'Too many people on roster');
	}

	/**
	 * Test _getAffiliateTeam();
	 */
	public function testGetAffiliatedTeam(): void {
		/** @var Team $team */
		$team = TeamFactory::make()->with('Divisions.Leagues')->persist();
		$this->assertNull($team->affiliated_team);

		/** @var Team $playoff_team */
		$playoff_team = TeamFactory::make(['name' => $team->name])->with('Divisions', ['current_round' => 'playoff', 'league_id' => $team->division->league_id])->persist();
		$this->assertNotNull($playoff_team->affiliated_team);
		$this->assertEquals($team->id, $playoff_team->affiliated_team->id);

		// TODO: Test with franchises
		//Configure::write('feature.franchises', true);
		//$franchise = FranchiseFactory::...
	}

}
