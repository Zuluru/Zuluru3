<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\TeamEventsTable;

/**
 * App\Model\Table\TeamEventsTable Test Case
 */
class TeamEventsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var TeamEventsTable
	 */
	public $TeamEventsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('TeamEvents') ? [] : ['className' => TeamEventsTable::class];
		$this->TeamEventsTable = TableRegistry::getTableLocator()->get('TeamEvents', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->TeamEventsTable);

		parent::tearDown();
	}

	/**
	 * Test readAttendance method
	 */
	public function testReadAttendance(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test createAttendance method
	 */
	public function testCreateAttendance(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
