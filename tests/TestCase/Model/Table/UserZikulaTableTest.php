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
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Users',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('UserZikula') ? [] : ['className' => 'App\Model\Table\UserZikulaTable'];
		$this->UserZikulaTable = TableRegistry::get('UserZikula', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->UserZikulaTable);

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

}
