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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		// This needs to be defined or else the table's constructor tries to include library code
		// TODOLATER: Will need to do something better than this when we implement these tests
		Configure::write('Security.joomlaPrefix', 'x');

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
	public function testDefaultConnectionName() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test comparepassword method
	 *
	 * @return void
	 */
	public function testComparepassword() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test hashPassword method
	 *
	 * @return void
	 */
	public function testHashPassword() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test activated method
	 *
	 * @return void
	 */
	public function testActivated() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 *
	 * @return void
	 */
	public function testBeforeDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
