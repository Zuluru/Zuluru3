<?php
namespace App\Test\TestCase\Controller;

use App\Core\UserCache;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\TestSuite\TestEmailTransport;

class ControllerTestCase extends TestCase {

	use IntegrationTestTrait;

	protected $_jsonOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		// Minimize menu building during most tests, to cut down on required fixtures and speed things along
		Configure::write('feature.minimal_menus', true);

		foreach (Configure::read('App.globalListeners') as $listener) {
			EventManager::instance()->on($listener);
		}
	}

	public function tearDown(): void {
		Cache::clear('long_term');
		FrozenTime::setTestNow();
		FrozenDate::setTestNow();
		parent::tearDown();
	}

	/**
	 * @param $personId int|int[]
	 * @return void
	 */
	protected function login($personId) {
		// Clear the request stack: they pile up when running multiple requests from a single test
		while (Router::popRequest()) {};

		if (is_array($personId)) {
			[$personId, $actAs] = $personId;
			$actAs = TableRegistry::getTableLocator()->get('People')->get($actAs);
		} else {
			$actAs = null;
		}

		$person = TableRegistry::getTableLocator()->get('People')->get($personId);
		if (!$person->user_id) {
			$this->fail('Cannot log in as a profile without a user record.');
		}

		$user_table = TableRegistry::getTableLocator()->get(Configure::read('Security.authModel', 'Users'));
		$user = $user_table->get($person->user_id);
		if ($actAs) {
			$user->person = $actAs;
			$user->real_person = $person;
		} else {
			$user->person = $person;
		}

		$this->session(['Auth' => $user]);
		UserCache::setIdentity(null);
	}

	protected function logout() {
		// Clear the request stack: they pile up when running multiple requests from a single test
		while (Router::popRequest()) {};

		// Clear session info, so that unauthenticated requests aren't mistakenly processed as the last logged-in user
		$this->_session = [];
		$this->_cookie = [];
		$this->_requestSession = null;
		UserCache::setIdentity(null);
	}

	/**
	 * Common helper to confirm that there is GET access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAsAccessOk($url, $user) {
		$this->login($user);
		$this->get($url);

		$this->assertResponseOk();
	}

	/**
	 * Common helper to confirm that there is AJAX GET access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAjaxAsAccessOk($url, $user) {
		$this->login($user);
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->get($url);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		$this->assertResponseOk();
		$this->assertResponseNotContains('"_redirect":{', 'Response contains a redirect');

		$this->logout();
	}

	/**
	 * Common helper to confirm that there is GET access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAnonymousAccessOk($url) {
		$this->logout();
		$this->get($url);
		$this->assertResponseOk();
	}

	/**
	 * Common helper to confirm that there is AJAX GET access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAjaxAnonymousAccessOk($url) {
		$this->logout();
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->get($url);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		$this->assertResponseOk();
		$this->assertResponseNotContains('"_redirect":{', 'Response contains a redirect');
	}

	/**
	 * Common helper to confirm that there is POST access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @param $data string|mixed[]
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAsAccessOk($url, $user, $data) {
		$this->login($user);
		$this->post($url, $data);

		$this->assertResponseOk();
	}

	/**
	 * Common helper to confirm that there is AJAX POST access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @param $data string|mixed[]
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAjaxAsAccessOk($url, $user, $data = []) {
		$this->login($user);
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->post($url, $data);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		$this->assertResponseOk();
		$this->assertResponseNotContains('"_redirect":{', 'Response contains a redirect');
	}

	/**
	 * Common helper to confirm that there is POST access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $data string|mixed[]
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAnonymousAccessOk($url, $data) {
		$this->logout();
		$this->post($url, $data);
		$this->assertResponseOk();
	}

	/**
	 * Common helper to confirm that there is AJAX POST access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $data string|mixed[]
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAjaxAnonymousAccessOk($url, $data) {
		$this->logout();
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->post($url, $data);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		$this->assertResponseOk();
		$this->assertResponseNotContains('"_redirect":{', 'Response contains a redirect');
	}

	/**
	 * Common helper to confirm that there is no GET access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @param $redirect string|array
	 * @param $message string|boolean
	 * @param $key string
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAsAccessRedirect($url, $user, $redirect, $message = false, $key = 'Flash.flash.0.message') {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->enableRetainFlashMessages();
		$this->login($user);
		$this->get($url);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals($redirect);

		if ($message) {
			if ($message[0] === '#') {
				$this->assertRegExp($message, $this->_requestSession->read($key));
			} else {
				$this->assertSession($message, $key);
			}
		} else {
			$this->assertSessionNotHasKey($key);
			$this->assertSession(null, $key);
		}
	}

	/**
	 * Common helper to confirm that there is no AJAX GET access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @param $redirect string|array
	 * @param $message string
	 * @param $class string
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAjaxAsAccessRedirect($url, $user, $redirect, $message = false, $class = 'info') {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->enableRetainFlashMessages();
		$this->login($user);
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->get($url);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		if ($message) {
			$message = [
				0 => [
					'message' => $message,
					'key' => 'flash',
					'element' => "flash/$class",
					'params' => [],
				],
			];
		} else {
			$message = null;
		}

		$error = [
			'error' => null,
			'content' => null,
			'_message' => $message,
			'_redirect' => [
				'url' => Router::url($redirect),
				'status' => 302,
			],
		];
		$this->assertResponseOk();
		$this->assertEquals(json_encode($error, $this->_jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no GET access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $redirect string|array
	 * @param $message string|boolean
	 * @param $key string
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAnonymousAccessRedirect($url, $redirect, $message = false, $key = 'Flash.flash.0.message') {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->enableRetainFlashMessages();
		$this->logout();
		$this->get($url);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals($redirect);

		if ($message) {
			if ($message[0] === '#') {
				$this->assertRegExp($message, $this->_requestSession->read($key));
			} else {
				$this->assertSession($message, $key);
			}
		} else {
			$this->assertSessionNotHasKey($key);
		}
	}

	/**
	 * Common helper to confirm that there is no AJAX GET access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $redirect string|array
	 * @param $message string
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAjaxAnonymousAccessRedirect($url, $redirect, $message = false, $class = 'info') {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->enableRetainFlashMessages();
		$this->logout();
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->get($url);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		if ($message) {
			$message = [
				0 => [
					'message' => $message,
					'key' => 'flash',
					'element' => "flash/$class",
					'params' => [],
				],
			];
		} else {
			$message = null;
		}

		$error = [
			'error' => null,
			'content' => null,
			'_message' => $message,
			'_redirect' => [
				'url' => Router::url($redirect),
				'status' => 302,
			],
		];
		$this->assertResponseOk();
		$this->assertEquals(json_encode($error, $this->_jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no POST access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @param $data string|mixed[]
	 * @param $redirect string|array
	 * @param $message string|boolean
	 * @param $key string
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAsAccessRedirect($url, $user, $data = [], $redirect, $message = false, $key = 'Flash.flash.0.message') {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->enableRetainFlashMessages();
		$this->login($user);
		$this->post($url, $data);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals($redirect);

		if ($message) {
			if ($message[0] === '#') {
				$this->assertRegExp($message, $this->_requestSession->read($key));
			} else {
				$this->assertSession($message, $key);
			}
		} else {
			$this->assertSessionNotHasKey($key);
		}
	}

	/**
	 * Common helper to confirm that there is no AJAX POST access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @param $data string|mixed[]
	 * @param $redirect string|array
	 * @param $message string
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAjaxAsAccessRedirect($url, $user, $data = [], $redirect, $message = false, $class = 'info') {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->login($user);
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->post($url, $data);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		if ($message) {
			$message = [
				0 => [
					'message' => $message,
					'key' => 'flash',
					'element' => "flash/$class",
					'params' => [],
				],
			];
		} else {
			$message = null;
		}

		$error = [
			'error' => null,
			'content' => null,
			'_message' => $message,
			'_redirect' => [
				'url' => Router::url($redirect),
				'status' => 302,
			],
		];
		$this->assertResponseOk();
		$this->assertEquals(json_encode($error, $this->_jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no POST access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $data string|mixed[]
	 * @param $redirect string|array
	 * @param $message string|boolean
	 * @param $key string
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAnonymousAccessRedirect($url, $data = [], $redirect, $message = false, $key = 'Flash.flash.0.message') {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->enableRetainFlashMessages();
		$this->logout();
		$this->post($url, $data);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals($redirect);

		if ($message) {
			if ($message[0] === '#') {
				$this->assertRegExp($message, $this->_requestSession->read($key));
			} else {
				$this->assertSession($message, $key);
			}
		} else {
			$this->assertSessionNotHasKey($key);
		}
	}

	/**
	 * Common helper to confirm that there is no AJAX POST access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $data string|mixed[]
	 * @param $redirect string|array
	 * @param $message string
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAjaxAnonymousAccessRedirect($url, $data = [], $redirect, $message = false, $class = 'info') {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->logout();
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->post($url, $data);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		if ($message) {
			$message = [
				0 => [
					'message' => $message,
					'key' => 'flash',
					'element' => "flash/$class",
					'params' => [],
				],
			];
		} else {
			$message = null;
		}

		$error = [
			'error' => null,
			'content' => null,
			'_message' => $message,
			'_redirect' => [
				'url' => Router::url($redirect),
				'status' => 302,
			],
		];
		$this->assertResponseOk();
		$this->assertEquals(json_encode($error, $this->_jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no GET access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAsAccessDenied($url, $user) {
		$this->enableRetainFlashMessages();
		$this->login($user);
		$this->get($url);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals('/');
		$this->assertSession('You do not have permission to access that page.', 'Flash.flash.0.message');
		$this->assertSession('flash/error', 'Flash.flash.0.element');
	}

	/**
	 * Common helper to confirm that there is no AJAX GET access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAjaxAsAccessDenied($url, $user) {
		$this->login($user);
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->get($url);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		$error = [
			'error' => null,
			'content' => null,
			'_message' => [
				0 => [
					'message' => 'You do not have permission to access that page.',
					'key' => 'flash',
					'element' => 'flash/error',
					'params' => [],
				],
			],
			'_redirect' => [
				'url' => Router::url('/'),
				'status' => 302,
			],
		];
		$this->assertResponseOk();
		$this->assertEquals(json_encode($error, $this->_jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no GET access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAnonymousAccessDenied($url) {
		$this->enableRetainFlashMessages();
		$this->logout();
		$this->get($url);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals(['plugin' => false, 'controller' => 'Users', 'action' => 'login', '?' => ['redirect' => Router::url($url)]]);
		$this->assertSession('You must login to access full site functionality.', 'Flash.flash.0.message');
		$this->assertSession('flash/error', 'Flash.flash.0.element');
	}

	/**
	 * Common helper to confirm that there is no AJAX GET access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAjaxAnonymousAccessDenied($url) {
		$this->logout();
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->get($url);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		$error = [
			'error' => null,
			'content' => null,
			'_message' => [
				0 => [
					'message' => 'You must login to access full site functionality.',
					'key' => 'flash',
					'element' => 'flash/error',
					'params' => [],
				],
			],
			'_redirect' => [
				'url' => Router::url(['controller' => 'Users', 'action' => 'login', '?' => ['redirect' => Router::url($url)]]),
				'status' => 302,
			],
		];
		$this->assertResponseOk();
		$this->assertEquals(json_encode($error, $this->_jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no POST access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @param $data string|mixed[]
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAsAccessDenied($url, $user, $data = []) {
		$this->enableRetainFlashMessages();
		$this->login($user);
		$this->post($url, $data);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals('/');
		$this->assertSession('You do not have permission to access that page.', 'Flash.flash.0.message');
		$this->assertSession('flash/error', 'Flash.flash.0.element');
	}

	/**
	 * Common helper to confirm that there is no AJAX POST access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @param $data string|mixed[]
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAjaxAsAccessDenied($url, $user, $data = []) {
		$this->login($user);
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->post($url, $data);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		$error = [
			'error' => null,
			'content' => null,
			'_message' => [
				0 => [
					'message' => 'You do not have permission to access that page.',
					'key' => 'flash',
					'element' => 'flash/error',
					'params' => [],
				],
			],
			'_redirect' => [
				'url' => Router::url('/'),
				'status' => 302,
			],
		];
		$this->assertResponseOk();
		$this->assertEquals(json_encode($error, $this->_jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no POST access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $data string|mixed[]
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAnonymousAccessDenied($url, $data = []) {
		$this->enableRetainFlashMessages();
		$this->logout();
		$this->post($url, $data);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals(['plugin' => false, 'controller' => 'Users', 'action' => 'login', '?' => ['redirect' => Router::url($url)]]);
		$this->assertSession('You must login to access full site functionality.', 'Flash.flash.0.message');
		$this->assertSession('flash/error', 'Flash.flash.0.element');
	}

	/**
	 * Common helper to confirm that there is no AJAX POST access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $data string|mixed[]
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAjaxAnonymousAccessDenied($url, $data = []) {
		$this->logout();
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->post($url, $data);
		unset($_SERVER['HTTP_X_REQUESTED_WITH']);

		$error = [
			'error' => null,
			'content' => null,
			'_message' => [
				0 => [
					'message' => 'You must login to access full site functionality.',
					'key' => 'flash',
					'element' => 'flash/error',
					'params' => [],
				],
			],
			'_redirect' => [
				'url' => Router::url(['controller' => 'Users', 'action' => 'login', '?' => ['redirect' => Router::url($url)]]),
				'status' => 302,
			],
		];
		$this->assertResponseOk();
		$this->assertEquals(json_encode($error, $this->_jsonOptions), (string)$this->_response->getBody());
	}

	protected function debugResponse(): void {
		debug((string)$this->_response->getBody());
	}

	protected function debugEmails(): void {
		foreach (TestEmailTransport::getEmails() as $email) {
			debug($email->message());
		}
	}

}
