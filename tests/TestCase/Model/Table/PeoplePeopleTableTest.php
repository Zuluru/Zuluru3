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
	 * @var PeoplePeopleTable
	 */
	public $PeoplePeopleTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('PeoplePeople') ? [] : ['className' => PeoplePeopleTable::class];
		$this->PeoplePeopleTable = TableRegistry::getTableLocator()->get('PeoplePeople', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->PeoplePeopleTable);

		parent::tearDown();
	}

	/**
	 * Test afterSave method
	 */
	public function testAfterSave(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
