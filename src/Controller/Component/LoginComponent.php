<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\Table;

class LoginComponent extends Component {
	public function login(Table $users_table) {
	}

	public function expired() {
		return false;
	}
}
