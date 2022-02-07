<?php
namespace App\Test\TestCase\Controller\Component;

use App\Core\UserCache;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Core\UserCache Test Case
 */
class UserCacheTest extends TestCase {

	use IntegrationTestTrait;

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
	public function testGetInstance(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initializeData method
	 *
	 * @return void
	 */
	public function testInitializeData(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initializeId method
	 *
	 * @return void
	 */
	public function testInitializeId(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test currentId method
	 *
	 * @return void
	 */
	public function testCurrentId(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test realId method
	 *
	 * @return void
	 */
	public function testRealId(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test read method
	 *
	 * @return void
	 */
	public function testRead(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear method
	 *
	 * @return void
	 */
	public function testClear(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allActAs method
	 *
	 * @return void
	 */
	public function testAllActAs(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _deleteTeamData method
	 *
	 * @return void
	 */
	public function testDeleteTeamData(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _deleteFranchiseData method
	 *
	 * @return void
	 */
	public function testDeleteFranchiseData(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
