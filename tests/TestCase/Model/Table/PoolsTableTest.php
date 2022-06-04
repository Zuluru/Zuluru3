<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\PoolsTable;

/**
 * App\Model\Table\PoolsTable Test Case
 */
class PoolsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var PoolsTable
	 */
	public $PoolsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Pools') ? [] : ['className' => PoolsTable::class];
		$this->PoolsTable = TableRegistry::getTableLocator()->get('Pools', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->PoolsTable);

		parent::tearDown();
	}

}
