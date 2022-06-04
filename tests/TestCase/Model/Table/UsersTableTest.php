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
	 * @var UsersTable
	 */
	public $UsersTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];
		$this->UsersTable = TableRegistry::getTableLocator()->get('Users', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->UsersTable);

		parent::tearDown();
	}

	/**
	 * Test validationPassword method
	 */
	public function testValidationPassword(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationCreate method
	 */
	public function testValidationCreate(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test findAuth method
	 */
	public function testFindAuth(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activated method
	 */
	public function testActivated(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
