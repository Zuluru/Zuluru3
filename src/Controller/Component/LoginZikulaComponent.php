<?php

namespace App\Controller\Component;

use Cake\ORM\Table;
use Cake\Routing\Router;

class LoginZikulaComponent extends LoginComponent {
	public function TODOLATER_login(Table $users_table) {
		// Check if we're running under Zikula
		if (Router::getRequest()->session()->read('PNSVrand')) {
			// Hide login/logout menu items
			Router::getRequest()->session()->write('Zuluru.external_login', true);
		}

		// Check if we're logged in to Zikula
		$uid = Router::getRequest()->session()->read('PNSVuid');
		if ($uid) {
			// Parameter to Auth->login must be a string
			$this->_registry->getController()->Auth->login($uid . '');
			Router::getRequest()->session()->write('Zuluru.zikula_session', $uid);
		}
	}

	// We might have session information but the user has logged out of Zikula
	public function TODOLATER_expired() {
		if (Router::getRequest()->session()->read('Zuluru.external_login')) {
			$uid = Router::getRequest()->session()->read('PNSVuid');
			if (!$uid || $uid != Router::getRequest()->session()->read('Zuluru.zikula_session')) {
				return true;
			}
		}
	}
}
