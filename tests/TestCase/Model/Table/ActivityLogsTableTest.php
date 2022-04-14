<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\ActivityLogsTable;

/**
 * App\Model\Table\ActivityLogsTable Test Case
 */
class ActivityLogsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\ActivityLogsTable
	 */
	public $ActivityLogsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('ActivityLogs') ? [] : ['className' => 'App\Model\Table\ActivityLogsTable'];
		$this->ActivityLogsTable = TableRegistry::getTableLocator()->get('ActivityLogs', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ActivityLogsTable);

		parent::tearDown();
	}

}
