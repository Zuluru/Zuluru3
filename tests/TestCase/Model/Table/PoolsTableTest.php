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
	 * @var \App\Model\Table\PoolsTable
	 */
	public $PoolsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Pools') ? [] : ['className' => 'App\Model\Table\PoolsTable'];
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
