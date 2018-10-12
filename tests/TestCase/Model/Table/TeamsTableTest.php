<?php
namespace App\Test\TestCase\Model\Table;

use App\Core\UserCache;
use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.skills',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_people',
					'app.divisions_days',
			'app.franchises',
				'app.franchises_teams',
			'app.settings',
	];

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
		$teams = $this->TeamsTable->readByPlayerId(PERSON_ID_CAPTAIN);
		$this->assertEquals(1, count($teams));
		$this->assertArrayHasKey(0, $teams);
		$this->assertEquals(TEAM_ID_RED, $teams[0]->id);

		$teams = $this->TeamsTable->readByPlayerId(PERSON_ID_CAPTAIN, false);
		$this->assertEquals(2, count($teams));
		$this->assertArrayHasKey(0, $teams);
		$this->assertEquals(TEAM_ID_RED_PAST, $teams[0]->id);
		$this->assertArrayHasKey(1, $teams);
		$this->assertEquals(TEAM_ID_RED, $teams[1]->id);
	}

	/**
	 * Test compareRoster method
	 *
	 * @return void
	 */
	public function testCompareRoster() {
		UserCache::getInstance()->initializeIdForTests(PERSON_ID_CAPTAIN);

		$event = new CakeEvent('Configuration.initialize', $this);
		EventManager::instance()->dispatch($event);

		$team = $this->TeamsTable->get(TEAM_ID_RED, ['contain' => ['People' => ['Skills']]]);
		\App\lib\context_usort($team->people, ['App\Model\Table\TeamsTable', 'compareRoster'], ['team' => $team]);

		// TODO: Add more people on the roster for more thorough testing
		$this->assertEquals(2, count($team->people));
		$this->assertArrayHasKey(0, $team->people);
		$this->assertEquals(PERSON_ID_CAPTAIN, $team->people[0]->id);
		$this->assertArrayHasKey(1, $team->people);
		$this->assertEquals(PERSON_ID_PLAYER, $team->people[1]->id);
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
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->TeamsTable->affiliate(TEAM_ID_RED));
	}

	/**
	 * Test sport method
	 *
	 * @return void
	 */
	public function testSport() {
		$this->assertEquals('ultimate', $this->TeamsTable->sport(TEAM_ID_RED));
	}

}
