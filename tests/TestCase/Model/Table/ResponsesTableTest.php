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
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Responses') ? [] : ['className' => 'App\Model\Table\ResponsesTable'];
		$this->ResponsesTable = TableRegistry::getTableLocator()->get('Responses', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ResponsesTable);

		parent::tearDown();
	}

	/**
	 * Test beforeSave method
	 */
	public function testBeforeSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
