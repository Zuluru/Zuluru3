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
	 */
	public function setUp(): void {
		parent::setUp();
		$registry = new ComponentRegistry();
		$this->LockComponent = new LockComponent($registry);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->LockComponent);

		parent::tearDown();
	}

	/**
	 * Test shutdown method
	 */
	public function testShutdown(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test lock method
	 */
	public function testLock(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unlock method
	 */
	public function testUnlock(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
