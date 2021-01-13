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
	 * @var \App\Model\Table\DivisionsGameslotsTable
	 */
	public $DivisionsGameslotsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('DivisionsGameslots') ? [] : ['className' => 'App\Model\Table\DivisionsGameslotsTable'];
		$this->DivisionsGameslotsTable = TableRegistry::get('DivisionsGameslots', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->DivisionsGameslotsTable);

		parent::tearDown();
	}

}
