<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TeamsFixture
 *
 */
class TeamsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'teams'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init(): void {
		$this->records = [
			[
				'name' => 'Red',
				'division_id' => DIVISION_ID_MONDAY_LADDER_PAST,
				'website' => null,
				'shirt_colour' => 'Red',
				// This doesn't use FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 so as to reduce cross-fixture dependencies
				'home_field_id' => 1,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 0,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => 'redteam',
			],
			[
				'name' => 'Red',
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'website' => null,
				'shirt_colour' => 'Red',
				// This doesn't use FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_1 so as to reduce cross-fixture dependencies
				'home_field_id' => 1,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 3,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => 'redteam',
			],
			[
				'name' => 'Blue',
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'website' => null,
				'shirt_colour' => 'Blue',
				// This doesn't use FIELD_ID_SUNNYBROOK_FIELD_HOCKEY_2 so as to reduce cross-fixture dependencies
				'home_field_id' => 2,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 2,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => 'blueteam',
			],
			[
				'name' => 'Green',
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'website' => null,
				'shirt_colour' => 'Green',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1450,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1450,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 1,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => 'greenteam',
			],
			[
				'name' => 'Yellow',
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'website' => null,
				'shirt_colour' => 'Yellow',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1450,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1450,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 4,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => 'yellowteam',
			],
			[
				'name' => 'Orange',
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'website' => 'https://www.netflix.com/ca/title/70242311',
				'shirt_colour' => 'Orange',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1425,
				'track_attendance' => false,
				'attendance_reminder' => null,
				'attendance_summary' => null,
				'attendance_notification' => null,
				'initial_rating' => 1425,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 5,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => true,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
			[
				'name' => 'Purple',
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'website' => null,
				'shirt_colour' => 'Purple',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1425,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1425,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 6,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
			[
				'name' => 'Black',
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'website' => null,
				'shirt_colour' => 'Black',
				'home_field_id' => null,
				'region_preference_id' => 1,
				'open_roster' => true,
				'rating' => 1400,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1400,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 7,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
			[
				'name' => 'White',
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'website' => null,
				'shirt_colour' => 'White',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1400,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1400,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 8,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
			[
				'name' => 'Red',
				'division_id' => DIVISION_ID_MONDAY_PLAYOFF,
				'website' => null,
				'shirt_colour' => 'Red',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 0,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => 'redteam',
			],
			[
				'name' => 'Maples',
				'division_id' => DIVISION_ID_TUESDAY_ROUND_ROBIN,
				'website' => null,
				'shirt_colour' => 'Green',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 0,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
			[
				'name' => 'Oaks',
				'division_id' => DIVISION_ID_TUESDAY_ROUND_ROBIN,
				'website' => null,
				'shirt_colour' => 'Green',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 0,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
			[
				'name' => 'Chickadees',
				'division_id' => DIVISION_ID_THURSDAY_ROUND_ROBIN,
				'website' => null,
				'shirt_colour' => 'White',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 0,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
			[
				'name' => 'Sparrows',
				'division_id' => DIVISION_ID_THURSDAY_ROUND_ROBIN,
				'website' => null,
				'shirt_colour' => 'Brown',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'initial_seed' => 0,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
			[
				'name' => 'Bears',
				'division_id' => DIVISION_ID_SUNDAY_SUB,
				'website' => null,
				'shirt_colour' => 'Brown',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_SUB,
				'initial_seed' => 0,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
			[
				'name' => 'Lions',
				'division_id' => DIVISION_ID_SUNDAY_SUB,
				'website' => null,
				'shirt_colour' => 'Gold',
				'home_field_id' => null,
				'region_preference_id' => null,
				'open_roster' => false,
				'rating' => 1500,
				'track_attendance' => true,
				'attendance_reminder' => 3,
				'attendance_summary' => 2,
				'attendance_notification' => 1,
				'initial_rating' => 1500,
				'affiliate_id' => AFFILIATE_ID_SUB,
				'initial_seed' => 0,
				'seed' => 0,
				'flickr_user' => null,
				'flickr_set' => null,
				'flickr_ban' => false,
				'logo' => null,
				'short_name' => null,
				'twitter_user' => null,
			],
		];

		if (!defined('TEAM_ID_RED')) {
			$i = 0;
			define('TEAM_ID_RED_PAST', ++$i);
			define('TEAM_ID_RED', ++$i);
			define('TEAM_ID_BLUE', ++$i);
			define('TEAM_ID_GREEN', ++$i);
			define('TEAM_ID_YELLOW', ++$i);
			define('TEAM_ID_ORANGE', ++$i);
			define('TEAM_ID_PURPLE', ++$i);
			define('TEAM_ID_BLACK', ++$i);
			define('TEAM_ID_WHITE', ++$i);
			define('TEAM_ID_RED_PLAYOFF', ++$i);
			define('TEAM_ID_MAPLES', ++$i);
			define('TEAM_ID_OAKS', ++$i);
			define('TEAM_ID_CHICKADEES', ++$i);
			define('TEAM_ID_SPARROWS', ++$i);
			define('TEAM_ID_BEARS', ++$i);
			define('TEAM_ID_LIONS', ++$i);
			// This must always be the last one in the list
			define('TEAM_ID_NEW', ++$i);
		}

		parent::init();
	}

}
