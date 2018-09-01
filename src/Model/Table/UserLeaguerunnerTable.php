<?php
namespace App\Model\Table;

/**
 * Class for handling authentication using a user database imported from Leaguerunner.
 * TODOSECOND: Eliminate this entirely? What purpose is it serving?
 */
class UserLeaguerunnerTable extends UsersTable {
	/**
	 * function to use for hashing passwords.
	 */
	public $hashMethod = 'md5';

	public function initialize(array $config) {
		parent::initialize($config);
	}
}
