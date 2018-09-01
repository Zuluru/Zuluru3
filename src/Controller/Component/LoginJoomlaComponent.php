<?php

namespace App\Controller\Component;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Core\Configure;

/**
 * @property \Cake\Controller\Component\AuthComponent $Auth
 */
class LoginJoomlaComponent extends LoginComponent {

	// The other component your component uses
	public $components = ['Auth'];

	public function login(Table $users_table) {
		// Check if we're running under Joomla
		$session_name = md5(md5(Configure::read('Security.joomlaSecret') . 'site'));

		// Hide login/logout menu items
		Router::getRequest()->session()->write('Zuluru.external_login', true);

		$session = $this->request->cookie($session_name);
		if ($session) {
			$user = $users_table->find()
				->matching('JoomlaSessions', function (Query $q) use ($session) {
					return $q->where(['JoomlaSessions.session_id' => $session]);
				})
				->contain([
					'People' => ['Groups'],
				])
				->first();

			// Check if we're logged in to Joomla
			if (!$user || empty($user->id)) {
				Router::getRequest()->session()->delete('Zuluru.joomla_session');
				Router::getRequest()->session()->delete('Zuluru.zuluru_person_id');
				return;
			}

			// If there is no person record for this user, create it.
			if (!$user->has('person')) {
				$user->person = $users_table->People->createPersonRecord($user);
			}

			$this->Auth->setUser($user);
			Router::getRequest()->session()->write('Zuluru.joomla_session', $session);
			Router::getRequest()->session()->write('Zuluru.zuluru_person_id', $user->person->id);
		}
	}

	// We might have session information but the user has logged out of Joomla
	public function expired() {
		if (Router::getRequest()->session()->read('Zuluru.external_login')) {
			$session_name = md5(md5(Configure::read('Security.joomlaSecret') . 'site'));
			if ($this->request->cookie($session_name) != Router::getRequest()->session()->read('Zuluru.joomla_session')) {
				return true;
			}
			return false;
		}
	}
}
