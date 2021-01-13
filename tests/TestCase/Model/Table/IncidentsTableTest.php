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
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Incidents') ? [] : ['className' => 'App\Model\Table\IncidentsTable'];
		$this->IncidentsTable = TableRegistry::get('Incidents', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->IncidentsTable);

		parent::tearDown();
	}

}
