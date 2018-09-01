<?php
namespace App\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;

class ControllerTestCase extends IntegrationTestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		// Minimize menu building during most tests, to cut down on required fixtures and speed things along
		Configure::write('feature.minimal_menus', true);

		foreach (Configure::read('App.globalListeners') as $listener) {
			EventManager::instance()->on($listener);
		}
	}

	public function tearDown() {
		Cache::clear(false, 'long_term');
		FrozenTime::setTestNow();
		FrozenDate::setTestNow();
		parent::tearDown();
	}

	/**
	 * Common helper to confirm that there is access allowed for the given user (default anonymous) to the given URL
	 *
	 * @param $url array
	 * @param null $user string|null
	 * @param $method string
	 * @param $data mixed[]
	 */
	protected function assertAccessOk($url, $user = null, $method = 'get', $data = []) {
		if ($user) {
			$this->session(['Auth.User.id' => $user, 'Zuluru.zuluru_person_id' => $user]);
		}
		if ($method == 'get') {
			$this->get($url);
		} else if ($method == 'getajax') {
			// Set header for Ajax request
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
			$this->get($url);
		} else if ($method == 'post') {
			$this->post($url, $data);
		} else if ($method == 'postajax') {
			// Set header for Ajax request
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
			$this->post($url, $data);
		} else {
			$this->fail('Invalid method: get, getajax, post and postajax are supported');
		}
		$this->assertResponseOk();
	}

	/**
	 * Common helper to confirm that there is no access allowed for the given user (default anonymous) to the given URL
	 *
	 * @param $url array
	 * @param $user string|null
	 * @param $method string
	 * @param $data mixed[]
	 * @param $redirect string|null
	 * @param $message string
	 * @param $key string
	 */
	protected function assertAccessRedirect($url, $user = null, $method = 'get', $data = [], $redirect = null, $message = null, $key = null) {
		if ($user) {
			$this->session(['Auth.User.id' => $user, 'Zuluru.zuluru_person_id' => $user]);
		}
		if ($method == 'get') {
			$this->get($url);
		} else if ($method == 'getajax') {
			// Set header for Ajax request
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
			$this->get($url);
		} else if ($method == 'post') {
			$this->post($url, $data);
		} else if ($method == 'postajax') {
			// Set header for Ajax request
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
			$this->post($url, $data);
		} else {
			$this->fail('Invalid method: get, getajax, post and postajax are supported');
		}

		if (!$redirect) {
			if ($user) {
				$redirect = '/';
			} else {
				$redirect = ['controller' => 'Users', 'action' => 'login'];
			}
		}

		if ($method == 'getajax' || $method == 'postajax') {
			if ($message) {
				$message = [
					0 => [
						'message' => $message,
						'key' => 'flash',
						'element' => 'Flash/info',
						'params' => [],
					],
				];
			}

			$error = [
				'error' => null,
				'content' => null,
				'_message' => $message,
				'_redirect' => [
					'url' => Router::url($redirect, true),
					'status' => 302,
				],
			];
			$this->assertResponseOk();
			$this->assertEquals(json_encode($error), $this->_response->body());
		} else {
			$this->assertResponseCode(302);
			$this->assertRedirect($redirect);

			if ($message !== false) {
				if ($key === null) {
					$key = 'Flash.auth.0.message';
				}
				if ($message === null) {
					if ($user) {
						$message = 'You do not have permission to access that page.';
					} else {
						$message = 'You must login to access full site functionality.';
					}
				}
				if ($message[0] == '#') {
					$this->assertRegExp($message, $this->_requestSession->read($key));
				} else {
					$this->assertEquals($message, $this->_requestSession->read($key));
				}
			}
		}
	}

}
