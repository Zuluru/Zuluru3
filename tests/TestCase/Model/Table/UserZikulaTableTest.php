<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\UserZikulaTable;

/**
 * App\Model\Table\UserZikulaTable Test Case
 */
class UserZikulaTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\UserZikulaTable
	 */
	public $UserZikulaTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('UserZikula') ? [] : ['className' => 'App\Model\Table\UserZikulaTable'];
		$this->UserZikulaTable = TableRegistry::getTableLocator()->get('UserZikula', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->UserZikulaTable);

		parent::tearDown();
	}

	/**
	 * Test activated method
	 */
	public function testActivated(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 */
	public function testBeforeDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
