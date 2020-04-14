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
	 * @var \App\Model\Table\DaysTable
	 */
	public $DaysTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Days') ? [] : ['className' => 'App\Model\Table\DaysTable'];
		$this->DaysTable = TableRegistry::get('Days', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->DaysTable);

		parent::tearDown();
	}

}
