<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\DaysTable;

/**
 * App\Model\Table\DaysTable Test Case
 */
class DaysTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var DaysTable
	 */
	public $DaysTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Days') ? [] : ['className' => DaysTable::class];
		$this->DaysTable = TableRegistry::getTableLocator()->get('Days', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->DaysTable);

		parent::tearDown();
	}

}
