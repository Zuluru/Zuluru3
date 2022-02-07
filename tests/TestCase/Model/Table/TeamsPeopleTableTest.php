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
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('TeamsPeople') ? [] : ['className' => 'App\Model\Table\TeamsPeopleTable'];
		$this->TeamsPeopleTable = TableRegistry::get('TeamsPeople', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TeamsPeopleTable);

		parent::tearDown();
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 *
	 * @return void
	 */
	public function testBeforeDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
