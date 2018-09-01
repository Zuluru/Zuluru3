<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;
use Cake\Utility\Security;

/**
 * UsersFixture
 *
 */
class UsersFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'users'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				// id = 1
				'user_name' => 'amy',
				'password' => Security::hash('amypassword'),
				'email' => 'amy@zuluru.org',
				// TODO: Set the time for all tests instead of just some, then this can use FrozenTime instead of FrozenDate
				'last_login' => FrozenDate::now()->subDay(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 2
				'user_name' => 'mary',
				'password' => Security::hash('marypassword'),
				'email' => 'mary@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 3
				'user_name' => 'cindy',
				'password' => Security::hash('cindypassword'),
				'email' => 'cindy@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 4
				'user_name' => 'crystal',
				'password' => Security::hash('crystalpassword'),
				'email' => 'crystal@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 5
				'user_name' => 'chuck',
				'password' => Security::hash('chuckpassword'),
				'email' => 'chuck@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 6
				'user_name' => 'carolyn',
				'password' => Security::hash('carolynpassword'),
				'email' => 'carolyn@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 7
				'user_name' => 'carl',
				'password' => Security::hash('carlpassword'),
				'email' => 'carl@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 8
				'user_name' => 'pam',
				'password' => Security::hash('pampassword'),
				'email' => 'pam@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 9
				'user_name' => 'mary2',
				'password' => Security::hash('mary2password'),
				'email' => 'mary@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 10
				'user_name' => 'andy',
				'password' => Security::hash('andypassword'),
				'email' => 'andy@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 11
				'user_name' => 'veronica',
				'password' => Security::hash('veronicapassword'),
				'email' => 'veronica@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
		];

		if (!defined('USER_ID_ADMIN')) {
			$i = 0;
			define('USER_ID_ADMIN', ++$i);
			define('USER_ID_MANAGER', ++$i);
			define('USER_ID_COORDINATOR', ++$i);
			define('USER_ID_CAPTAIN', ++$i);
			define('USER_ID_CAPTAIN2', ++$i);
			define('USER_ID_CAPTAIN3', ++$i);
			define('USER_ID_CAPTAIN4', ++$i);
			define('USER_ID_PLAYER', ++$i);
			define('USER_ID_DUPLICATE', ++$i);
			define('USER_ID_ANDY_SUB', ++$i);
			define('USER_ID_VISITOR', ++$i);
			// This must always be the last one in the list: it is for new
			// records created in UsersControllerTest
			define('USER_ID_NEW', ++$i);
		}

		parent::init();
	}

}
