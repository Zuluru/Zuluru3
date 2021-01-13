<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\TaskFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\TasksTable;

/**
 * App\Model\Table\TasksTable Test Case
 */
class TasksTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\TasksTable
	 */
	public $TasksTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Tasks') ? [] : ['className' => 'App\Model\Table\TasksTable'];
		$this->TasksTable = TableRegistry::get('Tasks', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TasksTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $entity = TaskFactory::make()->with('Categories', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->TasksTable->affiliate($entity->id));
	}

}
