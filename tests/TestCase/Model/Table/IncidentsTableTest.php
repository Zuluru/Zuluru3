<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\IncidentsTable;

/**
 * App\Model\Table\IncidentsTable Test Case
 */
class IncidentsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\IncidentsTable
	 */
	public $IncidentsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Incidents') ? [] : ['className' => 'App\Model\Table\IncidentsTable'];
		$this->IncidentsTable = TableRegistry::getTableLocator()->get('Incidents', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->IncidentsTable);

		parent::tearDown();
	}

}
