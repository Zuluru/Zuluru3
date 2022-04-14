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
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('PoolsTeams') ? [] : ['className' => 'App\Model\Table\PoolsTeamsTable'];
		$this->PoolsTeamsTable = TableRegistry::getTableLocator()->get('PoolsTeams', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->PoolsTeamsTable);

		parent::tearDown();
	}

	/**
	 * Test validationQualifiers method
	 */
	public function testValidationQualifiers(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
