<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\AttendancesTable;

/**
 * App\Model\Table\AttendancesTable Test Case
 */
class AttendancesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var AttendancesTable
	 */
	public $AttendancesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Attendances') ? [] : ['className' => AttendancesTable::class];
		$this->AttendancesTable = TableRegistry::getTableLocator()->get('Attendances', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->AttendancesTable);

		parent::tearDown();
	}

}
