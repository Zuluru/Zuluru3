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
	 * @var \App\Model\Table\TeamEventsTable
	 */
	public $TeamEventsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('TeamEvents') ? [] : ['className' => 'App\Model\Table\TeamEventsTable'];
		$this->TeamEventsTable = TableRegistry::get('TeamEvents', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TeamEventsTable);

		parent::tearDown();
	}

	/**
	 * Test readAttendance method
	 *
	 * @return void
	 */
	public function testReadAttendance(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test createAttendance method
	 *
	 * @return void
	 */
	public function testCreateAttendance(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
