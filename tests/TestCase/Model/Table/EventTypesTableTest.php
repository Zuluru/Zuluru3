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
	 * @var \App\Model\Table\EventTypesTable
	 */
	public $EventTypes;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		// TODO
    ];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('EventTypes') ? [] : ['className' => 'App\Model\Table\EventTypesTable'];
		$this->EventTypes = TableRegistry::get('EventTypes', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->EventTypes);

		parent::tearDown();
	}

	/**
	 * Test initialize method
	 *
	 * @return void
	 */
	public function testInitialize() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationDefault method
	 *
	 * @return void
	 */
	public function testValidationDefault() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
