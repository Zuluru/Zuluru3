<?php
namespace TestCase\Model\Entity;

use App\Model\Entity\User;
use Cake\Auth\DefaultPasswordHasher;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\User Test Case
 */
class UserTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Entity\User
	 */
	public $User;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$this->User = new User();
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->User);

		parent::tearDown();
	}

	/**
	 * Test _setPassword($value);
	 */
	public function testSetPassword(): void {
		$testPassword = 'insecure';
		$this->User->password = $testPassword;
		$this->assertNotEquals($testPassword, $this->User->password, 'Password stored in plain-text');
		$hasher = new DefaultPasswordHasher();
		$this->assertTrue($hasher->check($testPassword, $this->User->password), 'Password hash doesn\'t match what should have been generated from the input password');
	}

	/**
	 * Test merge method
	 */
	public function testMerge(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
