<?php
namespace App\Test\TestCase\Model\Table;

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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
			'app.categories',
				'app.tasks',
					'app.task_slots',
	];

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
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->TaskSlotsTable->affiliate(1));
	}

}
