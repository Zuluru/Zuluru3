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
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Pools') ? [] : ['className' => 'App\Model\Table\PoolsTable'];
		$this->PoolsTable = TableRegistry::get('Pools', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PoolsTable);

		parent::tearDown();
	}

}
