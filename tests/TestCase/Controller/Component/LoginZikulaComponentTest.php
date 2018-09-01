<?php
namespace App\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use App\Controller\Component\LoginZikulaComponent;

/**
 * App\Controller\Component\LoginZikulaComponent Test Case
 */
class LoginZikulaComponentTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Controller\Component\LoginZikulaComponent
	 */
	public $LoginZikulaComponent;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$registry = new ComponentRegistry();
		$this->LoginZikulaComponent = new LoginZikulaComponent($registry);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LoginZikulaComponent);

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
