<?php
namespace App\Test\Fixture_deprecated;

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
				'password' => password_hash('amypassword', PASSWORD_DEFAULT),
				'email' => 'amy@zuluru.org',
				// TODO: Set the time for all tests instead of just some, then this can use FrozenTime instead of FrozenDate
				'last_login' => FrozenDate::now()->subDays(1),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 2
				'user_name' => 'mary',
				'password' => password_hash('marypassword', PASSWORD_DEFAULT),
				'email' => 'mary@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 3
				'user_name' => 'cindy',
				'password' => password_hash('cindypassword', PASSWORD_DEFAULT),
				'email' => 'cindy@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 4
				'user_name' => 'crystal',
				'password' => password_hash('crystalpassword', PASSWORD_DEFAULT),
				'email' => 'crystal@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 5
				'user_name' => 'chuck',
				'password' => password_hash('chuckpassword', PASSWORD_DEFAULT),
				'email' => 'chuck@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 6
				'user_name' => 'carolyn',
				'password' => password_hash('carolynpassword', PASSWORD_DEFAULT),
				'email' => 'carolyn@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 7
				'user_name' => 'carl',
				'password' => password_hash('carlpassword', PASSWORD_DEFAULT),
				'email' => 'carl@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 8
				'user_name' => 'pam',
				'password' => password_hash('pampassword', PASSWORD_DEFAULT),
				'email' => 'pam@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 9
				'user_name' => 'mary2',
				'password' => password_hash('mary2password', PASSWORD_DEFAULT),
				'email' => 'mary@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 10
				'user_name' => 'andy',
				'password' => password_hash('andypassword', PASSWORD_DEFAULT),
				'email' => 'andy@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 11
				'user_name' => 'veronica',
				'password' => password_hash('veronicapassword', PASSWORD_DEFAULT),
				'email' => 'veronica@zuluru.org',
				'last_login' => FrozenTime::now(),
				'client_ip' => '127.0.0.1',
			],
			[
				// id = 12
				'user_name' => 'irene',
				'password' => password_hash('irenepassword', PASSWORD_DEFAULT),
				'email' => 'irene@zuluru.org',
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
			define('USER_ID_INACTIVE', ++$i);
			// This must always be the last one in the list: it is for new
			// records created in UsersControllerTest
			define('USER_ID_NEW', ++$i);
		}

		parent::init();
	}

}
