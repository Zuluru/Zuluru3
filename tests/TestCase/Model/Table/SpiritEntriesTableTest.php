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
	 * @var \App\Model\Table\SpiritEntriesTable
	 */
	public $SpiritEntriesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('SpiritEntries') ? [] : ['className' => 'App\Model\Table\SpiritEntriesTable'];
		$this->SpiritEntriesTable = TableRegistry::get('SpiritEntries', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->SpiritEntriesTable);

		parent::tearDown();
	}

	/**
	 * Test addValidation method
	 *
	 * @return void
	 */
	public function testAddValidation(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
