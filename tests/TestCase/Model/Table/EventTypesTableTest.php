<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventTypesTable;
use Cake\ORM\TableRegistry;

/**
 * App\Model\Table\EventTypesTable Test Case
 */
class EventTypesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var EventTypesTable
	 */
	public $EventTypes;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('EventTypes') ? [] : ['className' => EventTypesTable::class];
		$this->EventTypes = TableRegistry::getTableLocator()->get('EventTypes', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->EventTypes);

		parent::tearDown();
	}

	/**
	 * Test initialize method
	 */
	public function testInitialize(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationDefault method
	 */
	public function testValidationDefault(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
