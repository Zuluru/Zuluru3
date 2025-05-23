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
		'app.UserGroups',
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
		$this->setupFixtures();

		[$user1, $user2] = PersonFactory::make([
			['gender' => 'Woman'],
			['gender' => 'Man'],
		])->player()->persist();
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
		$this->setupFixtures();

		$user1 = PersonFactory::make()->player(['gender' => 'Woman'])->persist();
		$user2 = PersonFactory::make()->child(['gender' => 'Man'])->persist();
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
		$this->setupFixtures();

		/** @var Person $user1 */
		$user1 = PersonFactory::make(['gender' => 'Man'])->withGroup(GROUP_PLAYER)->persist();
		/** @var Person $user2 */
		$user2 = PersonFactory::make(['gender' => 'Woman'])->player()->persist();
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
		$this->setupFixtures();

		/** @var Person $user1 */
		$user1 = PersonFactory::make(['gender' => 'Man'])->withGroup(GROUP_PLAYER)->persist();
		/** @var Person $user2 */
		$user2 = PersonFactory::make(['gender' => 'Woman'])->withGroup(GROUP_PLAYER)->persist();

		$user1->merge($user2);
		$this->assertEquals($user2->first_name, $user1->first_name);
		$this->assertEquals($user2->gender, $user1->gender);
		$this->assertEquals($user2->addr_street, $user1->addr_street);
	}

	/**
	 * Test merge method for a parent with a player
	 */
	public function testMergeParentWithPlayer(): void {
		$this->setupFixtures();

		/** @var Person $player */
		$player = PersonFactory::make()->player(['gender' => 'Man', 'roster_designation' => 'Open', 'height' => 70, 'shirt_size' => 'Mens Large'])->persist();
		/** @var Person $parent */
		$parent = PersonFactory::make()->parent()->persist();

		$player->merge($parent);
		$this->assertEquals($parent->first_name, $player->first_name);
		$this->assertEquals($parent->addr_street, $player->addr_street);
		$this->assertEquals('Man', $player->gender);
		$this->assertEquals(70, $player->height);
		$this->assertEquals('Mens Large', $player->shirt_size);
	}

}
