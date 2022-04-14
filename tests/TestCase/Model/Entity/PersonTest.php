<?php
namespace TestCase\Model\Entity;

use App\Model\Entity\Person;
use App\Test\Factory\PersonFactory;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class PersonTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Groups',
	];

	public $autoFixtures = false;

	/**
	 * The Entity we'll be using in the test
	 *
	 * @var \App\Model\Entity\Person
	 */
	public $person;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$this->person = PersonFactory::make([
			'first_name' => 'Amy',
			'last_name' => 'Administrator',
			'roster_designation' => 'Woman',
			'status' => 'active',
			'alternate_first_name' => 'Buford',
			'alternate_last_name' => 'Tannen',
			'alternate_email' => 'Buford.Tannen@HillValley.com',
		])->with('Users', [
			'user_name' => 'amy',
			'password' => 'amypassword',
			'email' => 'amy@zuluru.org',
		])->getEntity();
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->person);

		parent::tearDown();
	}

	/**
	 * Test _getUserName()
	 */
	public function testGetUserName(): void {
		$this->assertEquals('amy', $this->person->user_name);
	}

	/**
	 * Test _getPassword()
	 */
	public function testGetPassword(): void {
		$this->assertTrue(password_verify('amypassword', $this->person->password));
	}

	/**
	 * Test _getLastLogin()
	 */
	public function testLastLogin(): void {
		$this->assertEquals(new FrozenTime('yesterday'), $this->person->last_login);
	}

	/**
	 * Test _getClientIp()
	 */
	public function testGetClientIp(): void {
		$this->assertEquals('127.0.0.1', $this->person->client_ip);
	}

	/**
	 * Test _getFullName()
	 */
	public function testGetFullName(): void {
		$this->assertEquals('Amy Administrator', $this->person->full_name);
	}

	/**
	 * Test _getAlternateFullName()
	 */
	public function testGetAlternateFullName(): void {
		$this->assertEquals('Buford Tannen', $this->person->alternate_full_name);
	}

	/**
	 * Test _getEmail()
	 */
	public function testGetEmail(): void {
		$this->assertEquals('amy@zuluru.org', $this->person->email);

	}

	/**
	 * Test _getEmailFormatted()
	 */
	public function testGetEmailFormatted(): void {
		$this->assertEquals('"Amy Administrator" <amy@zuluru.org>', $this->person->email_formatted);

	}

	/**
	 * Test _getAlternateEmailFormatted()
	 */
	public function testGetAlternateEmailFormatted(): void {
		$this->assertEquals('"Amy Administrator (alternate)" <Buford.Tannen@HillValley.com>', $this->person->alternate_email_formatted);
	}

	/**
	 * Test merge method for two users
	 */
	public function testMergeUserWithUser(): void {
		$this->loadFixtures();

		[$user1, $user2] = PersonFactory::makePlayer([
			['gender' => 'Woman'],
			['gender' => 'Man'],
		])->persist();
		$user_id = $user1->user_id;

		$user1->merge($user2);
		$this->assertEquals($user2->first_name, $user1->first_name);
		$this->assertEquals($user2->gender, $user1->gender);
		$this->assertEquals($user2->addr_street, $user1->addr_street);
		$this->assertEquals($user_id, $user1->user_id);
	}

	/**
	 * Test merge method for a user and a profile
	 */
	public function testMergeUserWithProfile(): void {
		$this->loadFixtures();

		$user1 = PersonFactory::makePlayer(['gender' => 'Woman'])->persist();
		$user2 = PersonFactory::make(['gender' => 'Man'])->withGroup(GROUP_PLAYER)->persist();
		$user_id = $user1->user_id;
		$address = $user1->addr_street;

		$user1->merge($user2);
		$this->assertEquals($user2->first_name, $user1->first_name);
		$this->assertEquals($user2->gender, $user1->gender);
		$this->assertEquals($address, $user1->addr_street);
		$this->assertEquals($user_id, $user1->user_id);
	}

	/**
	 * Test merge method for a profile and a user
	 */
	public function testMergeProfileWithUser(): void {
		$this->loadFixtures();

		$user1 = PersonFactory::make(['gender' => 'Man'])->withGroup(GROUP_PLAYER)->persist();
		$user2 = PersonFactory::makePlayer(['gender' => 'Woman'])->persist();
		$user_id = $user2->user_id;

		$user1->merge($user2);
		$this->assertEquals($user2->first_name, $user1->first_name);
		$this->assertEquals($user2->gender, $user1->gender);
		$this->assertEquals($user2->addr_street, $user1->addr_street);
		$this->assertEquals($user_id, $user1->user_id);
	}

	/**
	 * Test merge method for two profiles
	 */
	public function testMergeProfileWithProfile(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
