<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\StatsTable;

/**
 * App\Model\Table\StatsTable Test Case
 */
class StatsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\StatsTable
	 */
	public $StatsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Stats') ? [] : ['className' => 'App\Model\Table\StatsTable'];
		$this->StatsTable = TableRegistry::get('Stats', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->StatsTable);

		parent::tearDown();
	}

	/**
	 * Test applicable method
	 *
	 * @return void
	 */
	public function testApplicable() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
