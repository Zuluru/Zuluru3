<?php
namespace App\Shell;

use Cake\Console\Shell;

class RunShell extends Shell {

	public $tasks = [
		'OpenLeagues',
		'DeactivateAccounts',
		'MembershipBadges',
		'RecalculateRatings',
		'MembershipLetters',
		'FinalizeGames',
		'RosterEmails',
		'GameAttendance',
		'TeamEventAttendance',
		'RunReport',
		'InitializeBadge',
	];

	public function main() {
		$args = func_get_args();
		if (empty($args)) {
			$this->out('You must specify a task to run.');
			return;
		}
		$subshell = array_shift($args);
		$object = $this->{$subshell};
		if (!$object instanceof Shell) {
			$this->out('You must specify a task to run.');
			return;
		}
		call_user_func_array([$object, 'main'], $args);
	}

}
