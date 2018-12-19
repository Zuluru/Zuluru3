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

if (!function_exists('App\Config\make_options')) {
	function make_option($value) {
		if (is_numeric($value)) {
			return $value;
		} else {
			return __($value);
		}
	}

	function make_options($values) {
		if (empty($values)) {
			return [];
		}
		return array_combine($values, array_map('App\Config\make_option', $values));
	}

	function make_human_options($values) {
		if (empty($values)) {
			return [];
		}
		$human = array_map('Cake\\Utility\\Inflector::humanize', $values);
		$human = array_map('__', $human);
		return array_combine($values, $human);
	}
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

	'gender' => make_options([
		'Woman',
		'Man',
		'Trans',
		'Self-defined',
		'Prefer not to say',
	]),

	// The female one must always go first, here and above
	'gender_binary' => ['Woman', 'Man'],

	'roster_designation' => make_options([
		'Woman',
		'Open',
	]),

	'shirt_size' => make_options([
		'Womens XSmall',
		'Womens Small',
		'Womens Medium',
		'Womens Large',
		'Womens XLarge',
		'Mens Small',
		'Mens Medium',
		'Mens Large',
		'Mens XLarge',
		'Mens XXLarge',
		'Youth Small',
		'Youth Medium',
		'Youth Large',
	]),

	'record_status' => make_options([
		'new',
		'inactive',
		'active',
		'locked',
	]),

	'sport' => make_human_options([
		'baseball',
		'basketball',
		'cricket',
		'crossfit',
		'dodgeball',
		'football',
		'hockey',
		'rugby',
		'soccer',
		'ultimate',
		'volleyball',
	]),

	'surface' => make_human_options([
		'grass',
		'turf',
		'sand',
		'dirt',
		'clay',
		'hardwood',
		'rubber',
		'urethane',
		'concrete',
		'asphalt',
		'ice',
		'snow',
	]),

	'sotg_display' => make_human_options([
		'symbols_only', // admin gets to see the actual score
		'coordinator_only', // admin and coordinator get to see the actual score
		'numeric', // everyone gets to see the actual score
		'all', // everyone gets to see the actual score
	]),

	'tie_breaker' => [
		'hth' => __('Head-to-head'),
		'hthpm' => __('Head-to-head plus-minus'),
		'pm' => __('Plus-minus'),
		'gf' => __('Goals for'),
		'win' => __('Wins'),
		'loss' => __('Losses'),
		'cf' => __('Carbon flip'),
		'spirit' => __('Spirit'),
	],

	'allstar' => make_options([
		'never',
		'optional',
		'always',
	]),

	'allstar_from' => make_options([
		'opponent',
		'submitter',
	]),

	'most_spirited' => make_options([
		'never',
		'optional',
		'always',
	]),

	'stat_tracking' => make_options([
		'never',
		'optional',
		'always',
	]),

	'online_payment' => [
		ONLINE_FULL_PAYMENT => __('Require the full amount to be paid online'),
		ONLINE_MINIMUM_DEPOSIT => __('Require a minimum deposit to be paid online, but allow full payment'),
		ONLINE_SPECIFIC_DEPOSIT => __('Either a specific deposit or the full amount may be paid online'),
		ONLINE_DEPOSIT_ONLY => __('Require a specific deposit amount to be paid online, the remainder to be collected offline'),
		ONLINE_NO_MINIMUM => __('Allow any amount, including zero, to be paid online, the remainder (if any) to be collected offline'),
		ONLINE_NO_PAYMENT => __('No online payments will be allowed, the entire amount will be collected offline'),
	],

	'payment' => make_options([
		'Unpaid',
		'Reserved',
		'Pending',
		'Deposit',
		'Partial',
		'Paid',
		'Cancelled',
		'Waiting',
	]),

	'payment_method' => make_options([
		'Online',
		'Credit Card',
		'Cheque',
		'Electronic Funds Transfer',
		'Cash',
		'Money Order',
		'Other',
		'Credit Redeemed',
	]),

	'incident_types' => make_options([
		__(Configure::read('UI.field_cap')) . ' condition',
		'Injury',
		'Rules disagreement',
		'Illegal Substitution',
		'Escalated incident',
		'Other',
	]),

	// If additions are made to this, they must also be reflected in features.php
	'season' => make_options([
		'None',
		'Winter',
		'Winter Indoor',
		'Spring',
		'Spring Indoor',
		'Summer',
		'Summer Indoor',
		'Fall',
		'Fall Indoor',
	]),

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

	'game_status' => make_human_options([
		'normal',
		'in_progress',
		'home_default',
		'away_default',
		'rescheduled',
		'cancelled',
		'forfeit',
	]),

	'game_lengths' => make_options([
		0,
		15,
		30,
		45,
		60,
		75,
		90,
		105,
		120,
	]),

	'game_buffers' => make_options([
		0,
		5,
		10,
		15,
	]),

	'field_rating' => [
		'A' => 'A',
		'B' => 'B',
		'C' => 'C',
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

	'units' => make_options([
		'Imperial',
		'Metric',
	]),

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

	'question_types' => make_options([
		'radio',
		'select',
		'checkbox',
		'text',
		'textarea',
		'group_start',
		'group_end',
		'description',
		'label',
	]),

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

	// List of available payment providers
	'payment_provider' => [
		'chase' => __('Chase Paymentech'),
		'moneris' => __('Moneris'),
		'paypal' => __('Paypal'),
	],

	// List of available invoice outputs
	'invoice' => [
		'invoice' => __('Standard'),
	],
];

$options['options']['round'] = make_options(range(1, 5));
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
