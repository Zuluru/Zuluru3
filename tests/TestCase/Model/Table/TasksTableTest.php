<?php
namespace App\Test\TestCase\Model\Table;

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
	];

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
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->TasksTable->affiliate(1));
	}

}
