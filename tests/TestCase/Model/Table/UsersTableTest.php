<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\UsersTable;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\UsersTable
	 */
	public $UsersTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Users') ? [] : ['className' => 'App\Model\Table\UsersTable'];
		$this->UsersTable = TableRegistry::get('Users', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->UsersTable);

		parent::tearDown();
	}

	/**
	 * Test validationPassword method
	 *
	 * @return void
	 */
	public function testValidationPassword(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationCreate method
	 *
	 * @return void
	 */
	public function testValidationCreate(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test findAuth method
	 *
	 * @return void
	 */
	public function testFindAuth(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activated method
	 *
	 * @return void
	 */
	public function testActivated(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
