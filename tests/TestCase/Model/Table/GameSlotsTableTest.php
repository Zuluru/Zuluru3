<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\GameSlotsTable;

/**
 * App\Model\Table\GameSlotsTable Test Case
 */
class GameSlotsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\GameSlotsTable
	 */
	public $GameSlotsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.game_slots',
						'app.divisions_gameslots',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('GameSlots') ? [] : ['className' => 'App\Model\Table\GameSlotsTable'];
		$this->GameSlotsTable = TableRegistry::get('GameSlots', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->GameSlotsTable);

		parent::tearDown();
	}

	/**
	 * Test validationCommon method
	 *
	 * @return void
	 */
	public function testValidationCommon() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationDefault method
	 *
	 * @return void
	 */
	public function testValidationDefault() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationBulk method
	 *
	 * @return void
	 */
	public function testValidationBulk() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeMarshal method
	 *
	 * @return void
	 */
	public function testBeforeMarshal() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test findAvailable method
	 *
	 * @return void
	 */
	public function testFindAvailable() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->GameSlotsTable->affiliate(1));
	}

	/**
	 * Test sport method
	 *
	 * @return void
	 */
	public function testSport() {
		$this->assertEquals('ultimate', $this->GameSlotsTable->sport(GAME_SLOT_ID_MONDAY_SUNNYBROOK_1_WEEK_1));
		$this->assertEquals('soccer', $this->GameSlotsTable->sport(GAME_SLOT_ID_MONDAY_BROADACRES_WEEK_1));
	}

	/**
	 * Test compareTimeAndField method
	 *
	 * @return void
	 */
	public function testCompareTimeAndField() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
