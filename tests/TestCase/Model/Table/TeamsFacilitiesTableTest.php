<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\TeamsFacilitiesTable;

/**
 * App\Model\Table\TeamsFacilitiesTable Test Case
 */
class TeamsFacilitiesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\TeamsFacilitiesTable
	 */
	public $TeamsFacilitiesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('TeamsFacilities') ? [] : ['className' => 'App\Model\Table\TeamsFacilitiesTable'];
		$this->TeamsFacilitiesTable = TableRegistry::getTableLocator()->get('TeamsFacilities', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->TeamsFacilitiesTable);

		parent::tearDown();
	}

}
