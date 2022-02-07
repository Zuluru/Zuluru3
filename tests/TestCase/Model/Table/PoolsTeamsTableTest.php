<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\PoolsTeamsTable;

/**
 * App\Model\Table\PoolsTeamsTable Test Case
 */
class PoolsTeamsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\PoolsTeamsTable
	 */
	public $PoolsTeamsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('PoolsTeams') ? [] : ['className' => 'App\Model\Table\PoolsTeamsTable'];
		$this->PoolsTeamsTable = TableRegistry::get('PoolsTeams', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PoolsTeamsTable);

		parent::tearDown();
	}

	/**
	 * Test validationQualifiers method
	 *
	 * @return void
	 */
	public function testValidationQualifiers(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
