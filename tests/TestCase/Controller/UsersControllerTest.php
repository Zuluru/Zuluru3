<?php
namespace App\Test\TestCase\Controller;

use Cake\Auth\DefaultPasswordHasher;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\UsersController Test Case
 */
class UsersControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
					'app.skills',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
			'app.settings',
	];

	/**
	 * Test login method
	 *
	 * @return void
	 */
	public function testLogin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test logout method
	 *
	 * @return void
	 */
	public function testLogout() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test create_account method as an admin
	 *
	 * @return void
	 */
	public function testCreateAccountAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test create_account method as a manager
	 *
	 * @return void
	 */
	public function testCreateAccountAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test create_account method as a coordinator
	 *
	 * @return void
	 */
	public function testCreateAccountAsCoordinator() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			PERSON_ID_COORDINATOR, 'get', [], null, 'You are already logged in!', 'Flash.flash.0.message');
	}

	/**
	 * Test create_account method as a captain
	 *
	 * @return void
	 */
	public function testCreateAccountAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			PERSON_ID_CAPTAIN, 'get', [], null, 'You are already logged in!', 'Flash.flash.0.message');
	}

	/**
	 * Test create_account method as a player
	 *
	 * @return void
	 */
	public function testCreateAccountAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			PERSON_ID_PLAYER, 'get', [], null, 'You are already logged in!', 'Flash.flash.0.message');
	}

	/**
	 * Test create_account method as someone else
	 *
	 * @return void
	 */
	public function testCreateAccountAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			PERSON_ID_VISITOR, 'get', [], null, 'You are already logged in!', 'Flash.flash.0.message');
	}

	/**
	 * Test create_account method without being logged in
	 *
	 * @return void
	 */
	public function testCreateAccountAsAnonymous() {
		$this->assertAccessOk(['controller' => 'Users', 'action' => 'create_account']);
		$this->assertResponseRegExp('#You will be participating as a player#');
		$this->assertResponseRegExp('#You have one or more children who will be participating as players#');
		$this->assertResponseRegExp('#You will be coaching a team that you are not a player on#');
		$this->assertResponseRegExp('#You plan to volunteer to help organize or run things#');
		$this->assertResponseNotRegExp('#You will be acting as an in-game official#');
		$this->assertResponseNotRegExp('#You are an organizational manager with some admin privileges#');
		$this->assertResponseNotRegExp('#You are an organizational administrator with absolute privileges#');
	}

	/**
	 * Test antispam features of account creation
	 *
	 * @return void
	 */
	public function testCreateAccountAntiSpam() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test creating a player account
	 *
	 * @return void
	 */
	public function testCreateAccountForPlayer() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			null, 'post', [
				'user_name' => 'test',
				'email' => 'test@example.com',
				'new_password' => 'password',
				'confirm_password' => 'password',
				'timestamp' => FrozenTime::now()->subMinute()->toUnixString(),
				'person' => [
					'groups' => ['_ids' => [GROUP_ID_PLAYER]],
					'affiliates' => [['id' => AFFILIATE_ID_CLUB]],
					'first_name' => 'Test',
					'last_name' => 'Test',
					'publish_email' => true,
					'home_phone' => '4162345678',
					'publish_home_phone' => true,
					'work_phone' => '',
					'publish_work_phone' => false,
					'work_ext' => '',
					'mobile_phone' => '',
					'publish_mobile_phone' => false,
					'addr_street' => '123 Main St.',
					'addr_city' => 'Toronto',
					'addr_prov' => 'Ontario',
					'addr_country' => 'Canada',
					'addr_postalcode' => 'M1A1A1',
					'gender' => 'Woman',
					'gender_description' => null,
					'roster_designation' => 'Woman',
					'birthdate' => ['year' => FrozenDate::now()->year - 30, 'month' => '01', 'day' => '01'],
					'height' => 70,
					'shirt_size' => 'Mens Large',
					'skills' => [
						[
							'enabled' => false,
							'sport' => 'baseball',
						],
						[
							'enabled' => true,
							'sport' => 'ultimate',
							'year_started' => [
								'year' => FrozenDate::now()->year - 5
							],
							'skill_level' => 5,
						],
					],
				],
				'action' => 'create',
			],
			'/', 'Flash/account_created', 'Flash.flash.0.element'
		);
		$this->assertEquals(USER_ID_NEW, $this->_requestSession->read('Auth.User.id'));

		$user = TableRegistry::get('Users')->get(USER_ID_NEW, ['contain' => [
			'People' => [
				'Affiliates',
				'Groups',
				'Skills',
			],
		]]);
		$this->assertTrue((new DefaultPasswordHasher)->check('password', $user->password));
		$this->assertEquals('test', $user->user_name);
		$this->assertEquals('test@example.com', $user->email);
		$this->assertNotNull($user->person);
		$this->assertEquals(PERSON_ID_NEW, $user->person->id);
		$this->assertEquals('Test', $user->person->first_name);
		$this->assertEquals('new', $user->person->status);
		$this->assertEquals(true, $user->person->complete);
		$this->assertEquals(FrozenDate::now(), $user->person->modified);
		$this->assertEquals(1, count($user->person->affiliates));
		$this->assertEquals(AFFILIATE_ID_CLUB, $user->person->affiliates[0]->id);
		$this->assertEquals(1, count($user->person->groups));
		$this->assertEquals(GROUP_ID_PLAYER, $user->person->groups[0]->id);
		$this->assertEquals(2, count($user->person->skills));
		$this->assertEquals('baseball', $user->person->skills[0]->sport);
		$this->assertFalse($user->person->skills[0]->enabled);
		$this->assertEquals('ultimate', $user->person->skills[1]->sport);
		$this->assertTrue($user->person->skills[1]->enabled);
		$this->assertEquals(FrozenDate::now()->year - 5, $user->person->skills[1]->year_started);
		$this->assertEquals(5, $user->person->skills[1]->skill_level);
	}

	/**
	 * Test creating a parent account
	 *
	 * @return void
	 */
	public function testCreateAccountForParent() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			null, 'post', [
				'user_name' => 'test',
				'email' => 'test@example.com',
				'new_password' => 'password',
				'confirm_password' => 'password',
				'timestamp' => FrozenTime::now()->subMinute()->toUnixString(),
				'person' => [
					'groups' => ['_ids' => [GROUP_ID_PARENT]],
					'affiliates' => [['id' => AFFILIATE_ID_CLUB]],
					'first_name' => 'Test',
					'last_name' => 'Test',
					'publish_email' => true,
					'home_phone' => '4162345678',
					'publish_home_phone' => true,
					'work_phone' => '',
					'publish_work_phone' => false,
					'work_ext' => '',
					'mobile_phone' => '',
					'publish_mobile_phone' => false,
					'addr_street' => '123 Main St.',
					'addr_city' => 'Toronto',
					'addr_prov' => 'Ontario',
					'addr_country' => 'Canada',
					'addr_postalcode' => 'M1A1A1',
					'relatives' => [
						[
							'first_name' => 'Young',
							'last_name' => 'Test',
							'gender' => 'Woman',
							'gender_description' => null,
							'roster_designation' => 'Woman',
							'birthdate' => ['year' => FrozenDate::now()->year - 10, 'month' => '01', 'day' => '01'],
							'height' => 50,
							'shirt_size' => 'Youth Large',
							'skills' => [
								[
									'enabled' => false,
									'sport' => 'baseball',
								],
								[
									'enabled' => true,
									'sport' => 'ultimate',
									'year_started' => [
										'year' => FrozenDate::now()->year - 1
									],
									'skill_level' => 3,
								],
							],
						],
					],
				],
				'action' => 'create',
			],
			'/', 'Flash/account_created', 'Flash.flash.0.element'
		);
		$this->assertEquals(USER_ID_NEW, $this->_requestSession->read('Auth.User.id'));

		$user = TableRegistry::get('Users')->get(USER_ID_NEW, ['contain' => [
			'People' => [
				'Affiliates',
				'Groups',
				'Skills',
				'Relatives' => [
					'Affiliates',
					'Groups',
					'Skills',
				],
			],
		]]);
		$this->assertTrue((new DefaultPasswordHasher)->check('password', $user->password));
		$this->assertEquals('test', $user->user_name);
		$this->assertEquals('test@example.com', $user->email);
		$this->assertNotNull($user->person);
		$this->assertEquals(PERSON_ID_NEW, $user->person->id);
		$this->assertEquals('Test', $user->person->first_name);
		$this->assertEquals('new', $user->person->status);
		$this->assertEquals(true, $user->person->complete);
		$this->assertEquals(FrozenDate::now(), $user->person->modified);
		$this->assertEquals(1, count($user->person->affiliates));
		$this->assertEquals(AFFILIATE_ID_CLUB, $user->person->affiliates[0]->id);
		$this->assertEquals(1, count($user->person->groups));
		$this->assertEquals(GROUP_ID_PARENT, $user->person->groups[0]->id);
		$this->assertEmpty(count($user->person->skills));

		$this->assertEquals(1, count($user->person->relatives));
		$this->assertEquals(PERSON_ID_NEW + 1, $user->person->relatives[0]->id);
		$this->assertTrue($user->person->relatives[0]->_joinData->approved);
		$this->assertEquals('Young', $user->person->relatives[0]->first_name);
		$this->assertEquals('new', $user->person->relatives[0]->status);
		$this->assertEquals(true, $user->person->relatives[0]->complete);
		$this->assertEquals(FrozenDate::now(), $user->person->relatives[0]->modified);
		$this->assertEquals(1, count($user->person->relatives[0]->affiliates));
		$this->assertEquals(AFFILIATE_ID_CLUB, $user->person->relatives[0]->affiliates[0]->id);
		$this->assertEquals(1, count($user->person->relatives[0]->groups));
		$this->assertEquals(GROUP_ID_PLAYER, $user->person->relatives[0]->groups[0]->id);
		$this->assertEquals(2, count($user->person->relatives[0]->skills));
		$this->assertEquals('baseball', $user->person->relatives[0]->skills[0]->sport);
		$this->assertFalse($user->person->relatives[0]->skills[0]->enabled);
		$this->assertEquals('ultimate', $user->person->relatives[0]->skills[1]->sport);
		$this->assertTrue($user->person->relatives[0]->skills[1]->enabled);
		$this->assertEquals(FrozenDate::now()->year - 1, $user->person->relatives[0]->skills[1]->year_started);
		$this->assertEquals(3, $user->person->relatives[0]->skills[1]->skill_level);
	}

	/**
	 * Test creating a parent account, with continuation to add another child
	 *
	 * @return void
	 */
	public function testCreateAccountForParentWithSecondChild() {
		$this->enableCsrfToken();
		$this->enableSecurityToken();

		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			null, 'post', [
				'user_name' => 'test',
				'email' => 'test@example.com',
				'new_password' => 'password',
				'confirm_password' => 'password',
				'timestamp' => FrozenTime::now()->subMinute()->toUnixString(),
				'person' => [
					'groups' => ['_ids' => [GROUP_ID_PARENT]],
					'affiliates' => [['id' => AFFILIATE_ID_CLUB]],
					'first_name' => 'Test',
					'last_name' => 'Test',
					'publish_email' => true,
					'home_phone' => '4162345678',
					'publish_home_phone' => true,
					'work_phone' => '',
					'publish_work_phone' => false,
					'work_ext' => '',
					'mobile_phone' => '',
					'publish_mobile_phone' => false,
					'addr_street' => '123 Main St.',
					'addr_city' => 'Toronto',
					'addr_prov' => 'Ontario',
					'addr_country' => 'Canada',
					'addr_postalcode' => 'M1A1A1',
					'relatives' => [
						[
							'first_name' => 'Young',
							'last_name' => 'Test',
							'gender' => 'Woman',
							'gender_description' => null,
							'roster_designation' => 'Woman',
							'birthdate' => ['year' => FrozenDate::now()->year - 10, 'month' => '01', 'day' => '01'],
							'height' => 50,
							'shirt_size' => 'Youth Large',
							'skills' => [
								[
									'enabled' => false,
									'sport' => 'baseball',
								],
								[
									'enabled' => true,
									'sport' => 'ultimate',
									'year_started' => [
										'year' => FrozenDate::now()->year - 1
									],
									'skill_level' => 3,
								],
							],
						],
					],
				],
				'action' => 'continue',
			],
			['controller' => 'People', 'action' => 'add_relative'], 'Flash/account_created', 'Flash.flash.0.element'
		);
		$this->assertEquals(USER_ID_NEW, $this->_requestSession->read('Auth.User.id'));
	}

	/**
	 * Test import method as an admin
	 *
	 * @return void
	 */
	public function testImportAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test import method as a manager
	 *
	 * @return void
	 */
	public function testImportAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test import method as a coordinator
	 *
	 * @return void
	 */
	public function testImportAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test import method as a captain
	 *
	 * @return void
	 */
	public function testImportAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test import method as a player
	 *
	 * @return void
	 */
	public function testImportAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test import method as someone else
	 *
	 * @return void
	 */
	public function testImportAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test import method without being logged in
	 *
	 * @return void
	 */
	public function testImportAsAnonymous() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as an admin
	 *
	 * @return void
	 */
	public function testChangePasswordAsAdmin() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as a manager
	 *
	 * @return void
	 */
	public function testChangePasswordAsManager() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as a coordinator
	 *
	 * @return void
	 */
	public function testChangePasswordAsCoordinator() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as a captain
	 *
	 * @return void
	 */
	public function testChangePasswordAsCaptain() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as a player
	 *
	 * @return void
	 */
	public function testChangePasswordAsPlayer() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as someone else
	 *
	 * @return void
	 */
	public function testChangePasswordAsVisitor() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method without being logged in
	 *
	 * @return void
	 */
	public function testChangePasswordAsAnonymous() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'change_password']);
	}

	/**
	 * Test reset_password method as an admin
	 *
	 * @return void
	 */
	public function testResetPasswordAsAdmin() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_ADMIN, 'get', [], ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.', 'Flash.flash.0.message');
	}

	/**
	 * Test reset_password method as a manager
	 *
	 * @return void
	 */
	public function testResetPasswordAsManager() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_MANAGER, 'get', [], ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.', 'Flash.flash.0.message');
	}

	/**
	 * Test reset_password method as a coordinator
	 *
	 * @return void
	 */
	public function testResetPasswordAsCoordinator() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_COORDINATOR, 'get', [], ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.', 'Flash.flash.0.message');
	}

	/**
	 * Test reset_password method as a captain
	 *
	 * @return void
	 */
	public function testResetPasswordAsCaptain() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_CAPTAIN, 'get', [], ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.', 'Flash.flash.0.message');
	}

	/**
	 * Test reset_password method as a player
	 *
	 * @return void
	 */
	public function testResetPasswordAsPlayer() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_PLAYER, 'get', [], ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.', 'Flash.flash.0.message');
	}

	/**
	 * Test reset_password method as someone else
	 *
	 * @return void
	 */
	public function testResetPasswordAsVisitor() {
		$this->assertAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_VISITOR, 'get', [], ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.', 'Flash.flash.0.message');
	}

	/**
	 * Test reset_password method without being logged in
	 *
	 * @return void
	 */
	public function testResetPasswordAsAnonymous() {
		$this->assertAccessOk(['controller' => 'Users', 'action' => 'reset_password']);
		$this->markTestIncomplete('Not implemented yet.');
	}

}
