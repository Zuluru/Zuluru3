<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\DivisionFactory;
use App\Test\Factory\GameFactory;
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
	 * Test findDate method
	 *
	 * @return void
	 */
	public function testFindDate() {
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
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
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
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
	    $affiliateId = rand();
	    $division = DivisionFactory::make()
            ->with('Leagues', [
                'affiliate_id' => $affiliateId,
            ])
            ->persist();
		$this->assertEquals($affiliateId, $this->DivisionsTable->affiliate($division->id));
	}

	/**
	 * Test league method
	 *
	 * @return void
	 */
	public function testLeague() {
        $division = DivisionFactory::make()
            ->with('Leagues')
            ->persist();
		$this->assertEquals($division->league_id, $this->DivisionsTable->league($division->id));
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
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
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
