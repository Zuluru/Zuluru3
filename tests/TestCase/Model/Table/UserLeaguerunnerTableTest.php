<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\UserLeaguerunnerTable;

/**
 * App\Model\Table\UserLeaguerunnerTable Test Case
 */
class UserLeaguerunnerTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\UserLeaguerunnerTable
	 */
	public $UserLeaguerunnerTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('UserLeaguerunner') ? [] : ['className' => 'App\Model\Table\UserLeaguerunnerTable'];
		$this->UserLeaguerunnerTable = TableRegistry::get('UserLeaguerunner', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->UserLeaguerunnerTable);

		parent::tearDown();
	}

}
