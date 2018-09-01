<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\UserDrupalTable;

/**
 * App\Model\Table\UserDrupalTable Test Case
 */
class UserDrupalTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\UserDrupalTable
	 */
	public $UserDrupalTable;

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
		if (!defined('DRUPAL_ROOT')) {
			define('DRUPAL_ROOT', null);
		}

		parent::setUp();
		$config = TableRegistry::exists('UserDrupal') ? [] : ['className' => 'App\Model\Table\UserDrupalTable'];
		$this->UserDrupalTable = TableRegistry::get('UserDrupal', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->UserDrupalTable);

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

	/**
	 * Test beforeSave method
	 *
	 * @return void
	 */
	public function testBeforeSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
