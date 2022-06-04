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
	 * @var TeamsFacilitiesTable
	 */
	public $TeamsFacilitiesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('TeamsFacilities') ? [] : ['className' => TeamsFacilitiesTable::class];
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
