<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\LocksTable;

/**
 * App\Model\Table\LocksTable Test Case
 */
class LocksTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var LocksTable
	 */
	public $LocksTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Locks') ? [] : ['className' => LocksTable::class];
		$this->LocksTable = TableRegistry::getTableLocator()->get('Locks', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->LocksTable);

		parent::tearDown();
	}

}
