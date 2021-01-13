<?php
namespace App\Test\TestCase\Model\Table;

use App\Core\UserCache;
use App\Middleware\ConfigurationLoader;
use App\Test\Factory\GameFactory;
use App\Test\Factory\TeamFactory;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Model\Table\TeamsTable;

/**
 * App\Model\Table\TeamsTable Test Case
 */
class TeamsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\TeamsTable
	 */
	public $TeamsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Teams') ? [] : ['className' => 'App\Model\Table\TeamsTable'];
		$this->TeamsTable = TableRegistry::get('Teams', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TeamsTable);

		parent::tearDown();
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterDelete method
	 *
	 * @return void
	 */
	public function testAfterDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test readByPlayerId method
	 *
	 * @return void
	 */
	public function testReadByPlayerId() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		// We need this for sorting leagues by season
		Configure::load('options');

		$teams = $this->TeamsTable->readByPlayerId(PERSON_ID_CAPTAIN);
		$this->assertEquals(2, count($teams));
		$this->assertArrayHasKey(0, $teams);
		$this->assertEquals(TEAM_ID_RED, $teams[0]->id);
		$this->assertArrayHasKey(1, $teams);
		$this->assertEquals(TEAM_ID_CHICKADEES, $teams[1]->id);

		$teams = $this->TeamsTable->readByPlayerId(PERSON_ID_CAPTAIN, false);
		$this->assertEquals(3, count($teams));
		$this->assertArrayHasKey(0, $teams);
		$this->assertEquals(TEAM_ID_RED_PAST, $teams[0]->id);
		$this->assertArrayHasKey(1, $teams);
		$this->assertEquals(TEAM_ID_RED, $teams[1]->id);
		$this->assertArrayHasKey(2, $teams);
		$this->assertEquals(TEAM_ID_CHICKADEES, $teams[2]->id);
	}

	/**
	 * Test compareRoster method
	 *
	 * @return void
	 */
	public function testCompareRoster() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		UserCache::getInstance()->initializeIdForTests(PERSON_ID_CAPTAIN);

		ConfigurationLoader::loadConfiguration();

		$team = $this->TeamsTable->get(TEAM_ID_RED, ['contain' => ['People' => ['Skills']]]);
		\App\lib\context_usort($team->people, ['App\Model\Table\TeamsTable', 'compareRoster'], ['include_gender' => true]);

		// TODO: Add more people on the roster for more thorough testing
		$this->assertEquals(3, count($team->people));
		$this->assertArrayHasKey(0, $team->people);
		$this->assertEquals(PERSON_ID_CAPTAIN, $team->people[0]->id);
		$this->assertArrayHasKey(1, $team->people);
		$this->assertEquals(PERSON_ID_CAPTAIN3, $team->people[1]->id);
		$this->assertArrayHasKey(2, $team->people);
		$this->assertEquals(PERSON_ID_PLAYER, $team->people[2]->id);
	}

	/**
	 * Test canEditRoster method
	 *
	 * @return void
	 */
	public function testCanEditRoster() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $entity = TeamFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->TeamsTable->affiliate($entity->id));

        $entity = TeamFactory::make()->with('Divisions.Leagues', ['affiliate_id' => $affiliateId])->persist();
        $this->assertEquals($affiliateId, $this->TeamsTable->affiliate($entity->id));

    }

	/**
	 * Test sport method
	 *
	 * @return void
	 */
	public function testSport() {

	    $team = TeamFactory::make()->with('Divisions.Leagues')->persist();
		$this->assertEquals($team->division->league->sport, $this->TeamsTable->sport($team->id));
	}

}
