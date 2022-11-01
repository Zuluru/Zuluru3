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
	 * @var NoticesTable
	 */
	public $NoticesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Notices') ? [] : ['className' => NoticesTable::class];
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
