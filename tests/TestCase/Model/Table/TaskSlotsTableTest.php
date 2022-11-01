<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\TaskSlotFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\TaskSlotsTable;

/**
 * App\Model\Table\TaskSlotsTable Test Case
 */
class TaskSlotsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var TaskSlotsTable
	 */
	public $TaskSlotsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('TaskSlots') ? [] : ['className' => TaskSlotsTable::class];
		$this->TaskSlotsTable = TableRegistry::getTableLocator()->get('TaskSlots', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->TaskSlotsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $entity = TaskSlotFactory::make()->with('Tasks.Categories', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->TaskSlotsTable->affiliate($entity->id));
	}

}
