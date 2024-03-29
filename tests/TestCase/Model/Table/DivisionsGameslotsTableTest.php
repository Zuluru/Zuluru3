<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\DivisionsGameslotsTable;

/**
 * App\Model\Table\DivisionsGameslotsTable Test Case
 */
class DivisionsGameslotsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var DivisionsGameslotsTable
	 */
	public $DivisionsGameslotsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('DivisionsGameslots') ? [] : ['className' => DivisionsGameslotsTable::class];
		$this->DivisionsGameslotsTable = TableRegistry::getTableLocator()->get('DivisionsGameslots', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->DivisionsGameslotsTable);

		parent::tearDown();
	}

}
