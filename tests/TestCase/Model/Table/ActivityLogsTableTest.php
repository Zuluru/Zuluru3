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
	 * @var ActivityLogsTable
	 */
	public $ActivityLogsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('ActivityLogs') ? [] : ['className' => ActivityLogsTable::class];
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
