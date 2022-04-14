<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\GameSlotFactory;
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
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('GameSlots') ? [] : ['className' => 'App\Model\Table\GameSlotsTable'];
		$this->GameSlotsTable = TableRegistry::getTableLocator()->get('GameSlots', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->GameSlotsTable);

		parent::tearDown();
	}

	/**
	 * Test validationCommon method
	 */
	public function testValidationCommon(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationDefault method
	 */
	public function testValidationDefault(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationBulk method
	 */
	public function testValidationBulk(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeMarshal method
	 */
	public function testBeforeMarshal(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test findAvailable method
	 */
	public function testFindAvailable(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = rand();
        $gameSlot = GameSlotFactory::make()
            ->with('Fields.Facilities.Regions', ['affiliate_id' => $affiliateId])
            ->persist();
		$this->assertEquals($affiliateId, $this->GameSlotsTable->affiliate($gameSlot->id));
	}

	/**
	 * Test sport method
	 */
	public function testSport(): void {
        $gameSlot1 = GameSlotFactory::make()->with('Fields', ['sport' => 'ultimate'])->persist();
        $gameSlot2 = GameSlotFactory::make()->with('Fields', ['sport' => 'soccer'])->persist();
		$this->assertEquals('ultimate', $this->GameSlotsTable->sport($gameSlot1->id));
		$this->assertEquals('soccer', $this->GameSlotsTable->sport($gameSlot2->id));
	}

	/**
	 * Test compareTimeAndField method
	 */
	public function testCompareTimeAndField(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
