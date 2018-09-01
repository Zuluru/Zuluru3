<?php
namespace App\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use App\Controller\Component\LoginJoomlaComponent;

/**
 * App\Controller\Component\LoginJoomlaComponent Test Case
 */
class LoginJoomlaComponentTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Controller\Component\LoginJoomlaComponent
	 */
	public $LoginJoomlaComponent;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$registry = new ComponentRegistry();
		$this->LoginJoomlaComponent = new LoginJoomlaComponent($registry);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LoginJoomlaComponent);

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
