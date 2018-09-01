<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\RulesChecker;
use Cake\ORM\TableRegistry;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController {

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _publicActions() {
		return ['logout', 'create_account', 'reset_password'];
	}

	/**
	 * _freeActions method
	 *
	 * @return array list of actions that people can perform even if the system wants them to do something else
	 */
	protected function _freeActions() {
		return ['logout'];
	}

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			// People can perform these operations on their own account
			if (in_array($this->request->params['action'], [
				'change_password',
			]))
			{
				// If a player id is specified, check if it's the logged-in user
				// If no player id is specified, it's always the logged-in user
				$person = $this->request->query('user');
				if (!$person || $person == $this->UserCache->read('Person.user_id')) {
					return true;
				}
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	public function login() {
		$this->set('failed', false);
		$users_table = TableRegistry::get(Configure::read('Security.authModel'));

		// Set some variables the login page needs to properly render the form
		$this->set('model', $users_table->alias());
		$this->set('user_field', $users_table->userField);
		$this->set('pwd_field', $users_table->pwdField);

		if ($this->request->is('post')) {
			$user = $this->Auth->identify();
			if (!$user) {
				$this->Flash->error(__('Username or password is incorrect'), 'default', [], 'auth');
				$this->set('failed', true);
				return;
			}
			$this->Auth->setUser($user);

			if (!empty($this->request->data['remember_me'])) {
				$this->Cookie->write('Auth.User', ['user_name' => $this->request->data('user_name'), 'password' => $this->request->data('password')]);
			}

			return $this->redirect($this->Auth->redirectUrl());
		}
	}

	public function logout() {
		if ($this->Cookie->read('Auth.User')) {
			$this->Cookie->delete('Auth.User');
		}
		$this->request->session()->delete('Zuluru');
		return $this->redirect($this->Auth->logout());
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function create_account() {
		if (!Configure::read('feature.manage_accounts')) {
			$this->Flash->info(__('This system uses {0} to manage user accounts. Account creation through Zuluru is disabled.', Configure::read('feature.manage_name')));
			return $this->redirect('/');
		}

		if (!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager') && $this->UserCache->currentId()) {
			$this->Flash->info(__('You are already logged in!'));
			return $this->redirect('/');
		}

		$this->_loadAddressOptions();
		$this->_loadAffiliateOptions();
		$users_table = TableRegistry::get(Configure::read('Security.authModel'));
		$groups = $users_table->People->Groups->find('options', ['min_level' => 3])->toArray();

		$this->set([
			'user_field' => $users_table->userField,
			'email_field' => $users_table->emailField,
			'groups' => $groups,
		]);

		$user = $users_table->newEntity();

		if ($this->request->is('post')) {
			// Handle affiliations
			if (Configure::read('feature.affiliates') && Configure::read('Perm.is_manager')) {
				pr($this->request->data['person']['affiliates']);
				trigger_error('TODOLATER', E_USER_WARNING);
				exit;
				// Something like this to ensure that the person is a manager of the affiliate they are adding the person for
				$is_user_manager = Configure::read('Perm.is_manager') && in_array($division->league->affiliate_id, $this->UserCache->read('ManagedAffiliateIDs'));
			}

			$this->request->data[$users_table->pwdField] = $this->request->data['new_password'];
			$user = $users_table->patchEntity($user, $this->request->data, [
				'associated' => [
					'People' => ['validate' => 'create'],
					'People.Groups',
					'People.Affiliates',
					'People.Skills',
					'People.Relatives' => ['validate' => 'create'],
					'People.Relatives.Groups',
					'People.Relatives.Affiliates',
					'People.Relatives.Skills',
				],
				'validate' => 'create',
			]);

			if ($users_table->connection()->transactional(function () use ($user, $users_table) {
				if ($users_table->save($user, ['manage_affiliates' => true, 'manage_groups' => true])) {
					return true;
				}

				$this->Flash->warning(__('The account could not be saved. Please correct the errors below and try again.'));

				// Force the various rules checks to run, for better feedback to the user
				$users_table->checkRules($user, RulesChecker::CREATE, ['manage_affiliates' => true, 'manage_groups' => true]);
				$users_table->People->checkRules($user->person, RulesChecker::CREATE, ['manage_affiliates' => true, 'manage_groups' => true]);
				if (!empty($user->person->relatives)) {
					$users_table->People->checkRules($user->person->relatives[0], RulesChecker::CREATE, ['manage_affiliates' => true, 'manage_groups' => true]);
				}
				return false;
			})) {
				$this->Flash->account_created(null, ['params' => ['continue' => $this->request->data['action'] == 'continue']]);

				if (!Configure::read('Perm.is_logged_in')) {
					if (!$this->request->session()->read('Zuluru.external_login')) {
						// Automatically log the user in
						$user = $this->Auth->identify($this->request->data);
						$this->Auth->setUser($user);

						if ($this->request->data['action'] == 'continue') {
							return $this->redirect(['controller' => 'People', 'action' => 'add_relative']);
						}
					} else if (!empty($user->person->relatives)) {
						$this->Flash->info(__('To add additional children, first log in, then go to {0} -> {1}.', __('My Profile'), __('Add new child')));
					}
				}
				return $this->redirect('/');
			}
		} else {
			// By default, select the first group
			$user = $users_table->patchEntity($user, [
				'person' => ['groups' => ['_ids' => [current(array_keys($groups))]]]
			], [
				'validate' => false,
				'associated' => [
					'People' => ['validate' => false],
					'People.Groups',
				],
			]);
		}
		$this->set(compact('user'));
		$this->set('_serialize', true);
	}

	public function TODOLATER_import() {
		$users_table = TableRegistry::get(Configure::read('Security.authModel'));
		$this->set('groups', $users_table->People->Groups->find('options', ['min_level' => 3])->toArray());

		// TODO: Centralize checking of profile fields
		$columns = $this->Users->People->schema()->columns();
		foreach ($columns as $key => $column) {
			if (in_array($column, ['id', 'user_id', 'user_name', 'email', 'complete', 'twitter_token', 'twitter_secret', 'modified'])) {
				unset($columns[$key]);
			}

			// Deal with special cases
			$short_column = str_replace('alternate_', '', $column);
			if ($short_column == 'work_ext') {
				$include = Configure::read('profile.work_phone');
			} else {
				$include = Configure::read("profile.$short_column");
			}
			if (!$include) {
				unset($columns[$key]);
			}
		}
		$columns[] = 'password';
		$columns[] = 'alternate_email';
		$this->set(compact('columns'));

		// Add other columns that we'll accept but are mentioned separately in the view
		// Columns set to "true" are for the user record; anything else goes in the person record
		$columns['id'] = [true];
		$columns['user_name'] = true;
		$columns['email'] = true;

		$user = $users_table->newEntity();

		if ($this->request->is('post')) {
			$continue = true;
			if (empty($this->request->data['Person']['on_error'])) {
				$this->Person->validationErrors['on_error'] = __('Select how to handle fields with errors in them.');
				$continue = false;
			}
			if (empty($this->request->data['Person']['status'])) {
				$this->Person->validationErrors['status'] = __('Select a status for imported accounts.');
				$continue = false;
			}
			if (!empty($this->request->data['file']['error'])) {
				$this->Flash->info(__('There was an error uploading the file.'));
				$continue = false;
			} else if ($this->request->data['file']['type'] != 'text/x-csv') {
				$this->Flash->info(__('Only import from CSV files is currently supported.'));
				$continue = false;
			}

			if ($continue) {
				// TODOLATER: Is the inputTypeMap useful here?
				// http://book.cakephp.org/3.0/en/controllers/components/request-handling.html#automatically-decoding-request-data
				$file = fopen($this->request->data['file']['tmp_name'], 'r');
				$header = fgetcsv($file);
				$skip = [];
				foreach ($header as $key => $column) {
					if (!array_key_exists($column, $columns)) {
						unset($header[$key]);
						$skip[] = $column;
					}
				}
				if (!in_array('email', $header)) {
					$this->Flash->info(__('No email column was found.'));
				} else {
					$this->set(compact('header', 'skip'));
					$remap = [
						'user_name' => $users_table->userField,
						'email' => $users_table->emailField,
						'password' => 'new_password',
					];
					$unmap = array_flip($remap);

					$succeeded = $resolved = $failed = [];
					$parent_id = null;

					while (($row = fgetcsv($file)) !== false) {
						// Skip rows starting with a #
						if ($row[0][0] == '#') {
							continue;
						}

						$continue = true;
						$errors = [];
						$data = [
							'Person' => [],
							$users_table->alias() => [],
							'Affiliate' => $this->request->data['Affiliate'],
						];
						foreach ($header as $key => $column) {
							if (array_key_exists($column, $remap)) {
								$mapped_column = $remap[$column];
							} else {
								$mapped_column = $column;
							}
							if ($columns[$column] === true) {
								$data[$users_table->alias()][$mapped_column] = $row[$key];
							} else {
								$data['Person'][$mapped_column] = $row[$key];
							}
						}
						if (!empty($data['Person']['id'])) {
							$matches = $this->Person->find('count', [
									'contain' => [],
									'conditions' => ['id' => $data['Person']['id']],
							]);
							if ($matches) {
								$errors[] = "id ({$data['Person']['id']} already taken)";
								$continue = false;
							}
						}
						if (empty($data[$users_table->alias()][$users_table->userField])) {
							$user_name = $data[$users_table->alias()][$users_table->emailField];
							if ($this->request->data['Person']['trim_email_domain']) {
								$user_name = $base_name = substr($user_name, 0, strpos($user_name, '@'));
								$append = 2;
								while (true) {
									if (!in_array($user_name, $succeeded) && !in_array($user_name, $resolved)) {
										$matches = $users_table->find('count', [
												'contain' => [],
												'conditions' => [$users_table->userField => $user_name],
										]);
										if (!$matches) {
											break;
										}
									}
									$user_name = "$base_name$append";
									++ $append;
								}
							}
							$data[$users_table->alias()][$users_table->userField] = $user_name;
						}
						if (empty($data[$users_table->alias()]['new_password'])) {
							$data[$users_table->alias()]['new_password'] = $this->_password(10);
						}
						$names = [];
						if (!empty($data['Person']['first_name'])) {
							$names[] = $data['Person']['first_name'];
						}
						if (!empty($data['Person']['last_name'])) {
							$names[] = $data['Person']['last_name'];
						}
						$data['Person']['full_name'] = implode(' ', $names);

						// Special handling of child accounts
						if (strtolower($data[$users_table->alias()][$users_table->emailField]) == 'child') {
							$is_child = true;
							$data['Group'] = ['Group' => [GROUP_PLAYER]];
							unset($data[$users_table->alias()]);
							$data['Related'] = [['person_id' => $parent_id, 'approved' => true]];
						} else {
							$is_child = false;
							$data['Group'] = $this->request->data['Group'];
							$data['Person']['email'] = $data[$users_table->alias()][$users_table->emailField];
							if (!empty($users_table->nameField) && empty($data[$users_table->alias()][$users_table->nameField])) {
								$data[$users_table->alias()][$users_table->nameField] = $data['Person']['full_name'];
							}
						}
						if (empty($data['Person']['status'])) {
							$data['Person']['status'] = $this->request->data['Person']['status'];
						}

						$success = $this->Person->saveAll($data, ['validate' => 'only']);

						foreach (array_keys($users_table->validationErrors) as $column) {
							if (array_key_exists($column, $unmap)) {
								$mapped_column = $unmap[$column];
							} else {
								$mapped_column = $column;
							}
							$errors[] = "$mapped_column ({$data[$users_table->alias()][$column]})";
							$continue = false;
						}

						if ($continue && !$this->request->data['Person']['trial_run']) {
							$this->Person->create();
							if ($success) {
								$success = $this->Person->saveAll($data);
							} else {
								$old_validate = $this->Person->validate;
								if ($this->request->data['Person']['on_error'] == 'blank') {
									foreach (array_keys($this->Person->validationErrors) as $column) {
										unset($data['Person'][$column]);
										unset($this->Person->validate[$column]);
										$errors[] = "$column ('{$data['Person'][$column]}' blanked)";
									}
								} else if ($this->request->data['Person']['on_error'] == 'ignore') {
									foreach (array_keys($this->Person->validationErrors) as $column) {
										unset($this->Person->validate[$column]);
										$errors[] = "$column ('{$data['Person'][$column]}' imported anyway)";
									}
								} else {
									$continue = false;
								}

								if ($continue) {
									$success = $this->Person->saveAll($data, ['validate' => 'first']);
								}
								$this->Person->validate = $old_validate;
							}
						}

						if ($is_child) {
							$desc = "&nbsp;&nbsp;+ {$data['Person']['full_name']} as a child";
						} else {
							$desc = "{$data[$users_table->alias()][$users_table->userField]} ({$data[$users_table->alias()][$users_table->emailField]})";
						}

						if ($continue && $success) {
							if (!$is_child) {
								$parent_id = $users_table->id;
							}
							if (empty($errors)) {
								$succeeded[] = $desc;
							} else {
								$resolved[] = $desc . ': ' . implode(', ', $errors);
							}
							if (!$this->request->data['Person']['trial_run'] && $this->request->data['Person']['notify_new_users'] && !$is_child) {
								$this->_sendMail([
									'to' => $data,
									'subject' => __('New account'),
									'template' => 'account_new',
									'sendAs' => 'both',
									'viewVars' => [
										'user' => $data,
										'user_model' => $users_table->alias(),
										'user_field' => $users_table->userField,
									],
								]);
							}
						} else {
							unset($this->Person->validationErrors[$users_table->alias()]);
							foreach (array_keys($this->Person->validationErrors) as $column) {
								$errors[] = "$column ({$data['Person'][$column]})";
								if ($this->request->data['Person']['on_error'] == 'skip') {
									$continue = false;
								}
							}

							if ($continue) {
								$resolved[] = $desc . ': ' . implode(', ', $errors);
							} else {
								$failed[] = $desc . ': ' . implode(', ', $errors);
							}
						}
					}
				}
			}
		} else {
			$user->person = $users_table->People->newEntity([
				// Set default state for checkboxes, since Cake doesn't allow default
				// settings in the input call for them.
				'trim_email_domain' => true,
				'trial_run' => true,
				'notify_new_users' => true,
			], ['validate' => false]);
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('user', 'affiliates', 'succeeded', 'resolved', 'failed'));
	}

	public function change_password() {
		$id = $this->request->query('user');
		if (!$id) {
			$id = $this->UserCache->read('Person.user_id');
		}

		$user_model = Configure::read('Security.authModel');
		$users_table = TableRegistry::get($user_model);
		try {
			$user = $users_table->get($id, [
				'contain' => ['People']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid user.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid user.'));
			return $this->redirect('/');
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$this->request->data[$users_table->pwdField] = $this->request->data['new_password'];
			$user = $users_table->patchEntity($user, $this->request->data, ['validate' => 'password']);
			if ($users_table->save($user)) {
				// Update the "remember me" cookie, if there is one
				if ($this->Cookie->read('Auth.User')) {
					$this->Cookie->write('Auth.User', ['user_name' => $user->{$users_table->userField}, 'password' => $this->request->data($users_table->pwdField)]);
				}

				$this->Flash->success(__('The password has been updated.'));
				return $this->redirect('/');
			} else {
				$this->Flash->warning(__('The password could not be updated. Please, try again.'));
			}
		}
		$this->set([
			'user' => $user,
			'is_me' => ($this->UserCache->read('Person.user_id') == $id),
		]);
	}

	public function reset_password($id = null, $code = null) {
		if ($this->UserCache->currentId() !== null) {
			$this->Flash->info(__('You are already logged in. Use the change password form instead.'));
			return $this->redirect(['action' => 'change_password']);
		}

		$user_model = Configure::read('Security.authModel');
		$users_table = TableRegistry::get($user_model);
		$user = $users_table->newEntity();
		if ($code !== null) {
			try {
				$user = $users_table->get($id, [
					'contain' => ['People']
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid user.'));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid user.'));
				return $this->redirect('/');
			}

			// Look up the provided code
			if ($this->_passwordCode($user) != $code) {
				$this->Flash->warning(__('The provided code is not valid!'));
			} else {
				if ($this->_emailNewPassword($user, $user->person)) {
					$this->Flash->success(__('Your new password has been emailed to you.'));
					return $this->redirect('/');
				} else {
					$this->Flash->warning(__('There was an error emailing your new password to you, please try again. If you have continued problems, please contact the office.'));
				}
			}
		} else if ($this->request->is(['patch', 'post', 'put'])) {
			// Remove any empty fields
			foreach ($this->request->data as $field => $value) {
				if (empty($value)) {
					unset($this->request->data[$field]);
				}
			}
			if (!empty($this->request->data)) {
				// Find the user and send the email
				$matches = $users_table->find()
					->contain(['People'])
					->where($this->request->data);
				switch ($matches->count()) {
					case 0:
						$this->Flash->info(__('No matching accounts were found!'));
						break;

					case 1:
						$user = $matches->first();
						if (empty($user->email)) {
							$this->Flash->success(__('This account has no email address associated with it. Please contact an administrator to assist you.'));
							return $this->redirect('/');
						}
						if ($this->_emailResetCode($user, $user->person)) {
							$this->Flash->success(__('Your reset code has been emailed to you.'));
							return $this->redirect('/');
						} else {
							$this->Flash->warning(__('There was an error emailing the reset code to you, please try again. If you have continued problems, please contact the office.'));
						}
						break;

					default:
						$this->Flash->info(__('Multiple matching accounts were found for this email address; you will need to specify the username.'));
						break;
				}
			}
		}

		$this->set([
			'user' => $user,
			'user_field' => $users_table->userField,
			'email_field' => $users_table->emailField,
		]);
	}

	protected function _passwordCode($user) {
		return str_replace('/', '_', substr($user->password, -8));
	}

	protected function _emailResetCode($user, $person) {
		return $this->_sendMail([
			'to' => $user,
			'subject' => __('Password reset code'),
			'template' => 'password_reset',
			'sendAs' => 'both',
			'viewVars' => [
				'user' => $user,
				'person' => $person,
				'code' => $this->_passwordCode($user),
			]
		]);
	}

	protected function _emailNewPassword($user, $person) {
		$users_table = TableRegistry::get(Configure::read('Security.authModel'));
		$user->password = $password = $this->_password(16);
		if ($users_table->save($user)) {
			return $this->_sendMail([
				'to' => $user,
				'subject' => __('New password'),
				'template' => 'password_new',
				'sendAs' => 'both',
				'viewVars' => compact(['user', 'password']),
			]);
		}
		return false;
	}

	static public function _password($length) {
		$characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
		$string_length = strlen($characters) - 1;
		$string = '';

		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, $string_length)];
		}
		return $string;
	}

}
