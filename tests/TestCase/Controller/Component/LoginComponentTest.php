<?php
namespace App\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use App\Controller\Component\LoginComponent;

/**
 * App\Controller\Component\LoginComponent Test Case
 */
class LoginComponentTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Controller\Component\LoginComponent
	 */
	public $LoginComponent;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$registry = new ComponentRegistry();
		$this->LoginComponent = new LoginComponent($registry);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LoginComponent);

		parent::tearDown();
	}

	/**
	 * Test login method
	 *
	 * @return void
	 */
	public function testLogin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test expired method
	 *
	 * @return void
	 */
	public function testExpired() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
