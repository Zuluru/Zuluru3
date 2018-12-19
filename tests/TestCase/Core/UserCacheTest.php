<?php
namespace App\Test\TestCase\Controller\Component;

use Cake\TestSuite\IntegrationTestCase;
use App\Core\UserCache;

/**
 * App\Core\UserCache Test Case
 */
class UserCacheTest extends IntegrationTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Core\UserCache
	 */
	public $UserCache;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->session(['Auth.id' => 1]);
		$this->UserCache = UserCache::getInstance();
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->UserCache);

		parent::tearDown();
	}

	/**
	 * Test getInstance method
	 *
	 * @return void
	 */
	public function testGetInstance() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initializeData method
	 *
	 * @return void
	 */
	public function testInitializeData() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initializeId method
	 *
	 * @return void
	 */
	public function testInitializeId() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test currentId method
	 *
	 * @return void
	 */
	public function testCurrentId() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test realId method
	 *
	 * @return void
	 */
	public function testRealId() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test read method
	 *
	 * @return void
	 */
	public function testRead() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear method
	 *
	 * @return void
	 */
	public function testClear() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allActAs method
	 *
	 * @return void
	 */
	public function testAllActAs() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _deleteTeamData method
	 *
	 * @return void
	 */
	public function testDeleteTeamData() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _deleteFranchiseData method
	 *
	 * @return void
	 */
	public function testDeleteFranchiseData() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
