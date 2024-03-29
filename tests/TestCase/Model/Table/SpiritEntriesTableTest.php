<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\SpiritEntriesTable;

/**
 * App\Model\Table\SpiritEntriesTable Test Case
 */
class SpiritEntriesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var SpiritEntriesTable
	 */
	public $SpiritEntriesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('SpiritEntries') ? [] : ['className' => SpiritEntriesTable::class];
		$this->SpiritEntriesTable = TableRegistry::getTableLocator()->get('SpiritEntries', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->SpiritEntriesTable);

		parent::tearDown();
	}

	/**
	 * Test addValidation method
	 */
	public function testAddValidation(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
