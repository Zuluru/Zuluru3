<?php
namespace App\Test\TestCase\Controller;

use App\Model\Entity\User;
use App\Test\Factory\AffiliateFactory;
use App\Test\Factory\PersonFactory;
use App\Test\Scenario\DiverseUsersScenario;
use Cake\Auth\DefaultPasswordHasher;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use Firebase\JWT\JWT;

/**
 * App\Controller\UsersController Test Case
 */
class UsersControllerTest extends ControllerTestCase {

	use ScenarioAwareTrait;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Countries',
		'app.Groups',
		'app.Provinces',
		'app.Settings',
	];

	/**
	 * Test login method
	 */
	public function testLogin(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test logout method
	 */
	public function testLogout(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test create_account method as an admin
	 */
	public function testCreateAccountAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to create account
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'create_account'], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test create_account method as a manager
	 */
	public function testCreateAccountAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to create account
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'create_account'], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test create_account method as a coordinator
	 */
	public function testCreateAccountAsCoordinator(): void {
		[$volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer']);

		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			$volunteer->id, '/',
			'You are already logged in!');
	}

	/**
	 * Test create_account method as a player
	 */
	public function testCreateAccountAsPlayer(): void {
		[$player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['player']);

		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			$player->id, '/',
			'You are already logged in!');
	}

	/**
	 * Test create_account method without being logged in
	 */
	public function testCreateAccountAsAnonymous(): void {
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
	 */
	public function testCreateAccountAntiSpam(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test creating a player account
	 */
	public function testCreateAccountForPlayer(): void {
		$this->enableSecurityToken();

		$affiliate = AffiliateFactory::make()->persist();

		$this->assertPostAnonymousAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			[
				'user_name' => 'test',
				'email' => 'test@example.com',
				'new_password' => 'password',
				'confirm_password' => 'password',
				'timestamp' => FrozenTime::now()->subMinutes(1)->toUnixString(),
				'person' => [
					'groups' => ['_ids' => [GROUP_PLAYER]],
					'affiliates' => [['id' => $affiliate->id]],
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
					'pronouns' => 'She, Her, Hers',
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
			'/'
		);
		$this->assertFlashElement('flash/account_created');
		$this->assertSession(1, 'Auth.id');

		/** @var User $user */
		$user = TableRegistry::getTableLocator()->get('Users')->get(1, ['contain' => [
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
		$this->assertEquals('Test', $user->person->first_name);
		$this->assertEquals('new', $user->person->status);
		$this->assertEquals(true, $user->person->complete);
		$this->assertEquals(FrozenDate::now(), $user->person->modified);
		$this->assertCount(1, $user->person->affiliates);
		$this->assertEquals($affiliate->id, $user->person->affiliates[0]->id);
		$this->assertCount(1, $user->person->groups);
		$this->assertEquals(GROUP_PLAYER, $user->person->groups[0]->id);
		$this->assertCount(2, $user->person->skills);
		$this->assertEquals('baseball', $user->person->skills[0]->sport);
		$this->assertFalse($user->person->skills[0]->enabled);
		$this->assertEquals('ultimate', $user->person->skills[1]->sport);
		$this->assertTrue($user->person->skills[1]->enabled);
		$this->assertEquals(FrozenDate::now()->year - 5, $user->person->skills[1]->year_started);
		$this->assertEquals(5, $user->person->skills[1]->skill_level);
	}

	/**
	 * Test creating a parent account
	 */
	public function testCreateAccountForParent(): void {
		$this->enableSecurityToken();

		$affiliate = AffiliateFactory::make()->persist();

		$this->assertPostAnonymousAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			[
				'user_name' => 'test',
				'email' => 'test@example.com',
				'new_password' => 'password',
				'confirm_password' => 'password',
				'timestamp' => FrozenTime::now()->subMinutes(1)->toUnixString(),
				'person' => [
					'groups' => ['_ids' => [GROUP_PARENT]],
					'affiliates' => [['id' => $affiliate->id]],
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
							'pronouns' => 'She, Her, Hers',
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
			'/'
		);
		$this->assertFlashElement('flash/account_created');
		$this->assertSession(1, 'Auth.id');

		/** @var User $user */
		$user = TableRegistry::getTableLocator()->get('Users')->get(1, ['contain' => [
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
		$this->assertEquals('Test', $user->person->first_name);
		$this->assertEquals('new', $user->person->status);
		$this->assertEquals(true, $user->person->complete);
		$this->assertEquals(FrozenDate::now(), $user->person->modified);
		$this->assertEquals(1, count($user->person->affiliates));
		$this->assertEquals($affiliate->id, $user->person->affiliates[0]->id);
		$this->assertEquals(1, count($user->person->groups));
		$this->assertEquals(GROUP_PARENT, $user->person->groups[0]->id);
		$this->assertEmpty(count($user->person->skills));

		$this->assertEquals(1, count($user->person->relatives));
		$this->assertEquals($user->person->id + 1, $user->person->relatives[0]->id);
		$this->assertTrue($user->person->relatives[0]->_joinData->approved);
		$this->assertEquals('Young', $user->person->relatives[0]->first_name);
		$this->assertEquals('new', $user->person->relatives[0]->status);
		$this->assertEquals(true, $user->person->relatives[0]->complete);
		$this->assertEquals(FrozenDate::now(), $user->person->relatives[0]->modified);
		$this->assertEquals(1, count($user->person->relatives[0]->affiliates));
		$this->assertEquals($affiliate->id, $user->person->relatives[0]->affiliates[0]->id);
		$this->assertEquals(1, count($user->person->relatives[0]->groups));
		$this->assertEquals(GROUP_PLAYER, $user->person->relatives[0]->groups[0]->id);
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
	 */
	public function testCreateAccountForParentWithSecondChild(): void {
		$this->enableSecurityToken();

		$affiliate = AffiliateFactory::make()->persist();

		$this->assertPostAnonymousAccessRedirect(['controller' => 'Users', 'action' => 'create_account'],
			[
				'user_name' => 'test',
				'email' => 'test@example.com',
				'new_password' => 'password',
				'confirm_password' => 'password',
				'timestamp' => FrozenTime::now()->subMinutes(1)->toUnixString(),
				'person' => [
					'groups' => ['_ids' => [GROUP_PARENT]],
					'affiliates' => [['id' => $affiliate->id]],
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
							'pronouns' => 'She, Her, Hers',
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
			['controller' => 'People', 'action' => 'add_relative']
		);
		$this->assertFlashElement('flash/account_created');
		$this->assertSession(1, 'Auth.id');
	}

	/**
	 * Test import method as an admin
	 */
	public function testImportAsAdmin(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Admins are allowed to import
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'import'], $admin->id);
	}

	/**
	 * Test import method as a manager
	 */
	public function testImportAsManager(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Managers are allowed to import
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'import'], $manager->id);
	}

	/**
	 * Test import method as others
	 */
	public function testImportAsOthers(): void {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Others are not allowed to import
		$this->assertGetAsAccessDenied(['controller' => 'Users', 'action' => 'import'], $volunteer->id);
		$this->assertGetAsAccessDenied(['controller' => 'Users', 'action' => 'import'], $player->id);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Users', 'action' => 'import']);
	}

	/**
	 * Test JSON API token generation
	 */
	public function testToken(): void {

		$affiliate = AffiliateFactory::make()->persist();
		$admin = PersonFactory::make()
			->withGroup(GROUP_ADMIN)
			->with('Users', ['password' => 'tetspassword'])
			->with('Affiliates', [$affiliate])->persist();

		// Lock the time so that the token has a reliable value.
		// We have to use a time around now, because the underlying JWT library
		// uses time(), not Cake's special classes.
		FrozenTime::setTestNow(new FrozenTime(time()));

		$this->configRequest(['headers' => ['CONTENT_TYPE' => 'application/json', 'ACCEPT' => 'application/json']]);
		$this->assertPostAnonymousAccessOk(['controller' => 'Users', 'action' => 'token', '_ext' => 'json'],
			json_encode(['user_name' => $admin->user->user_name, 'password' => 'tetspassword'])
		);
		$this->assertJson((string)$this->_response->getBody());
		$response = json_decode((string)$this->_response->getBody(), true);
		$this->assertArrayHasKey('success', $response);
		$this->assertTrue($response['success']);
		$this->assertArrayHasKey('data', $response);
		$this->assertArrayHasKey('token', $response['data']);
		$token_data = JWT::decode($response['data']['token'], \Cake\Utility\Security::getSalt(), ['HS256']);
		$this->assertObjectHasAttribute('sub', $token_data);
		$this->assertEquals($admin->user_id, $token_data->sub);
		$this->assertObjectHasAttribute('exp', $token_data);
		$this->assertEquals(FrozenTime::now()->addWeeks(1)->toUnixString(), $token_data->exp);
	}

	/**
	 * Test change_password method as an admin
	 */
	public function testChangePasswordAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		// Admins are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], $admin->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test change_password method as a manager
	 */
	public function testChangePasswordAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		// Managers are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], $manager->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test change_password method as a coordinator
	 */
	public function testChangePasswordAsCoordinator(): void {
		[$volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer']);

		// Coordinators are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], $volunteer->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test change_password method as a player
	 */
	public function testChangePasswordAsPlayer(): void {
		[$player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['player']);

		// Players are allowed to change password
		$this->assertGetAsAccessOk(['controller' => 'Users', 'action' => 'change_password'], $player->id);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test change_password method without being logged in
	 */
	public function testChangePasswordAsAnonymous(): void {
		$this->assertGetAnonymousAccessDenied(['controller' => 'Users', 'action' => 'change_password']);
	}

	/**
	 * Test reset_password method as an admin
	 */
	public function testResetPasswordAsAdmin(): void {
		[$admin] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['admin']);

		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			$admin->id, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method as a manager
	 */
	public function testResetPasswordAsManager(): void {
		[$manager] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['manager']);

		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			$manager->id, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method as a coordinator
	 */
	public function testResetPasswordAsCoordinator(): void {
		[$volunteer] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['volunteer']);

		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			$volunteer->id, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method as a player
	 */
	public function testResetPasswordAsPlayer(): void {
		[$player] = $this->loadFixtureScenario(DiverseUsersScenario::class, ['player']);

		$this->assertGetAsAccessRedirect(['controller' => 'Users', 'action' => 'reset_password'],
			$player->id, ['controller' => 'Users', 'action' => 'change_password'],
			'You are already logged in. Use the change password form instead.');
	}

	/**
	 * Test reset_password method without being logged in
	 */
	public function testResetPasswordAsAnonymous(): void {
		$this->assertGetAnonymousAccessOk(['controller' => 'Users', 'action' => 'reset_password']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

}
