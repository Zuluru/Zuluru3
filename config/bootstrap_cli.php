<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Configure;

/*
 * Additional bootstrapping and configuration for CLI environments should
 * be put here.
 */

// Set the fullBaseUrl to allow URLs to be generated in shell tasks.
// This is useful when sending email from shells.
//Configure::write('App.fullBaseUrl', php_uname('n'));

// Set logs to different files so they don't have permission conflicts.
if (Configure::check('Log.debug')) {
	Configure::write('Log.debug.file', 'cli-debug');
}
if (Configure::check('Log.error')) {
	Configure::write('Log.error.file', 'cli-error');
}
if (Configure::check('Log.queries')) {
	Configure::write('Log.queries.file', 'cli-sql');
}
if (Configure::check('Log.rules')) {
	Configure::write('Log.rules.file', 'cli-rules');
}

// Set up scheduled tasks
Configure::write('SchedulerShell.jobs', [
	'OpenLeagues' => ['interval' => 'next day 00:00', 'task' => 'OpenLeagues'],
	'DeactivateAccounts' => ['interval' => 'next Monday 00:00', 'task' => 'DeactivateAccounts'],
	'MembershipBadges' => ['interval' => 'next day 00:00', 'task' => 'MembershipBadges'],
	'RecalculateRatings' => ['interval' => 'next day 04:00', 'task' => 'RecalculateRatings'],
	'MembershipLetters' => ['interval' => 'next day 09:00', 'task' => 'MembershipLetters'],
	'FinalizeGames' => ['interval' => 'PT55M', 'task' => 'FinalizeGames'],
	'RosterEmails' => ['interval' => 'next day ' . Configure::read('App.reminderEmailTime'), 'task' => 'RosterEmails'],
	'GameAttendance' => ['interval' => 'next day ' . Configure::read('App.reminderEmailTime'), 'task' => 'GameAttendance'],
	'TeamEventAttendance' => ['interval' => 'next day ' . Configure::read('App.reminderEmailTime'), 'task' => 'TeamEventAttendance'],
	'RunReport' => ['interval' => 'PT1M', 'task' => 'RunReport'],
	'InitializeBadge' => ['interval' => 'PT1M', 'task' => 'InitializeBadge'],
]);
