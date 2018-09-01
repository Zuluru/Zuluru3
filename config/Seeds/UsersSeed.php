<?php
use Cake\Core\Configure;
use Migrations\AbstractSeed;

/**
 * Users seed.
 */
class UsersSeed extends AbstractSeed {
	/**
	 * Run Method.
	 *
	 * @return void
	 */
	public function run() {
		Configure::write('new_admin_password', $password = \App\Controller\UsersController::_password(16));
		$hasher = new \Cake\Auth\DefaultPasswordHasher();
		$data = [
			[
				'id' => 1,
				'user_name' => 'admin',
				'password' => $hasher->hash($password),
				'email' => 'admin@' . Configure::read('App.domain'),
			],
		];

		$table = $this->table('users');
		$table->insert($data)->save();
	}
}
