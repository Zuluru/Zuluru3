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
	 * @var \App\Model\Table\TaskSlotsTable
	 */
	public $TaskSlotsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('TaskSlots') ? [] : ['className' => 'App\Model\Table\TaskSlotsTable'];
		$this->TaskSlotsTable = TableRegistry::get('TaskSlots', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TaskSlotsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate(): void {
        $affiliateId = rand();
        $entity = TaskSlotFactory::make()->with('Tasks.Categories', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->TaskSlotsTable->affiliate($entity->id));
	}

}
