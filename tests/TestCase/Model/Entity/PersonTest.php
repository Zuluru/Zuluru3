<?php
namespace TestCase\Model\Entity;

use App\Model\Entity\Person;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Security;

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
		'app.affiliates',
			'app.users',
				'app.people',
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
		$this->assertEquals(Security::hash('amypassword'), $this->Person1->password);

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
	 * Test merge method
	 */
	public function testMerge() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
