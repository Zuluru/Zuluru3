<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\StatsTable;

/**
 * App\Model\Table\StatsTable Test Case
 */
class StatsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var StatsTable
	 */
	public $StatsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Stats') ? [] : ['className' => StatsTable::class];
		$this->StatsTable = TableRegistry::getTableLocator()->get('Stats', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->StatsTable);

		parent::tearDown();
	}

	/**
	 * Test applicable method
	 */
	public function testApplicable(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
