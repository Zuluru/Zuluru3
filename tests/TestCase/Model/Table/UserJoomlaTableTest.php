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
	 */
	public function setUp(): void {
		// This needs to be defined or else the table's constructor tries to include library code
		if (!defined('JPATH_BASE')) {
			define('JPATH_BASE', TESTS . 'test_app' . DS . 'joomla');
		}

		parent::setUp();
		$config = TableRegistry::exists('UserJoomla') ? [] : ['className' => 'App\Model\Table\UserJoomlaTable'];
		$this->UserJoomlaTable = TableRegistry::getTableLocator()->get('UserJoomla', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->UserJoomlaTable);

		parent::tearDown();
	}

	/**
	 * Test defaultConnectionName method
	 */
	public function testDefaultConnectionName(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test comparepassword method
	 */
	public function testComparepassword(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test hashPassword method
	 */
	public function testHashPassword(): void {
		$this->markTestIncomplete('Not implemented yet.');
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
