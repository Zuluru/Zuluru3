<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\WaiversPeopleTable;

/**
 * App\Model\Table\WaiversPeopleTable Test Case
 */
class WaiversPeopleTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var WaiversPeopleTable
	 */
	public $WaiversPeopleTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('WaiversPeople') ? [] : ['className' => WaiversPeopleTable::class];
		$this->WaiversPeopleTable = TableRegistry::getTableLocator()->get('WaiversPeople', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->WaiversPeopleTable);

		parent::tearDown();
	}

}
