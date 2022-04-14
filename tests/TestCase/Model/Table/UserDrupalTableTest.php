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
	 */
	public function setUp(): void {
		// This needs to be defined or else the table's constructor tries to include library code
		if (!defined('DRUPAL_ROOT')) {
			define('DRUPAL_ROOT', TESTS . 'test_app' . DS . 'drupal');
		}

		parent::setUp();
		$config = TableRegistry::exists('UserDrupal') ? [] : ['className' => 'App\Model\Table\UserDrupalTable'];
		$this->UserDrupalTable = TableRegistry::getTableLocator()->get('UserDrupal', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->UserDrupalTable);

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

	/**
	 * Test beforeSave method
	 */
	public function testBeforeSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
