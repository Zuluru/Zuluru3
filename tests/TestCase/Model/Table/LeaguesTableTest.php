<?php
namespace App\Test\TestCase\Model\Table;

use App\Middleware\ConfigurationLoader;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\GameFactory;
use App\Test\Factory\LeagueFactory;
use Cake\Event\Event as CakeEvent;
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
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		ConfigurationLoader::loadConfiguration();

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
	    $affiliateId = rand();
	    $league = LeagueFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->LeaguesTable->affiliate($league->id));
	}

	/**
	 * Test divisions method
	 *
	 * @return void
	 */
	public function testDivisions() {
	    DivisionFactory::make(3)->persist();
	    $league = LeagueFactory::make()
            ->with('Divisions', 2)
            ->persist();

	    $expected = [
			$league->divisions[0]->id => $league->divisions[0]->id,
			$league->divisions[1]->id => $league->divisions[1]->id,
		];
		$this->assertEquals($expected, $this->LeaguesTable->divisions($league->id));
	}

}
