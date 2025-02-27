<?php
namespace App\Test\TestCase\Controller;

use App\Core\UserCache;
use App\TestSuite\Constraint\Session\SessionRegExp;
use App\TestSuite\ZuluruIntegrationTestTrait;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\TestSuite\TestEmailTransport;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

class ControllerTestCase extends TestCase {

	use IntegrationTestTrait {
		_sendRequest as _parentSendRequest;
	}
	use ZuluruIntegrationTestTrait;
	use TruncateDirtyTables;

	protected int $jsonOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR;
	protected string $flashKey = 'Flash.flash.0.message';

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
		$this->clearPlugins();
		parent::tearDown();
	}

	/**
	 * Clear the CSRF token flag after each request.
	 *
	 * @inheritDoc
	 * @throws \PHPUnit\Exception
	 * @throws \Throwable
	 */
	protected function _sendRequest($url, $method, $data = []): void {
		$this->_parentSendRequest($url, $method, $data);
		$this->_csrfToken = false;
	}

	/**
	 * @param $personId int|int[]
	 * @return void
	 */
	protected function login($personId) {
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
		$this->enableCsrfToken();
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
		$this->enableCsrfToken();
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
		$this->enableCsrfToken();
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
		$this->enableCsrfToken();
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
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAsAccessRedirect($url, $user, $redirect, $message = false) {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->login($user);
		$this->get($url);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals($redirect);

		if ($message) {
			if ($message[0] === '#') {
				$this->assertThat($message, new SessionRegExp($this->flashKey));
			} else if (is_array($message)) {
				$this->assertFlashMessages($message);
			} else {
				$this->assertFlashMessage($message);
			}
		} else {
			$this->assertSessionNotHasKey($this->flashKey);
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
		$this->assertEquals(json_encode($error, $this->jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no GET access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $redirect string|array
	 * @param $message string|boolean
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAnonymousAccessRedirect($url, $redirect, $message = false) {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->logout();
		$this->get($url);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals($redirect);

		if ($message) {
			if ($message[0] === '#') {
				$this->assertThat($message, new SessionRegExp($this->flashKey));
			} else if (is_array($message)) {
				$this->assertFlashMessages($message);
			} else {
				$this->assertFlashMessage($message);
			}
		} else {
			$this->assertSessionNotHasKey($this->flashKey);
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
		$this->assertEquals(json_encode($error, $this->jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no POST access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @param $data string|mixed[]
	 * @param $redirect string|array
	 * @param $message string|boolean
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAsAccessRedirect($url, $user, $data = [], $redirect, $message = false) {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->login($user);
		$this->enableCsrfToken();
		$this->post($url, $data);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals($redirect);

		if ($message) {
			if ($message[0] === '#') {
				$this->assertThat($message, new SessionRegExp($this->flashKey));
			} else if (is_array($message)) {
				$this->assertFlashMessages($message);
			} else {
				$this->assertFlashMessage($message);
			}
		} else {
			$this->assertSessionNotHasKey($this->flashKey);
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
		$this->enableCsrfToken();
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
		$this->assertEquals(json_encode($error, $this->jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no POST access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $data string|mixed[]
	 * @param $redirect string|array
	 * @param $message string|boolean
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAnonymousAccessRedirect($url, $data = [], $redirect, $message = false) {
		$this->assertNotEmpty($redirect, 'Redirect parameter cannot be empty.');

		$this->logout();
		$this->enableCsrfToken();
		$this->post($url, $data);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals($redirect);

		if ($message) {
			if ($message[0] === '#') {
				$this->assertThat($message, new SessionRegExp($this->flashKey));
			} else if (is_array($message)) {
				$this->assertFlashMessages($message);
			} else {
				$this->assertFlashMessage($message);
			}
		} else {
			$this->assertSessionNotHasKey($this->flashKey);
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
		$this->enableCsrfToken();
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
		$this->assertEquals(json_encode($error, $this->jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no GET access allowed for the given user to the given URL
	 *
	 * @param $url array
	 * @param $user int|int[] User ID
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAsAccessDenied($url, $user) {
		$this->login($user);
		$this->get($url);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals('/');
		$this->assertFlashMessage('You do not have permission to access that page.');
		$this->assertFlashElement('flash/error');
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
		$this->assertEquals(json_encode($error, $this->jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no GET access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @throws \PHPUnit\Exception
	 */
	protected function assertGetAnonymousAccessDenied($url) {
		$this->logout();
		$this->get($url);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals(['plugin' => false, 'controller' => 'Users', 'action' => 'login', '?' => ['redirect' => Router::url($url)]]);
		$this->assertFlashMessage('You must login to access full site functionality.');
		$this->assertFlashElement('flash/error');
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
		$this->assertEquals(json_encode($error, $this->jsonOptions), (string)$this->_response->getBody());
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
		$this->login($user);
		$this->enableCsrfToken();
		$this->post($url, $data);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals('/');
		$this->assertFlashMessage('You do not have permission to access that page.');
		$this->assertFlashElement('flash/error');
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
		$this->enableCsrfToken();
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
		$this->assertEquals(json_encode($error, $this->jsonOptions), (string)$this->_response->getBody());
	}

	/**
	 * Common helper to confirm that there is no POST access allowed for anonymous users to the given URL
	 *
	 * @param $url array
	 * @param $data string|mixed[]
	 * @throws \PHPUnit\Exception
	 */
	protected function assertPostAnonymousAccessDenied($url, $data = []) {
		$this->logout();
		$this->enableCsrfToken();
		$this->post($url, $data);

		$this->assertResponseCode(302);
		$this->assertRedirectEquals(['plugin' => false, 'controller' => 'Users', 'action' => 'login', '?' => ['redirect' => Router::url($url)]]);
		$this->assertFlashMessage('You must login to access full site functionality.');
		$this->assertFlashElement('flash/error');
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
		$this->enableCsrfToken();
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
		$this->assertEquals(json_encode($error, $this->jsonOptions), (string)$this->_response->getBody());
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
