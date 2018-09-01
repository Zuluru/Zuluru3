<?php
/**
 * Derived class for implementing functionality for interfacing with phpBB3.
 * This is intended for use only when you have phpBB3 integrated with some
 * other third-party software which is in turn handling user records.
 */
namespace App\Module;

use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

class CallbackPhpbb3 extends Callback {

	public function implementedEvents() {
		return [
			'Model.User.afterSave' => 'afterSave',
			'Model.User.afterDelete' => 'afterDelete',
		];
	}

	public function afterSave(Event $event, User $user) {
		$users_table = TableRegistry::get(Configure::read('Security.authModel'));
		$username_field = $users_table->userField;
		$email_field = $users_table->emailField;

		// We only care about username and email address changes
		if (!$user->dirty($username_field) && !$user->dirty($email_field)) {
			return;
		}

		// phpBB3 files need these
		global $phpbb_root_path, $phpEx;
		$phpbb_root_path = Configure::read('phpbb3.root_path');
		$phpEx = 'php';
		require($phpbb_root_path . 'config.php');

		$bb3_class = "{$table_prefix}users";
		$bb3_model = TableRegistry::get($bb3_class);
		$bb3_user = $bb3_model->find()
			->where(['username' => $user->getOriginal($username_field)])
			->first();

		if (!$bb3_user) {
			return;
		}

		// Include a couple of things needed for function definitions
		define('IN_PHPBB', true);
		include($phpbb_root_path . 'includes/functions.php');
		include($phpbb_root_path . 'includes/utf/utf_tools.php');

		$bb3_model->patchEntity($bb3_user, [
			'username' => $user->{$username_field},
			'username_clean' => utf8_clean_string($user->{$username_field}),
			'user_email' => $user->{$email_field},
			'user_email_hash' => phpbb_email_hash($user->{$email_field}),
		]);
		$bb3_model->save($bb3_user);
	}

	public function afterDelete(Event $event, User $user) {
		$users_table = TableRegistry::get(Configure::read('Security.authModel'));
		$username_field = $users_table->userField;

		// phpBB3 files need these
		global $phpbb_root_path, $phpEx;
		$phpbb_root_path = Configure::read('phpbb3.root_path');
		$phpEx = 'php';
		require($phpbb_root_path . 'config.php');

		$bb3_class = "{$table_prefix}users";
		$bb3_model = TableRegistry::get($bb3_class);
		$bb3_user = $bb3_model->find()
			->where(['username' => $user->$username_field])
			->first();

		if (!$bb3_user) {
			return;
		}

		$bb3_model->delete($bb3_user);
	}
}
