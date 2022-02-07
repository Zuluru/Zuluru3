<?php
namespace App\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Model\Table\UserJoomlaTable;

/**
 * App\Model\Table\UserJoomlaTable Test Case
 */
class UserJoomlaTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\UserJoomlaTable
	 */
	public $UserJoomlaTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		// This needs to be defined or else the table's constructor tries to include library code
		if (!defined('JPATH_BASE')) {
			define('JPATH_BASE', TESTS . 'test_app' . DS . 'joomla');
		}

		parent::setUp();
		$config = TableRegistry::exists('UserJoomla') ? [] : ['className' => 'App\Model\Table\UserJoomlaTable'];
		$this->UserJoomlaTable = TableRegistry::get('UserJoomla', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->UserJoomlaTable);

		parent::tearDown();
	}

	/**
	 * Test defaultConnectionName method
	 *
	 * @return void
	 */
	public function testDefaultConnectionName(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test comparepassword method
	 *
	 * @return void
	 */
	public function testComparepassword(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test hashPassword method
	 *
	 * @return void
	 */
	public function testHashPassword(): void {
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

	/**
	 * Test beforeDelete method
	 *
	 * @return void
	 */
	public function testBeforeDelete(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
