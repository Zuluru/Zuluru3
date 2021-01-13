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
	 * @var \App\Model\Table\NoticesPeopleTable
	 */
	public $NoticesPeopleTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('NoticesPeople') ? [] : ['className' => 'App\Model\Table\NoticesPeopleTable'];
		$this->NoticesPeopleTable = TableRegistry::get('NoticesPeople', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->NoticesPeopleTable);

		parent::tearDown();
	}

}
