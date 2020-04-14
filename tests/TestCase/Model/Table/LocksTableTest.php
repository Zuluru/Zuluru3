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
	 * @var \App\Model\Table\LocksTable
	 */
	public $LocksTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Locks',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Locks') ? [] : ['className' => 'App\Model\Table\LocksTable'];
		$this->LocksTable = TableRegistry::get('Locks', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LocksTable);

		parent::tearDown();
	}

}
