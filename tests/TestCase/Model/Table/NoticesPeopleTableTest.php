<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\NoticesPeopleTable;

/**
 * App\Model\Table\NoticesPeopleTable Test Case
 */
class NoticesPeopleTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var NoticesPeopleTable
	 */
	public $NoticesPeopleTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('NoticesPeople') ? [] : ['className' => NoticesPeopleTable::class];
		$this->NoticesPeopleTable = TableRegistry::getTableLocator()->get('NoticesPeople', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->NoticesPeopleTable);

		parent::tearDown();
	}

}
