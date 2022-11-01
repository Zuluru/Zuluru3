<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Division;
use App\Test\Factory\DivisionFactory;
use App\Test\Factory\TeamFactory;
use App\Test\Scenario\DiverseUsersScenario;
use App\Test\Scenario\LeagueScenario;
use Cake\Chronos\ChronosInterface;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use App\Model\Table\DivisionsTable;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;

/**
 * App\Model\Table\DivisionsTable Test Case
 */
class DivisionsTableTest extends TableTestCase {

	use ScenarioAwareTrait;

	/**
	 * Test subject
	 *
	 * @var DivisionsTable
	 */
	public $DivisionsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Groups',
	];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Divisions') ? [] : ['className' => DivisionsTable::class];
		$this->DivisionsTable = TableRegistry::getTableLocator()->get('Divisions', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->DivisionsTable);

		parent::tearDown();
	}

	/**
	 * Test beforeSave method
	 */
	public function testBeforeSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterSave method
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test findOpen method
	 */
	public function testFindOpen(): void {
		DivisionFactory::make([
			['is_open' => true, 'open' => FrozenDate::yesterday(),], // Is open
			['is_open' => false, 'open' => FrozenDate::tomorrow(),], // Is open
			['is_open' => false, 'open' => FrozenDate::today(),], // Is not open
			['is_open' => false, 'open' => FrozenDate::today(),], // Is not open
		])->persist();

		$divisions = $this->DivisionsTable->find('open');
		$this->assertEquals(2, $divisions->count());
	}

	/**
	 * Test findDay method
	 */
	public function testFindDay(): void {
		DivisionFactory::make(3)->with('Days', ['id' => ChronosInterface::MONDAY, 'name' => 'Monday'])->persist();
		DivisionFactory::make()->with('Days', ['id' => ChronosInterface::TUESDAY, 'name' => 'Tuesday'])->persist();

		$divisions = $this->DivisionsTable->find('day', ['date' => new FrozenDate('Monday')]);
		$this->assertEquals(3, $divisions->count());
		$divisions = $this->DivisionsTable->find('day', ['date' => new FrozenDate('Tuesday')]);
		$this->assertEquals(1, $divisions->count());
	}

	/**
	 * Test readByPlayerId method
	 */
	public function testReadByPlayerId(): void {
		[$admin, $volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin', 'volunteer']);

		/** @var \App\Model\Entity\League $league */
		$league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer, 'divisions' => 2,
		]);

		/** @var \App\Model\Entity\League $old_league */
		$old_league = $this->loadFixtureScenario(LeagueScenario::class, [
			'affiliate' => $admin->affiliates[0], 'coordinator' => $volunteer, 'division_details' => ['is_open' => false, 'open' => FrozenDate::now()->subMonth()]
		]);

		$divisions = $this->DivisionsTable->readByPlayerId($volunteer->id);
		$this->assertCount(2, $divisions);
		$this->assertArrayHasKey(0, $divisions);
		$this->assertEquals($league->divisions[0]->id, $divisions[0]->id);
		$this->assertEmpty($divisions[0]->teams);
		$this->assertArrayHasKey(1, $divisions);
		$this->assertEquals($league->divisions[1]->id, $divisions[1]->id);
		$this->assertEmpty($divisions[1]->teams);

		$divisions = $this->DivisionsTable->readByPlayerId($volunteer->id, false);
		$this->assertCount(3, $divisions);
		$this->assertArrayHasKey(0, $divisions);
		$this->assertEquals($old_league->divisions[0]->id, $divisions[0]->id);
		$this->assertEmpty($divisions[0]->teams);
		$this->assertArrayHasKey(1, $divisions);
		$this->assertEquals($league->divisions[0]->id, $divisions[1]->id);
		$this->assertEmpty($divisions[1]->teams);
		$this->assertArrayHasKey(2, $divisions);
		$this->assertEquals($league->divisions[1]->id, $divisions[2]->id);
		$this->assertEmpty($divisions[2]->teams);

		TeamFactory::make(4)->with('Divisions', $league->divisions[0])->persist();
		TeamFactory::make(2)->with('Divisions', $league->divisions[1])->persist();

		$divisions = $this->DivisionsTable->readByPlayerId($volunteer->id, true, true);
		$this->assertCount(2, $divisions);
		$this->assertArrayHasKey(0, $divisions);
		$this->assertEquals($league->divisions[0]->id, $divisions[0]->id);
		$this->assertCount(4, $divisions[0]->teams);
		$this->assertArrayHasKey(1, $divisions);
		$this->assertEquals($league->divisions[1]->id, $divisions[1]->id);
		$this->assertCount(2, $divisions[1]->teams);
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
		$affiliateId = mt_rand();
		/** @var Division $division */
		$division = DivisionFactory::make()
			->with('Leagues', [
				'affiliate_id' => $affiliateId,
			])
			->persist();
		$this->assertEquals($affiliateId, $this->DivisionsTable->affiliate($division->id));
	}

	/**
	 * Test league method
	 */
	public function testLeague(): void {
		/** @var Division $division */
		$division = DivisionFactory::make()
			->with('Leagues')
			->persist();
		$this->assertEquals($division->league_id, $this->DivisionsTable->league($division->id));
	}

	/**
	 * Test clearCache method
	 */
	public function testClearCache(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test translation behavior
	 */
	public function testTranslation(): void {
		$this->markTestIncomplete('Re-implement this after converting i18n.');

		Configure::load('options');
		Configure::load('sports');

		// With the default locale, it's English
		$division = $this->DivisionsTable->get(DIVISION_ID_MONDAY_LADDER);
		$this->assertEquals('Competitive', $division->name);

		// No Spanish name has been set, so it's still English
		I18n::setLocale('es');
		$division = $this->DivisionsTable->get(DIVISION_ID_MONDAY_LADDER);
		$this->assertEquals('Competitive', $division->name);

		// Set the Spanish name and save
		$division->name = 'Competitiva';
		$this->assertNotFalse($this->DivisionsTable->save($division));
		$this->assertEquals('Competitiva', $division->name);

		// Back to English, it should still be English
		I18n::setLocale('en');
		$division = $this->DivisionsTable->get(DIVISION_ID_MONDAY_LADDER);
		$this->assertEquals('Competitive', $division->name);

		// Spanish name has been set now, so it should read that
		I18n::setLocale('es');
		$division = $this->DivisionsTable->get(DIVISION_ID_MONDAY_LADDER);
		$this->assertEquals('Competitiva', $division->name);

		// Put the locale back to default
		I18n::setLocale('en');
	}

}
