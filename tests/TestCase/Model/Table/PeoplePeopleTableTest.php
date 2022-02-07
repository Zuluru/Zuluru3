<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\PeoplePeopleTable;

/**
 * App\Model\Table\PeoplePeopleTable Test Case
 */
class PeoplePeopleTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\PeoplePeopleTable
	 */
	public $PeoplePeopleTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('PeoplePeople') ? [] : ['className' => 'App\Model\Table\PeoplePeopleTable'];
		$this->PeoplePeopleTable = TableRegistry::get('PeoplePeople', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PeoplePeopleTable);

		parent::tearDown();
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
