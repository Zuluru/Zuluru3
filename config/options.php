<?php
/**
 * Configure various select options. These are used to populate various
 * SELECT inputs, and for validating those inputs.
 *
 * Changing the language of any of these should be done through the
 * internationalization methods at output, leaving English in the
 * database. This way, a single site can support multiple languages.
 *
 * If you have any local configuration customizations, adjust the $options
 * array by adding, altering or unsetting values through a file called
 * options_custom.php (which you must create).
 */
namespace App\Config;

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$category_types = [
	'Leagues' => __('Leagues'),
];
if (Configure::read('feature.tasks')) {
	$category_types['Tasks'] = __('Tasks');
}

$options['options'] = [
	'enable' => [
		false => __('Disabled'),
		true => __('Enabled')
	],

	'access_required' => [
		PROFILE_USER_UPDATE => __('User can update'),
		PROFILE_ADMIN_UPDATE => __('Admin can update'),
	],

	'access_optional' => [
		PROFILE_USER_UPDATE => __('User can update'),
		PROFILE_ADMIN_UPDATE => __('Admin can update'),
		PROFILE_DISABLED => __('Disabled entirely'),
	],

	'access_registration' => [
		PROFILE_USER_UPDATE => __('User can update'),
		PROFILE_ADMIN_UPDATE => __('Admin can update'),
		PROFILE_REGISTRATION => __('Updated during event registration'),
		PROFILE_DISABLED => __('Disabled entirely'),
	],

	'ages' => [
		'Youth' => __('Youth only'),
		'Adult' => __('Adult only'),
		'Both' => __('Both youth and adult'),
	],

	'modes' => [
		'Leagues' => __('Leagues only'),
		'Tournaments' => __('Tournaments/competitions only'),
		'Both' => __('Both leagues and tournaments/competitions'),
	],

	'genders' => [
		'Single' => __('Single-gender only, both women\'s and men\'s'),
		'Women' => __('Women\'s only'),
		'Men' => __('Men\'s only'),
		'Co-ed' => __('At least some co-ed where gender ratios are enforced'),
		'Open' => __('Anyone can play without restriction'),
	],

	'gender' => [
		'Woman' => __x('gender', 'Woman'),
		'Man' => __x('gender', 'Man'),
		'Non-Binary' => __x('gender', 'Non-Binary'),
		'Prefer not to say' => __x('gender', 'Prefer not to say'),
		'Prefer to specify' => __x('gender', 'Prefer to specify'),
	],

	// The female one must always go first, here and above
	'roster_designation' => [
		'Woman' => __x('gender', 'Woman'),
		'Open' => __x('gender', 'Open'),
	],

	'shirt_size' => [
		'Womens XSmall' => __('Womens XSmall'),
		'Womens Small' => __('Womens Small'),
		'Womens Medium' => __('Womens Medium'),
		'Womens Large' => __('Womens Large'),
		'Womens XLarge' => __('Womens XLarge'),
		'Womens XXLarge' => __('Womens XXLarge'),
		'Womens XXXLarge' => __('Womens XXXLarge'),
		'Mens Small' => __('Mens Small'),
		'Mens Medium' => __('Mens Medium'),
		'Mens Large' => __('Mens Large'),
		'Mens XLarge' => __('Mens XLarge'),
		'Mens XXLarge' => __('Mens XXLarge'),
		'Mens XXXLarge' => __('Mens XXXLarge'),
		'Youth Small' => __('Youth Small'),
		'Youth Medium' => __('Youth Medium'),
		'Youth Large' => __('Youth Large'),
	],

	'record_status' => [
		'new' => __('new'),
		'inactive' => __('inactive'),
		'active' => __('active'),
		'locked' => __('locked'),
	],

	'sport' => [
		'baseball' => __('Baseball'),
		'basketball' => __('Basketball'),
		'cricket' => __('Cricket'),
		'crossfit' => __('Crossfit'),
		'dodgeball' => __('Dodgeball'),
		'football' => __('Football'),
		'hockey' => __('Hockey'),
		'rugby' => __('Rubgy'),
		'soccer' => __('Soccer'),
		'ultimate' => __('Ultimate'),
		'volleyball' => __('Volleyball'),
	],

	'surface' => [
		'grass' => __('grass'),
		'turf' => __('turf'),
		'sand' => __('sand'),
		'dirt' => __('dirt'),
		'clay' => __('clay'),
		'hardwood' => __('hardwood'),
		'rubber' => __('rubber'),
		'urethane' => __('urethane'),
		'concrete' => __('concrete'),
		'asphalt' => __('asphalt'),
		'ice' => __('ice'),
		'snow' => __('snow'),
	],

	'sotg_display' => [
		'symbols_only' => __('Symbols Only'), // admin gets to see the actual score
		'coordinator_only' => __('Coordinator Only'), // admin and coordinator get to see the actual score
		'numeric' => __('Numeric'), // everyone gets to see the actual score
		'all' => __('All'), // everyone gets to see the actual score
	],

	'tie_breaker' => [
		'hth' => __('Head-to-head'),
		'hthpm' => __('Head-to-head plus-minus'),
		'pm' => __('Plus-minus'),
		'gf' => __('Goals For'),
		'win' => __('Wins'),
		'loss' => __('Losses'),
		'cf' => __('Carbon Flip'),
		'spirit' => __('Spirit'),
	],

	'allstar' => [
		'never' => __('never'),
		'optional' => __('optional'),
		'always' => __('always'),
	],

	'allstar_from' => [
		'opponent' => __('opponent'),
		'submitter' => __('submitter'),
	],

	'most_spirited' => [
		'never' => __('never'),
		'optional' => __('optional'),
		'always' => __('always'),
	],

	'stat_tracking' => [
		'never' => __('never'),
		'optional' => __('optional'),
		'always' => __('always'),
	],

	'online_payment' => [
		ONLINE_FULL_PAYMENT => __('Require the full amount to be paid online'),
		ONLINE_MINIMUM_DEPOSIT => __('Require a minimum deposit to be paid online, but allow full payment'),
		ONLINE_SPECIFIC_DEPOSIT => __('Either a specific deposit or the full amount may be paid online'),
		ONLINE_DEPOSIT_ONLY => __('Require a specific deposit amount to be paid online, the remainder to be collected offline'),
		ONLINE_NO_MINIMUM => __('Allow any amount, including zero, to be paid online, the remainder (if any) to be collected offline'),
		ONLINE_NO_PAYMENT => __('No online payments will be allowed, the entire amount will be collected offline'),
	],

	'payment' => [
		'Unpaid' => __('Unpaid'),
		'Reserved' => __('Reserved'),
		'Pending' => __('Pending'),
		'Deposit' => __('Deposit'),
		'Partial' => __('Partial'),
		'Paid' => __('Paid'),
		'Cancelled' => __('Cancelled'),
		'Waiting' => __('Waiting'),
	],

	'payment_method' => [
		'Online' => __('Online'),
		'Credit Card' => __('Credit Card'),
		'Cheque' => __('Cheque'),
		'Electronic Funds Transfer' => __('Electronic Funds Transfer'),
		'Cash' => __('Cash'),
		'Money Order' => __('Money Order'),
		'Other' => __('Other'),
		'Credit Redeemed' => __('Credit Redeemed'),
	],

	'incident_types' => [
		'Field Condition' => __('{0} condition', Configure::read('UI.field_cap')),
		'Injury' => __('Injury'),
		'Rules disagreement' => __('Rules disagreement'),
		// TODO: Fix capitalization different, requires a migration
		'Illegal Substitution' => __('Illegal Substitution'),
		'Escalated incident' => __('Escalated incident'),
		'Other' => __('Other'),
	],

	// If additions are made to this, they must also be reflected in features.php
	'season' => [
		'None' => __('None'),
		'Winter' => __('Winter'),
		'Winter Indoor' => __('Winter Indoor'),
		'Spring' => __('Spring'),
		'Spring Indoor' => __('Spring Indoor'),
		'Summer' => __('Summer'),
		'Summer Indoor' => __('Summer Indoor'),
		'Fall' => __('Fall'),
		'Fall Indoor' => __('Fall Indoor'),
	],

	'skill' => [
		'10' => __('10: High calibre touring player (team was top 4 at nationals)'),
		'9' => __('9: Medium calibre touring player'),
		'8' => __('8: Key player in competitive league, or lower calibre touring player'),
		'7' => __('7: Competitive league player, minimal/no touring experience'),
		'6' => __('6: Key player in intermediate league, or lower player in competitive league'),
		'5' => __('5: Comfortable in intermediate league'),
		'4' => __('4: Key player in recreational league, or lower player in intermediate league'),
		'3' => __('3: Comfortable in recreational league'),
		'2' => __('2: Beginner, minimal experience but athletic with sports background'),
		'1' => __('1: Absolute Beginner'),
	],

	'roster_methods' => [
		'invite' => __('Players are invited and must accept'),
		'add' => __('Players are added directly'),
	],

	'division_position' => [
		'coordinator' => __('Coordinator'),
	],

	'game_status' => [
		'normal' => __('Normal'),
		'in_progress' => __('In Progress'),
		'home_default' => __('Home Default'),
		'away_default' => __('Away Default'),
		'rescheduled' => __('Rescheduled'),
		'cancelled' => __('Cancelled'),
		'forfeit' => __('Forfeit'),
	],

	'game_lengths' => [
		0 => 0,
		15 => 15,
		30 => 30,
		45 => 45,
		60 => 60,
		70 => 70,
		75 => 75,
		80 => 80,
		90 => 90,
		105 => 105,
		120 => 120,
	],

	'game_buffers' => [
		0 => 0,
		5 => 5,
		10 => 10,
		15 => 15,
	],

	'field_rating' => [
		'A' => __x('rating', 'A'),
		'B' => __x('rating', 'B'),
		'C' => __x('rating', 'C'),
	],

	'test_payment' => [
		TEST_PAYMENTS_NOBODY => __('Nobody'),
		TEST_PAYMENTS_EVERYBODY => __('Everybody'),
		TEST_PAYMENTS_ADMINS => __('Admins'),
	],

	'currency' => [
		'CAD' => __('Canadian'),
		'USD' => __('USA'),
	],

	'units' => [
		'Imperial' => __('Imperial'),
		'Metric' => __('Metric'),
	],

	'waivers' => [
		'expiry_type' => [
			'fixed_dates' => __('Fixed dates'),
			'elapsed_time' => __('A fixed duration'),
			'event' => __('Duration of the event'),
			'never' => __('Never expires'),
		],
	],

	'date_formats' => [
		'MMM d, yyyy',
		'MMMM d, yyyy',
		'dd/MM/yyyy',
		'yyyy/MM/dd',
	],

	'day_formats' => [
		'EEE MMM d',
		'EEEE MMMM d',
	],

	'time_formats' => [
		'h:mma',
		'HH:mm',
	],

	'question_types' => [
		'radio' => __('Radio'),
		'select' => __('Select'),
		'checkbox' => __('Checkbox'),
		'text' => __('Text'),
		'textarea' => __('Text Area'),
		'group_start' => __('Group Start'),
		'group_end' => __('Group End'),
		'description' => __('Description'),
		'label' => __('Label'),
	],

	'category_types' => $category_types,

	// List of available badge categories
	'category' => [
		'runtime' => __('Run-time determination'),
		'game' => __('Determined by game outcomes'),
		'team' => __('Determined by roster status'),
		'registration' => __('Determined by registrations'),
		'aggregate' => __('Aggregates multiple badges, e.g. "5x"'),
		'nominated' => __('Nominated by anyone (must be approved)'),
		'assigned' => __('Assigned by an admin'),
	],

	'visibility' => [
		BADGE_VISIBILITY_ADMIN => __('Admin only (same locations as high)'),
		BADGE_VISIBILITY_HIGH => __('High (profile, pop-ups, team rosters)'),
		BADGE_VISIBILITY_LOW => __('Low (profile only)'),
	],

	/**
	 * The following options are for components that change the elements
	 * found on certain view and edit pages. Each group must have a base
	 * component class, and each item must have a derived component class.
	 * You can remove (comment out) any options in here that you don't
	 * want available for your leagues, but you can't just add things here
	 * without also adding the implementation to support it.
	 *
	 * The "competition" schedule type is not available by default, as it
	 * applies to a small subset of sports. It is used for anything where
	 * several teams are given a score based on their own performance,
	 * unrelated to anything that the other teams do. Teams may compete at
	 * the same time or not. The winner is the team with the highest (or
	 * lowest) score. Examples include many track & field events, golf, etc.
	 * The "manual" rating calculator is for use with competition divisions.
	 * To enable this type, either uncomment the lines below, or make use of
	 * the options_custom.php file to re-define the list of scheduling types
	 * and rating calculators with these included.
	 */

	// List of available scheduling types
	'schedule_type' => [
		'none' => __('None'),
		'roundrobin' => __('Round Robin'),
		'ratings_ladder' => __('Ratings Ladder'),
		//'competition' => __('Competition'),
		'tournament' => __('Tournament'),
	],

	// List of available rating calculators
	'rating_calculator' => [
		'none' => __('None'),
		'wager' => __('Wager System'),
		'usau_rankings' => __('USA Ultimate Rankings v2'),
		'usau_college' => __('USA Ultimate College'),
		'rri' => __('RRI'),
		'krach' => __('KRACH'),
		'rpi' => __('RPI'),
		'modified_elo' => __('Modified Elo'),
		//'manual' => __('Manual'),
	],

	// List of available spirit questionnaires
	'spirit_questions' => [
		'none' => __('No spirit questionnaire'),
		'wfdf' => __('WFDF standard'),
		'wfdf2' => __('WFDF standard 2014 version'),
		'wfdf2_inclusivity' => __('WFDF standard 2014 version, with inclusivity question'),
		'modified_wfdf' => __('Modified WFDF'),
		'modified_bula' => __('Modified BULA'),
		'team' => __('Leaguerunner original'),
		'ocua_team' => __('Modified Leaguerunner'),
		'suzuki' => __('Sushi Suzuki\'s Alternate'),
	],

	// List of available invoice outputs
	'invoice' => [
		'invoice' => __('Standard'),
	],
];

$options['options']['round'] = array_combine($r = range(1, 5), $r);
$options['options']['games_before_repeat'] = range(0, 9);

$year = FrozenTime::now()->year;
$options['options']['year'] = [
	'started' => ['min' => 1986, 'max' => $year],
	'born' => ['min' => $year - 75, 'max' => $year - 3],
	'event' => ['min' => $year - 1, 'max' => $year + 1],
	'gameslot' => ['min' => $year, 'max' => $year + 1],
];

if (file_exists(ZULURU_CONFIG . 'options_custom.php')) {
	include(ZULURU_CONFIG . 'options_custom.php');
}

return $options;
