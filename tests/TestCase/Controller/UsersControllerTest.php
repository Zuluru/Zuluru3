<?php
namespace App\Test\TestCase\Controller;

use Cake\Auth\DefaultPasswordHasher;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Firebase\JWT\JWT;

/**
 * App\Controller\UsersController Test Case
 */
class UsersControllerTest extends ControllerTestCase {

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
		// Admins are allowed to create account
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'create_account'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test create_account method as a manager
	 *
	 * @return void
	 */
	public function testCreateAccountAsManager() {
		// Managers are allowed to create account
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'create_account'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test create_account method as a coordinator
	 *
	 * @return void
	 */
	public function testCreateAccountAsCoordinator() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			PERSON_ID_COORDINATOR, '/',
			'You are already logged in!');
	}

	/**
	 * Test create_account method as a captain
	 *
	 * @return void
	 */
	public function testCreateAccountAsCaptain() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			PERSON_ID_CAPTAIN, '/',
			'You are already logged in!');
	}

	/**
	 * Test create_account method as a player
	 *
	 * @return void
	 */
	public function testCreateAccountAsPlayer() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			PERSON_ID_PLAYER, '/',
			'You are already logged in!');
	}

	/**
	 * Test create_account method as someone else
	 *
	 * @return void
	 */
	public function testCreateAccountAsVisitor() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			PERSON_ID_VISITOR, '/',
			'You are already logged in!');
	}

	/**
	 * Test create_account method without being logged in
	 *
	 * @return void
	 */
	public function testCreateAccountAsAnonymous() {
		$this->assertGetAnonymousAccessOk(['controller' => 'Users', 'action' => 'create_account']);
		$this->assertResponseContains('You will be participating as a player');
		$this->assertResponseContains('You have one or more children who will be participating as players');
		$this->assertResponseContains('You will be coaching a team that you are not a player on');
		$this->assertResponseContains('You plan to volunteer to help organize or run things');
		$this->assertResponseNotContains('You will be acting as an in-game official');
		$this->assertResponseNotContains('You are an organizational manager with some admin privileges');
		$this->assertResponseNotContains('You are an organizational administrator with absolute privileges');
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

		$this->assertPostAnonymousAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			[
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
		$this->assertEquals(USER_ID_NEW, $this->_requestSession->read('Auth.id'));

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

		$this->assertPostAnonymousAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			[
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
		$this->assertEquals(USER_ID_NEW, $this->_requestSession->read('Auth.id'));

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

		$this->assertPostAnonymousAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			[
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
		$this->assertEquals(USER_ID_NEW, $this->_requestSession->read('Auth.id'));
	}

	/**
	 * Test import method as an admin
	 *
	 * @return void
	 */
	public function testImportAsAdmin() {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Admins are allowed to import
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'import'], PERSON_ID_ADMIN);
	}

	/**
	 * Test import method as a manager
	 *
	 * @return void
	 */
	public function testImportAsManager() {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Managers are allowed to import
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'import'], PERSON_ID_MANAGER);
	}

	/**
	 * Test import method as others
	 *
	 * @return void
	 */
	public function testImportAsOthers() {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Others are not allowed to import
		$this->assertGetAsAccessDenied(['controller' => 'Users', 'action' => 'import'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Users', 'action' => 'import'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Users', 'action' => 'import'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Users', 'action' => 'import'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Users', 'action' => 'import']);
	}

	/**
	 * Test JSON API token generation
	 *
	 * @return void
	 */
	public function testToken() {
		// Lock the time so that the token has a reliable value.
		// We have to use a time around now, because the underlying JWT library
		// uses time(), not Cake's special classes.
		FrozenTime::setTestNow(new FrozenTime(time()));

		$this->configRequest(['headers' => ['CONTENT_TYPE' => 'application/json', 'ACCEPT' => 'application/json']]);
		$this->assertPostAnonymousAccessOk(['controller' => 'Users', 'action' => 'token', '_ext' => 'json'],
			json_encode(['user_name' => 'amy', 'password' => 'amypassword'])
		);
		$this->assertJson((string)$this->_response->getBody());
		$response = json_decode((string)$this->_response->getBody(), true);
		$this->assertArrayHasKey('success', $response);
		$this->assertTrue($response['success']);
		$this->assertArrayHasKey('data', $response);
		$this->assertArrayHasKey('token', $response['data']);
		$token_data = JWT::decode($response['data']['token'], \Cake\Utility\Security::getSalt(), ['HS256']);
		$this->assertObjectHasAttribute('sub', $token_data);
		$this->assertEquals(USER_ID_ADMIN, $token_data->sub);
		$this->assertObjectHasAttribute('exp', $token_data);
		$this->assertEquals(FrozenTime::now()->addWeek()->toUnixString(), $token_data->exp);
	}

	/**
	 * Test change_password method as an admin
	 *
	 * @return void
	 */
	public function testChangePasswordAsAdmin() {
		// Admins are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as a manager
	 *
	 * @return void
	 */
	public function testChangePasswordAsManager() {
		// Managers are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as a coordinator
	 *
	 * @return void
	 */
	public function testChangePasswordAsCoordinator() {
		// Coordinators are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as a captain
	 *
	 * @return void
	 */
	public function testChangePasswordAsCaptain() {
		// Captains are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as a player
	 *
	 * @return void
	 */
	public function testChangePasswordAsPlayer() {
		// Players are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method as someone else
	 *
	 * @return void
	 */
	public function testChangePasswordAsVisitor() {
		// Visitors are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test change_password method without being logged in
	 *
	 * @return void
	 */
	public function testChangePasswordAsAnonymous() {
		$this->assertGetAnonymousAccessDenied(['controller' => 'Users', 'action' => 'change_password']);
	}

	/**
	 * Test reset_password method as an admin
	 *
	 * @return void
	 */
	public function testResetPasswordAsAdmin() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_ADMIN, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method as a manager
	 *
	 * @return void
	 */
	public function testResetPasswordAsManager() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_MANAGER, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method as a coordinator
	 *
	 * @return void
	 */
	public function testResetPasswordAsCoordinator() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_COORDINATOR, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method as a captain
	 *
	 * @return void
	 */
	public function testResetPasswordAsCaptain() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_CAPTAIN, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method as a player
	 *
	 * @return void
	 */
	public function testResetPasswordAsPlayer() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_PLAYER, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method as someone else
	 *
	 * @return void
	 */
	public function testResetPasswordAsVisitor() {
		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			PERSON_ID_VISITOR, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method without being logged in
	 *
	 * @return void
	 */
	public function testResetPasswordAsAnonymous() {
		$this->assertGetAnonymousAccessOk(['controller' => 'Users', 'action' => 'reset_password']);
		$this->markTestIncomplete('Not implemented yet.');
	}

}
