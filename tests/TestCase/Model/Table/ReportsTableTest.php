<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ReportsTable;
use Cake\ORM\TableRegistry;

/**
 * App\Model\Table\ReportsTable Test Case
 */
class ReportsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\ReportsTable
	 */
	public $Reports;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
        'app.reports',
    ];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Reports') ? [] : ['className' => 'App\Model\Table\ReportsTable'];
		$this->Reports = TableRegistry::get('Reports', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Reports);

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

	/**
	 * Test buildRules method
	 *
	 * @return void
	 */
	public function testBuildRules() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
