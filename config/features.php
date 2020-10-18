<?php
/**
 * This file has two sections.
 *
 * First, we set up some mappings to pre-defined database values and
 * a few other constants. These items should not ever be changed.
 *
 * Second, we set up some static global configuration. These items
 * generally won't change for different installations. If you have
 * any local configuration customizations, adjust the $features array
 * by adding, altering or unsetting values through a file called
 * features_custom.php (which you must create).
 */
namespace App\Config;

if (!defined('GROUP_PLAYER')) {
	define('GROUP_PLAYER', 1);
	define('GROUP_PARENT', 2);
	define('GROUP_COACH', 3);
	define('GROUP_VOLUNTEER', 4);
	define('GROUP_OFFICIAL', 5);
	define('GROUP_MANAGER', 6);
	define('GROUP_ADMIN', 7);

	define('AFFILIATE_DUMMY', 1);

	define('PROFILE_DISABLED', 0);
	define('PROFILE_USER_UPDATE', 1);
	define('PROFILE_ADMIN_UPDATE', 2);
	define('PROFILE_REGISTRATION', 3);

	define('CAP_UNLIMITED', -1);
	define('CAP_COMBINED', -2);

	define('NEW_FRANCHISE', -1);

	define('SEASON_GAME', 1);
	define('POOL_PLAY_GAME', 2);
	define('CROSSOVER_GAME', 3);
	define('BRACKET_GAME', 4);

	define('APPROVAL_AUTOMATIC', -1);		// approval, scores agree
	define('APPROVAL_AUTOMATIC_HOME', -2);  // approval, home score used
	define('APPROVAL_AUTOMATIC_AWAY', -3);  // approval, away score used
	define('APPROVAL_AUTOMATIC_FORFEIT', -4); // approval, no score entered

	define('ROSTER_APPROVED', 1);
	define('ROSTER_INVITED', 2);
	define('ROSTER_REQUESTED', 3);

	define('REASON_TYPE_PLAYER_ACTIVE', 1);
	define('REASON_TYPE_PLAYER_PASSIVE', 2);
	define('REASON_TYPE_TEAM', 3);
	define('REASON_TYPE_CONSOLIDATED', 99);

	define('ATTENDANCE_UNKNOWN', 0);	// status is unknown
	define('ATTENDANCE_ATTENDING', 1);	// attendance has been confirmed by player (and captain, if a substitute)
	define('ATTENDANCE_ABSENT', 2);		// absence has been confirmed by player
	define('ATTENDANCE_INVITED', 3);	// substitute has been invited by the captain
	define('ATTENDANCE_AVAILABLE', 4);	// substitute has indicated they are available
	define('ATTENDANCE_NO_SHOW', 5);	// player said they were coming, but didn't show

	// Constants for IDs of automatic questions
	// Must all be negative to avoid conflicts with user-created questions
	define('TEAM_NAME', -1);
	define('SHIRT_COLOUR', -2);
	define('REGION_PREFERENCE', -3);
	define('OPEN_ROSTER', -4);
	define('TEAM_ID_CREATED', -5);
	define('FRANCHISE_ID', -6);
	define('FRANCHISE_ID_CREATED', -7);
	define('TRACK_ATTENDANCE', -10);
	define('SHIRT_SIZE', -20);

	// Event connection types
	define('EVENT_PREDECESSOR', 1);
	define('EVENT_SUCCESSOR', 2);
	define('EVENT_ALTERNATE', 3);

	define('VISIBILITY_PRIVATE', 1);
	define('VISIBILITY_CAPTAINS', 2);
	define('VISIBILITY_TEAM', 3);
	define('VISIBILITY_PUBLIC', 4);
	define('VISIBILITY_ADMIN', 5);
	define('VISIBILITY_COORDINATOR', 6);

	define('BADGE_VISIBILITY_ADMIN', 1);
	define('BADGE_VISIBILITY_HIGH', 2);
	define('BADGE_VISIBILITY_MEDIUM', 3);
	define('BADGE_VISIBILITY_LOW', 4);

	define('SCHEDULE_TYPE_LEAGUE', 1);
	define('SCHEDULE_TYPE_TOURNAMENT', 2);
	define('SCHEDULE_TYPE_NONE', 3);

	define('ONLINE_FULL_PAYMENT', 1);
	define('ONLINE_MINIMUM_DEPOSIT', 2);
	define('ONLINE_SPECIFIC_DEPOSIT', 3);
	define('ONLINE_DEPOSIT_ONLY', 4);
	define('ONLINE_NO_MINIMUM', 5);
	define('ONLINE_NO_PAYMENT', 6);

	define('TEST_PAYMENTS_NOBODY', 0);
	define('TEST_PAYMENTS_EVERYBODY', 1);
	define('TEST_PAYMENTS_ADMINS', 2);

	// Minimum "fake id" to use for setting edit pages
	define('MIN_FAKE_ID', 1000000000);
}

$features['season_is_indoor'] = [
	'None'			=> false,
	'Winter'		=> false,
	'Winter Indoor'	=> true,
	'Spring'		=> false,
	'Spring Indoor'	=> true,
	'Summer'		=> false,
	'Summer Indoor'	=> true,
	'Fall'			=> false,
	'Fall Indoor'	=> true,
];

// List of game statuses that indicate that the game was not played.
$features['unplayed_status'] = [
	'cancelled',
	'forfeit',
	'rescheduled',
];

// List of stat types for various displays
$features['stat_types'] = [
	'game' => [
		'entered',
		'game_calc',
	],
	'team' => [
		'season_total',
		'season_avg',
		'season_calc',
	],
];

$features['approved_by'] = [
	APPROVAL_AUTOMATIC			=> 'automatic approval',
	APPROVAL_AUTOMATIC_HOME		=> 'automatic approval using home submission',
	APPROVAL_AUTOMATIC_AWAY		=> 'automatic approval using away submission',
	APPROVAL_AUTOMATIC_FORFEIT	=> 'game automatically forfeited due to lack of score submission',
];

$features['attendance'] = [
	ATTENDANCE_ATTENDING	=> 'Attending',
	ATTENDANCE_ABSENT		=> 'Absent',
	ATTENDANCE_UNKNOWN		=> 'Unknown',
	ATTENDANCE_INVITED		=> 'Invited',
	ATTENDANCE_AVAILABLE	=> 'Available',
	ATTENDANCE_NO_SHOW		=> 'No Show',
];

$features['attendance_alt'] = [
	ATTENDANCE_ATTENDING	=> 'Y',
	ATTENDANCE_ABSENT		=> 'N',
	ATTENDANCE_UNKNOWN		=> '?',
	ATTENDANCE_INVITED		=> 'I',
	ATTENDANCE_AVAILABLE	=> 'A',
	ATTENDANCE_NO_SHOW		=> 'X',
];

$features['attendance_verb'] = [
	ATTENDANCE_ATTENDING	=> 'attending',
	ATTENDANCE_ABSENT		=> 'absent for',
	ATTENDANCE_UNKNOWN		=> 'unknown/undecided for',
	ATTENDANCE_INVITED		=> 'invited to sub for',
	ATTENDANCE_AVAILABLE	=> 'available to sub for',
	ATTENDANCE_NO_SHOW		=> 'a no-show for',
];

$features['event_attendance_verb'] = [
	ATTENDANCE_ATTENDING	=> 'attending',
	ATTENDANCE_ABSENT		=> 'absent for',
	ATTENDANCE_UNKNOWN		=> 'unknown/undecided for',
	ATTENDANCE_INVITED		=> 'invited to attend',
	ATTENDANCE_AVAILABLE	=> 'available to attend',
	ATTENDANCE_NO_SHOW		=> 'a no-show for',
];

$features['event_connection'] = [
	EVENT_PREDECESSOR => 'predecessor',
	EVENT_SUCCESSOR => 'successor',
	EVENT_ALTERNATE => 'alternate',
];

$features['visibility'] = [
	VISIBILITY_PRIVATE => 'Private',
	VISIBILITY_CAPTAINS => 'Captains',
	VISIBILITY_TEAM => 'Team',
	VISIBILITY_PUBLIC => 'Public',
	VISIBILITY_ADMIN => 'Admins',
	VISIBILITY_COORDINATOR => 'Coordinators',
];

// Percent likelihood that a notice will be shown, if there is one to show
$features['notice_frequency'] = 20;

// List of colours to use for automatically-created teams
$features['automatic_team_colours'] = [
	'Black',
	'White',
	'Red',
	'Blue',
	'Yellow',
	'Green',
	'Purple',
	'Orange',
];

$features['schedule_type'] = [
	'roundrobin' => SCHEDULE_TYPE_LEAGUE,
	'ratings_ladder' => SCHEDULE_TYPE_LEAGUE,
	'competition' => SCHEDULE_TYPE_LEAGUE,
	'tournament' => SCHEDULE_TYPE_TOURNAMENT,
	'none' => SCHEDULE_TYPE_NONE,
];

// The full list of options: 'Paid', 'Deposit', 'Partial', 'Pending', 'Reserved', 'Unpaid', 'Waiting', 'Cancelled'
$features['registration_paid'] = ['Paid', 'Deposit', 'Partial', 'Pending'];
$features['registration_unpaid'] = ['Deposit', 'Partial', 'Pending', 'Reserved', 'Unpaid', 'Waiting'];
$features['registration_delinquent'] = ['Deposit', 'Partial', 'Pending', 'Reserved', 'Unpaid'];
$features['registration_none_paid'] = ['Pending', 'Reserved', 'Unpaid', 'Waiting'];
$features['registration_some_paid'] = ['Paid', 'Deposit', 'Partial'];
$features['registration_reserved'] = ['Paid', 'Deposit', 'Partial', 'Pending', 'Reserved'];
$features['registration_not_reserved'] = ['Unpaid', 'Waiting', 'Cancelled'];
$features['registration_cancelled'] = ['Cancelled'];

$features['payment_payment'] = ['Full', 'Deposit', 'Installment', 'Remaining Balance', 'Transfer'];

// MIME definitions for document types that CakePHP doesn't support
$features['new_mime_types'] = [
	'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
	'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
	'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
	'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
	'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
	'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
	'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
];

// Extensions that we want to send to the browser instead of downloading
$features['no_download_extensions'] = [
	'html', 'htm', 'txt', 'pdf',
	'bmp', 'gif', 'jpe', 'jpeg', 'jpg', 'png', 'tif', 'tiff',
];

// Default demographic ranges per Ultimate Canada. Can be overridden on a per-sport basis if required.
// The values are the lower end of each range. 0 is assumed to be the bottom of the first range, so
// this will have buckets for 0-12, 13-17, etc.
$features['demographic_ranges'] = [
	13,
	18,
	30,
	41,
	55,
];

if (file_exists(ZULURU_CONFIG . 'features_custom.php')) {
	include(ZULURU_CONFIG . 'features_custom.php');
}

return $features;
