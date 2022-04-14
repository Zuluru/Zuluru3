<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\TeamsPeopleTable;

/**
 * App\Model\Table\TeamsPeopleTable Test Case
 */
class TeamsPeopleTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\TeamsPeopleTable
	 */
	public $TeamsPeopleTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('TeamsPeople') ? [] : ['className' => 'App\Model\Table\TeamsPeopleTable'];
		$this->TeamsPeopleTable = TableRegistry::getTableLocator()->get('TeamsPeople', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->TeamsPeopleTable);

		parent::tearDown();
	}

	/**
	 * Test afterSave method
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 */
	public function testBeforeDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
