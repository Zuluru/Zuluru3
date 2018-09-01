<?php
namespace App\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use App\Controller\Component\LockComponent;

/**
 * App\Controller\Component\LockComponent Test Case
 */
class LockComponentTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Controller\Component\LockComponent
	 */
	public $LockComponent;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$registry = new ComponentRegistry();
		$this->LockComponent = new LockComponent($registry);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LockComponent);

		parent::tearDown();
	}

	/**
	 * Test shutdown method
	 *
	 * @return void
	 */
	public function testShutdown() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test lock method
	 *
	 * @return void
	 */
	public function testLock() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unlock method
	 *
	 * @return void
	 */
	public function testUnlock() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
