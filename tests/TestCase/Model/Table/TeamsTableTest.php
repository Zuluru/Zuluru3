<?php
namespace App\Test\TestCase\Model\Table;

use App\Core\UserCache;
use App\Middleware\ConfigurationLoader;
use App\Model\Entity\Person;
use App\Model\Entity\Team;
use App\Test\Factory\LeagueFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Factory\TeamsPersonFactory;
use App\Test\Scenario\TeamScenario;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use App\Model\Table\TeamsTable;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Model\Table\TeamsTable Test Case
 */
class TeamsTableTest extends TableTestCase {

	use ScenarioAwareTrait;

	/**
	 * Test subject
	 *
	 * @var TeamsTable
	 */
	public $TeamsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Groups',
		'app.RosterRoles',
	];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Teams') ? [] : ['className' => TeamsTable::class];
		$this->TeamsTable = TableRegistry::getTableLocator()->get('Teams', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->TeamsTable);

		parent::tearDown();
	}

	/**
	 * Test afterSave method
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterDelete method
	 */
	public function testAfterDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test readByPlayerId method
	 */
	public function testReadByPlayerId(): void {
		// We need this for sorting leagues by season
		Configure::load('options');

		$leagues = LeagueFactory::make([
			['name' => 'a'],
			['name' => 'b'],
			['name' => 'c'],
		])->with('Affiliates')->persist();

		/** @var Person $player */
		$player = PersonFactory::make()
			->with('TeamsPeople', TeamsPersonFactory::make()->with('Teams.Divisions', ['league_id' => $leagues[0]->id, 'is_open' => false, 'open' => FrozenDate::now()->subMonth()]))
			->with('TeamsPeople', TeamsPersonFactory::make()->with('Teams.Divisions', ['league_id' => $leagues[1]->id, 'is_open' => true]))
			->with('TeamsPeople', TeamsPersonFactory::make()->with('Teams.Divisions', ['league_id' => $leagues[2]->id, 'is_open' => true]))
			->persist();

		$teams = $this->TeamsTable->readByPlayerId($player->id);
		$this->assertCount(2, $teams);
		$this->assertArrayHasKey(0, $teams);
		$this->assertEquals($player->teams_people[1]->team_id, $teams[0]->id);
		$this->assertArrayHasKey(1, $teams);
		$this->assertEquals($player->teams_people[2]->team_id, $teams[1]->id);

		$teams = $this->TeamsTable->readByPlayerId($player->id, false);
		$this->assertCount(3, $teams);
		$this->assertArrayHasKey(0, $teams);
		$this->assertEquals($player->teams_people[0]->team_id, $teams[0]->id);
		$this->assertArrayHasKey(1, $teams);
		$this->assertEquals($player->teams_people[1]->team_id, $teams[1]->id);
		$this->assertArrayHasKey(2, $teams);
		$this->assertEquals($player->teams_people[2]->team_id, $teams[2]->id);
	}

	/**
	 * Test compareRoster method
	 */
	public function testCompareRoster(): void {
		/** @var Team $team */
		$team = $this->loadFixtureScenario(TeamScenario::class, [
			'roles' => [
				'captain' => ['first_name' => 'Darlene', 'last_name' => 'Allen', 'gender' => 'Woman'],
				'coach' => ['first_name' => 'Aaron', 'last_name' => 'Allen', 'gender' => 'Man'],
				'player' => ['first_name' => 'Carla', 'last_name' => 'Booth', 'gender' => 'Woman'],
				'substitute' => ['first_name' => 'Brenda', 'last_name' => 'Booth', 'gender' => 'Woman'],
			],
		]);
		[$captain, $coach, $player, $sub] = $team->people;

		UserCache::getInstance()->initializeIdForTests($captain->id);

		ConfigurationLoader::loadConfiguration();

		/** @var Team $team */
		$team = $this->TeamsTable->get($team->id, ['contain' => ['People' => ['Skills']]]);
		\App\lib\context_usort($team->people, [TeamsTable::class, 'compareRoster'], ['include_gender' => true]);

		// TODO: Add more people on the roster for more thorough testing
		$this->assertCount(4, $team->people);
		$this->assertArrayHasKey(0, $team->people);
		$this->assertEquals($captain->id, $team->people[0]->id);
		$this->assertArrayHasKey(1, $team->people);
		$this->assertEquals($coach->id, $team->people[1]->id);
		$this->assertArrayHasKey(2, $team->people);
		$this->assertEquals($player->id, $team->people[2]->id);
		$this->assertArrayHasKey(3, $team->people);
		$this->assertEquals($sub->id, $team->people[3]->id);
	}

	/**
	 * Test canEditRoster method
	 */
	public function testCanEditRoster(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $entity = TeamFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->TeamsTable->affiliate($entity->id));

        $entity = TeamFactory::make()->with('Divisions.Leagues', ['affiliate_id' => $affiliateId])->persist();
        $this->assertEquals($affiliateId, $this->TeamsTable->affiliate($entity->id));

    }

	/**
	 * Test sport method
	 */
	public function testSport(): void {
		/** @var Team $team */
		$team = TeamFactory::make()->with('Divisions.Leagues')->persist();
		$this->assertEquals($team->division->league->sport, $this->TeamsTable->sport($team->id));
	}

}
