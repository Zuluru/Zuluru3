<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\NoticesTable;

/**
 * App\Model\Table\NoticesTable Test Case
 */
class NoticesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\NoticesTable
	 */
	public $NoticesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Notices') ? [] : ['className' => 'App\Model\Table\NoticesTable'];
		$this->NoticesTable = TableRegistry::getTableLocator()->get('Notices', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->NoticesTable);

		parent::tearDown();
	}

}
