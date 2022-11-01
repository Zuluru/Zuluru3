<?php
namespace App\Test\TestCase\Model\Table;

use App\Middleware\ConfigurationLoader;
use App\Model\Entity\League;
use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\LeagueFactory;
use Cake\Chronos\ChronosInterface;
use Cake\Event\Event as CakeEvent;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use App\Model\Table\LeaguesTable;

/**
 * App\Model\Table\LeaguesTable Test Case
 */
class LeaguesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var LeaguesTable
	 */
	public $LeaguesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Leagues') ? [] : ['className' => LeaguesTable::class];
		$this->LeaguesTable = TableRegistry::getTableLocator()->get('Leagues', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->LeaguesTable);

		parent::tearDown();
	}

	/**
	 * Test beforeMarshal method
	 */
	public function testBeforeMarshal(): void {
		$data = new \ArrayObject([
			'tie_breakers' => ['a', 'b', 'c', 'd']
		]);
		$this->LeaguesTable->beforeMarshal(new CakeEvent('testing'), $data, new \ArrayObject());
		$this->assertEquals('a,b,c,d', $data['tie_breaker']);
	}

	/**
	 * Test compareLeagueAndDivision method
	 */
	public function testCompareLeagueAndDivision(): void {
		ConfigurationLoader::loadConfiguration();

		$affiliates = AffiliateFactory::make([
			['name' => 'A'],
			['name' => 'B'],
		])->persist();

		// Make a variety of leagues
		$ultimate_closed = LeagueFactory::make(['season' => 'Summer', 'open' => FrozenDate::now()->subYear()])
			->with('Divisions.Days', ['id' => ChronosInterface::FRIDAY, 'name' => 'Friday'])
			->with('Affiliates', $affiliates[0])
			->persist();
		$ultimate_monday = LeagueFactory::make(['season' => 'Summer', 'sport' => 'ultimate'])
			->with('Divisions.Days', ['id' => ChronosInterface::MONDAY, 'name' => 'Monday'])
			->with('Affiliates', $affiliates[0])
			->persist();
		$ultimate_saturday = LeagueFactory::make(['season' => 'Summer', 'sport' => 'ultimate'])
			->with('Divisions.Days', ['id' => ChronosInterface::SATURDAY, 'name' => 'Saturday'])
			->with('Affiliates', $affiliates[0])
			->persist();
		$ultimate_affiliate = LeagueFactory::make(['season' => 'Summer', 'sport' => 'ultimate'])
			->with('Divisions.Days', ['id' => ChronosInterface::THURSDAY, 'name' => 'Thursday'])
			->with('Affiliates', $affiliates[1])
			->persist();
		$baseball_tuesday = LeagueFactory::make(['season' => 'Summer', 'sport' => 'baseball'])
			->with('Divisions.Days', ['id' => ChronosInterface::TUESDAY, 'name' => 'Tuesday'])
			->with('Affiliates', $affiliates[0])
			->persist();
		$baseball_wednesday = LeagueFactory::make(['season' => 'Summer', 'sport' => 'baseball'])
			->with('Divisions.Days', ['id' => ChronosInterface::WEDNESDAY, 'name' => 'Wednesday'])
			->with('Affiliates', $affiliates[0])
			->persist();
		$baseball_spring = LeagueFactory::make(['season' => 'Spring', 'sport' => 'baseball'])
			->with('Divisions.Days', ['id' => ChronosInterface::WEDNESDAY, 'name' => 'Wednesday'])
			->with('Affiliates', $affiliates[0])
			->persist();

		$leagues = $this->LeaguesTable->find()
			->contain(['Affiliates', 'Divisions' => ['Days']])
			->toArray();
		$this->assertCount(7, $leagues);
		usort($leagues, [LeaguesTable::class, 'compareLeagueAndDivision']);

		$expected = [
			// Baseball comes before ultimate, with Spring before Summer and Tuesday before Wednesday
			$baseball_spring->id, $baseball_tuesday->id, $baseball_wednesday->id,

			// The closed league is from last year
			$ultimate_closed->id,

			// Monday comes before Saturday
			$ultimate_monday->id, $ultimate_saturday->id,

			// Other affiliate comes last
			$ultimate_affiliate->id,
		];

		$this->assertEquals($expected, collection($leagues)->extract('id')->toArray());

		$this->markTestIncomplete('Add more league records, to more completely test the sort options: regular leagues before tournaments, tournaments by date. Leagues fall back to names, divisions to ID.');
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
	    $affiliateId = mt_rand();
	    $league = LeagueFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->LeaguesTable->affiliate($league->id));
	}

	/**
	 * Test divisions method
	 */
	public function testDivisions(): void {
		DivisionFactory::make(3)->persist();
		/** @var League $league */
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
