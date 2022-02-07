<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\ResponsesTable;

/**
 * App\Model\Table\ResponsesTable Test Case
 */
class ResponsesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\ResponsesTable
	 */
	public $ResponsesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Responses') ? [] : ['className' => 'App\Model\Table\ResponsesTable'];
		$this->ResponsesTable = TableRegistry::get('Responses', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ResponsesTable);

		parent::tearDown();
	}

	/**
	 * Test beforeSave method
	 *
	 * @return void
	 */
	public function testBeforeSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
