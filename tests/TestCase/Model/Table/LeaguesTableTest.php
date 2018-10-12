<?php
namespace App\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use App\Model\Table\LeaguesTable;

/**
 * App\Model\Table\LeaguesTable Test Case
 */
class LeaguesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\LeaguesTable
	 */
	public $LeaguesTable;

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
			'app.leagues',
				'app.divisions',
					'app.divisions_people',
					'app.divisions_days',
			'app.settings',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Leagues') ? [] : ['className' => 'App\Model\Table\LeaguesTable'];
		$this->LeaguesTable = TableRegistry::get('Leagues', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LeaguesTable);

		parent::tearDown();
	}

	/**
	 * Test beforeMarshal method
	 *
	 * @return void
	 */
	public function testBeforeMarshal() {
		$data = new \ArrayObject([
			'tie_breakers' => ['a', 'b', 'c', 'd']
		]);
		$this->LeaguesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals('a,b,c,d', $data['tie_breaker']);
	}

	/**
	 * Test compareLeagueAndDivision method
	 *
	 * @return void
	 */
	public function testCompareLeagueAndDivision() {
		$event = new CakeEvent('Configuration.initialize', $this);
		EventManager::instance()->dispatch($event);

		// TODO: Add more league records, to more completely test the sort options
		$leagues = $this->LeaguesTable->find()
			->contain(['Affiliates', 'Divisions' => ['Days']])
			->toArray();
		$this->assertEquals(7, count($leagues));
		usort($leagues, ['App\Model\Table\LeaguesTable', 'compareLeagueAndDivision']);

		// Baseball comes before ultimate
		$this->assertArrayHasKey(0, $leagues);
		$this->assertEquals(LEAGUE_ID_TUESDAY, $leagues[0]->id);

		$this->assertArrayHasKey(1, $leagues);
		$this->assertEquals(LEAGUE_ID_WEDNESDAY, $leagues[1]->id);

		// The closed league is from last year
		$this->assertArrayHasKey(2, $leagues);
		$this->assertEquals(LEAGUE_ID_MONDAY_PAST, $leagues[2]->id);

		// Monday comes before Thursday
		$this->assertArrayHasKey(3, $leagues);
		$this->assertEquals(LEAGUE_ID_MONDAY, $leagues[3]->id);

		$this->assertArrayHasKey(4, $leagues);
		$this->assertEquals(LEAGUE_ID_THURSDAY, $leagues[4]->id);

		$this->assertArrayHasKey(5, $leagues);
		$this->assertEquals(LEAGUE_ID_FRIDAY, $leagues[5]->id);

		$this->assertArrayHasKey(6, $leagues);
		$this->assertEquals(LEAGUE_ID_SUNDAY_SUB, $leagues[6]->id);
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->LeaguesTable->affiliate(LEAGUE_ID_MONDAY));
	}

	/**
	 * Test divisions method
	 *
	 * @return void
	 */
	public function testDivisions() {
		$expected = [
			DIVISION_ID_MONDAY_LADDER => DIVISION_ID_MONDAY_LADDER,
			DIVISION_ID_MONDAY_LADDER2 => DIVISION_ID_MONDAY_LADDER2,
			DIVISION_ID_MONDAY_PLAYOFF => DIVISION_ID_MONDAY_PLAYOFF,
		];
		$this->assertEquals($expected, $this->LeaguesTable->divisions(LEAGUE_ID_MONDAY));
	}

	/**
	 * Test is_coordinator method
	 *
	 * @return void
	 */
	public function testIsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
