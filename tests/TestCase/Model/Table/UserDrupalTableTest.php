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
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		// This needs to be defined or else the table's constructor tries to include library code
		if (!defined('DRUPAL_ROOT')) {
			define('DRUPAL_ROOT', TESTS . 'test_app' . DS . 'drupal');
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
