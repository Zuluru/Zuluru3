<?php
namespace App\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use App\Model\Table\DivisionsTable;

/**
 * App\Model\Table\DivisionsTable Test Case
 */
class DivisionsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\DivisionsTable
	 */
	public $DivisionsTable;

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
					'app.teams',
					'app.divisions_days',
					'app.divisions_people',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Divisions') ? [] : ['className' => 'App\Model\Table\DivisionsTable'];
		$this->DivisionsTable = TableRegistry::get('Divisions', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->DivisionsTable);

		parent::tearDown();
	}

	/**
	 * Test beforeSave method
	 *
	 * @return void
	 */
	public function testBeforeSave() {
		$this->markTestIncomplete('Not implemented yet.');
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
	 * Test findOpen method
	 *
	 * @return void
	 */
	public function testFindOpen() {
		$divisions = $this->DivisionsTable->find('open');
		$this->assertEquals(7, $divisions->count());
	}

	/**
	 * Test findDate method
	 *
	 * @return void
	 */
	public function testFindDate() {
		$divisions = $this->DivisionsTable->find('date', ['date' => new FrozenDate('Monday')]);
		$this->assertEquals(3, $divisions->count());
		$divisions = $this->DivisionsTable->find('date', ['date' => new FrozenDate('Tuesday')]);
		$this->assertEquals(1, $divisions->count());
		// Monday playoffs happen on Sunday
		$divisions = $this->DivisionsTable->find('date', ['date' => new FrozenDate('Sunday')]);
		$this->assertEquals(2, $divisions->count());
	}

	/**
	 * Test readByPlayerId method
	 *
	 * @return void
	 */
	public function testReadByPlayerId() {
		// readByPlayerId compares the open date to today, so we need to set "today" for this test to be reliable
		FrozenDate::setTestNow(new FrozenDate('May 31'));

		$divisions = $this->DivisionsTable->readByPlayerId(PERSON_ID_COORDINATOR);
		$this->assertEquals(3, count($divisions));
		$this->assertArrayHasKey(0, $divisions);
		$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $divisions[0]->id);
		$this->assertEmpty($divisions[0]->teams);
		$this->assertArrayHasKey(1, $divisions);
		$this->assertEquals(DIVISION_ID_MONDAY_PLAYOFF, $divisions[1]->id);
		$this->assertEmpty($divisions[1]->teams);
		$this->assertArrayHasKey(2, $divisions);
		$this->assertEquals(DIVISION_ID_THURSDAY_ROUND_ROBIN, $divisions[2]->id);
		$this->assertEmpty($divisions[2]->teams);

		$divisions = $this->DivisionsTable->readByPlayerId(PERSON_ID_COORDINATOR, false);
		$this->assertEquals(4, count($divisions));
		$this->assertArrayHasKey(0, $divisions);
		$this->assertEquals(DIVISION_ID_MONDAY_LADDER_PAST, $divisions[0]->id);
		$this->assertArrayHasKey(1, $divisions);
		$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $divisions[1]->id);
		$this->assertEmpty($divisions[1]->teams);
		$this->assertArrayHasKey(2, $divisions);
		$this->assertEquals(DIVISION_ID_MONDAY_PLAYOFF, $divisions[2]->id);
		$this->assertEmpty($divisions[2]->teams);
		$this->assertArrayHasKey(3, $divisions);
		$this->assertEquals(DIVISION_ID_THURSDAY_ROUND_ROBIN, $divisions[3]->id);
		$this->assertEmpty($divisions[3]->teams);

		$divisions = $this->DivisionsTable->readByPlayerId(PERSON_ID_COORDINATOR, true, true);
		$this->assertEquals(3, count($divisions));
		$this->assertArrayHasKey(0, $divisions);
		$this->assertEquals(DIVISION_ID_MONDAY_LADDER, $divisions[0]->id);
		$this->assertEquals(8, count($divisions[0]->teams));
		$this->assertArrayHasKey(1, $divisions);
		$this->assertEquals(DIVISION_ID_MONDAY_PLAYOFF, $divisions[1]->id);
		$this->assertEquals(1, count($divisions[1]->teams));
		$this->assertArrayHasKey(2, $divisions);
		$this->assertEquals(DIVISION_ID_THURSDAY_ROUND_ROBIN, $divisions[2]->id);
		$this->assertEquals(2, count($divisions[2]->teams));
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->DivisionsTable->affiliate(DIVISION_ID_MONDAY_LADDER));
	}

	/**
	 * Test league method
	 *
	 * @return void
	 */
	public function testLeague() {
		$this->assertEquals(LEAGUE_ID_MONDAY_PAST, $this->DivisionsTable->league(DIVISION_ID_MONDAY_LADDER_PAST));
		$this->assertEquals(LEAGUE_ID_MONDAY, $this->DivisionsTable->league(DIVISION_ID_MONDAY_LADDER));
		$this->assertEquals(LEAGUE_ID_MONDAY, $this->DivisionsTable->league(DIVISION_ID_MONDAY_PLAYOFF));
		$this->assertEquals(LEAGUE_ID_TUESDAY, $this->DivisionsTable->league(DIVISION_ID_TUESDAY_ROUND_ROBIN));
	}

	/**
	 * Test clearCache method
	 *
	 * @return void
	 */
	public function testClearCache() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test translation behavior
	 *
	 * @return void
	 */
	public function testTranslation() {
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
