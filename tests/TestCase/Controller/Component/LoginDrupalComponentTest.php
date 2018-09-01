<?php
namespace App\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;
use App\Controller\Component\LoginDrupalComponent;

/**
 * App\Controller\Component\LoginDrupalComponent Test Case
 */
class LoginDrupalComponentTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Controller\Component\LoginDrupalComponent
	 */
	public $LoginDrupalComponent;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$registry = new ComponentRegistry();
		$this->LoginDrupalComponent = new LoginDrupalComponent($registry);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->LoginDrupalComponent);

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
