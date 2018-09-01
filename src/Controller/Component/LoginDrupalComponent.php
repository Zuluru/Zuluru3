<?php

namespace App\Controller\Component;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Core\Configure;

/**
 * @property \Cake\Controller\Component\AuthComponent $Auth
 */
class LoginDrupalComponent extends LoginComponent {

	// The other component your component uses
	public $components = ['Auth'];

	public function login(Table $users_table) {
		// Check if we're running under Drupal
		$prefix = ini_get('session.cookie_secure') ? 'SSESS' : 'SESS';
		$session_name = Configure::read('Security.authSession');
		$session_name = $prefix . substr(hash('sha256', $session_name), 0, 32);

		// Hide login/logout menu items
		$this->request->session()->write('Zuluru.external_login', true);

		$session = $this->request->cookie($session_name);
		if ($session) {
			$user = $users_table->find()
				->matching('DrupalSessions', function (Query $q) use ($session) {
					return $q->where(['DrupalSessions.sid' => $session]);
				})
				->contain([
					'People' => ['Groups'],
				])
				->first();

			// Check if we're logged in to Drupal
			if (!$user || empty($user->uid)) {
				Router::getRequest()->session()->delete('Zuluru.drupal_session');
				Router::getRequest()->session()->delete('Zuluru.zuluru_person_id');
				return;
			}

			// If there is no person record for this user, create it.
			if (!$user->has('person')) {
				$user->person = $users_table->People->createPersonRecord($user);
			}

			$this->Auth->setUser($user);
			Router::getRequest()->session()->write('Zuluru.drupal_session', $session);
			Router::getRequest()->session()->write('Zuluru.zuluru_person_id', $user->person->id);
		}
	}

	// We might have session information but the user has logged out of Drupal
	public function expired() {
		if (Router::getRequest()->session()->read('Zuluru.external_login')) {
			$prefix = ini_get('session.cookie_secure') ? 'SSESS' : 'SESS';
			$session_name = Configure::read('Security.authSession');
			$session_name = $prefix . substr(hash('sha256', $session_name), 0, 32);
			if ($this->request->cookie($session_name) != Router::getRequest()->session()->read('Zuluru.drupal_session')) {
				return true;
			}
			return false;
		}
	}
}
