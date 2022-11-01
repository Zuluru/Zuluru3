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
	 * @var TasksTable
	 */
	public $TasksTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Tasks') ? [] : ['className' => TasksTable::class];
		$this->TasksTable = TableRegistry::getTableLocator()->get('Tasks', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->TasksTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $entity = TaskFactory::make()->with('Categories', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->TasksTable->affiliate($entity->id));
	}

}
