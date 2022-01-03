<?php
namespace TestCase\Model\Entity;

use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class PersonTest extends TestCase {

	/**
	 * The Entity we'll be using in the test
	 *
	 * @var \App\Model\Entity\Person
	 */
	public $Person1;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Affiliates',
			'app.Users',
				'app.People',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$people = TableRegistry::get('People');
		$this->Person1 = $people->get(PERSON_ID_ADMIN);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Person1);

		parent::tearDown();
	}

	/**
	 * Test _getUserName()
	 */
	public function testGetUserName() {
		$this->assertEquals('amy', $this->Person1->user_name);
	}

	/**
	 * Test _getPassword()
	 */
	public function testGetPassword() {
		$this->assertTrue(password_verify('amypassword', $this->Person1->password));
	}

	/**
	 * Test _getLastLogin()
	 */
	public function testLastLogin() {
		$this->assertEquals(new FrozenTime('yesterday'), $this->Person1->last_login);
	}

	/**
	 * Test _getClientIp()
	 */
	public function testGetClientIp() {
		$this->assertEquals('127.0.0.1', $this->Person1->client_ip);
	}

	/**
	 * Test _getFullName()
	 */
	public function testGetFullName() {
		$this->assertEquals('Amy Administrator', $this->Person1->full_name);
	}

	/**
	 * Test _getAlternateFullName()
	 */
	public function testGetAlternateFullName() {
		$this->assertEquals('Buford Tannen', $this->Person1->alternate_full_name);
	}

	/**
	 * Test _getEmail()
	 */
	public function testGetEmail() {
		$this->assertEquals('amy@zuluru.org', $this->Person1->email);

	}

	/**
	 * Test _getEmailFormatted()
	 */
	public function testGetEmailFormatted() {
		$this->assertEquals('"Amy Administrator" <amy@zuluru.org>', $this->Person1->email_formatted);

	}

	/**
	 * Test _getAlternateEmailFormatted()
	 */
	public function testGetAlternateEmailFormatted() {
		$this->assertEquals('"Amy Administrator (alternate)" <Buford.Tannen@HillValley.com>', $this->Person1->alternate_email_formatted);
	}

	/**
	 * Test merge method for two users
	 */
	public function testMergeUserWithUser() {
		$user1 = TableRegistry::getTableLocator()->get('People')->get(PERSON_ID_CAPTAIN);
		$user2 = TableRegistry::getTableLocator()->get('People')->get(PERSON_ID_CAPTAIN2);
		$user1->merge($user2);
		$this->assertEquals($user2->first_name, $user1->first_name);
		$this->assertEquals($user2->gender, $user1->gender);
		$this->assertEquals(USER_ID_CAPTAIN, $user1->user_id);
	}

	/**
	 * Test merge method for a user and a profile
	 */
	public function testMergeUserWithProfile() {
		$user1 = TableRegistry::getTableLocator()->get('People')->get(PERSON_ID_CAPTAIN);
		$user2 = TableRegistry::getTableLocator()->get('People')->get(PERSON_ID_CHILD);
		$user1->merge($user2);
		$this->assertEquals($user2->first_name, $user1->first_name);
		$this->assertEquals($user2->gender, $user1->gender);
		$this->assertEquals(USER_ID_CAPTAIN, $user1->user_id);
	}

	/**
	 * Test merge method for a profile and a user
	 */
	public function testMergeProfileWithUser() {
		$user1 = TableRegistry::getTableLocator()->get('People')->get(PERSON_ID_CHILD, [
			'contain' => ['Users']
		]);
		$user2 = TableRegistry::getTableLocator()->get('People')->get(PERSON_ID_CAPTAIN, [
			'contain' => ['Users']
		]);
		$user1->merge($user2);
		$this->assertEquals($user2->first_name, $user1->first_name);
		$this->assertEquals($user2->gender, $user1->gender);
		$this->assertEquals(USER_ID_CAPTAIN, $user1->user_id);
	}

	/**
	 * Test merge method for two profiles
	 */
	public function testMergeProfileWithProfile() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
