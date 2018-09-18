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
		$joomla_session = Router::getRequest()->session()->read('joomla');
		if (!$joomla_session) {
			return;
		}

		$joomla_session = unserialize(base64_decode($joomla_session));
		$joomla_user = $joomla_session->get('__default.user');

		// Check if we're logged in to Joomla
		if (!$joomla_user || empty($joomla_user->id)) {
			Router::getRequest()->session()->delete('Zuluru.zuluru_person_id');
			return;
		}

		// Hide login/logout menu items
		Router::getRequest()->session()->write('Zuluru.external_login', true);

		$user = $users_table->get($joomla_user->id, [
			'contain' => ['People' => ['Groups']]
		]);

		// If there is no person record for this user, create it.
		if (!$user->has('person')) {
			$user->person = $users_table->People->createPersonRecord($user);
		}

		$this->Auth->setUser($user);
		Router::getRequest()->session()->write('Zuluru.zuluru_person_id', $user->person->id);
	}

	// We might have session information but the user has logged out of Joomla
	public function expired() {
		if (Router::getRequest()->session()->read('Zuluru.external_login')) {
			$joomla_session = Router::getRequest()->session()->read('joomla');
			if (!$joomla_session) {
				return true;
			}
			return false;
		}
	}
}
