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
	 */
	public function setUp(): void {
		parent::setUp();
		$this->session(['Auth.id' => 1]);
		$this->UserCache = UserCache::getInstance();
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->UserCache);

		parent::tearDown();
	}

	/**
	 * Test getInstance method
	 */
	public function testGetInstance(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initializeData method
	 */
	public function testInitializeData(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test initializeId method
	 */
	public function testInitializeId(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test currentId method
	 */
	public function testCurrentId(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test realId method
	 */
	public function testRealId(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test read method
	 */
	public function testRead(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test clear method
	 */
	public function testClear(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test allActAs method
	 */
	public function testAllActAs(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _deleteTeamData method
	 */
	public function testDeleteTeamData(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test _deleteFranchiseData method
	 */
	public function testDeleteFranchiseData(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
