<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BadgesFixture
 *
 */
class BadgesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'badges'];

	/**
	 * Initialize function: Mostly, set up records
	 */

	public function init() {
		$this->records = [
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Active Player',
				'description' => 'This badge indicates a player who is on a team roster for a current or upcoming season.',
				'category' => 'team',
				'handler' => 'player_active',
				'active' => true,
				'visibility' => BADGE_VISIBILITY_HIGH,
				'icon' => 'badge_player',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Member',
				'description' => 'This badge indicates a player who has a current membership.',
				'category' => 'registration',
				'handler' => 'member_registered',
				'active' => false,
				'visibility' => BADGE_VISIBILITY_HIGH,
				'icon' => 'badge_member',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Intro Member',
				'description' => 'This badge indicates a player who has an introductory membership, typically a player new to the sport or the city.',
				'category' => 'registration',
				'handler' => 'member_intro',
				'active' => false,
				'visibility' => BADGE_VISIBILITY_HIGH,
				'icon' => 'badge_intro',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Junior Player',
				'description' => 'This badge indicates a player who is under 18.',
				'category' => 'runtime',
				'handler' => 'junior',
				'active' => false,
				'visibility' => BADGE_VISIBILITY_HIGH,
				'icon' => 'badge_junior',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Past Member',
				'description' => 'This badge denotes someone who had a membership in the past.',
				'category' => 'registration',
				'handler' => 'member_past',
				'active' => false,
				'visibility' => BADGE_VISIBILITY_LOW,
				'icon' => 'badge_past_member',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => '5x Past Member',
				'description' => 'This badge denotes someone who has had at least 5 memberships in the past.',
				'category' => 'aggregate',
				'handler' => '5x5',
				'active' => false,
				'visibility' => BADGE_VISIBILITY_LOW,
				'icon' => 'badge_past_member_5x',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'League Champion',
				'description' => 'This badge is awarded to all regular players on the rosters of teams that have won league playoffs.',
				'category' => 'game',
				'handler' => 'league_champion',
				'active' => true,
				'visibility' => BADGE_VISIBILITY_LOW,
				'icon' => 'badge_champion',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => '5x League Champion',
				'description' => 'This badge is awarded to people who have won five league championships.',
				'category' => 'aggregate',
				'handler' => '7x5',
				'active' => true,
				'visibility' => BADGE_VISIBILITY_LOW,
				'icon' => 'badge_champion_5x',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Hall of Fame',
				'description' => 'This badge is awarded exclusively to those who have been inducted into the Hall of Fame.',
				'category' => 'nominated',
				'handler' => '',
				'active' => true,
				'visibility' => BADGE_VISIBILITY_HIGH,
				'icon' => 'badge_hof',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Volunteer of the Year',
				'description' => 'This badge is awarded to those who have been chosen as volunteer of the year.',
				'category' => 'assigned',
				'handler' => '',
				'active' => false,
				'visibility' => BADGE_VISIBILITY_LOW,
				'icon' => 'badge_voy',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Volunteer of the Month',
				'description' => 'This badge is awarded to those who have been chosen as volunteer of the month.',
				'category' => 'assigned',
				'handler' => '',
				'active' => false,
				'visibility' => BADGE_VISIBILITY_LOW,
				'icon' => 'badge_vom',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Board of Directors',
				'description' => 'This badge is awarded to those who have are currently on the board of directors.',
				'category' => 'assigned',
				'handler' => '',
				'active' => false,
				'visibility' => BADGE_VISIBILITY_LOW,
				'icon' => 'badge_bod',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Red Flag',
				'description' => 'Denotes players under suspension.',
				'category' => 'assigned',
				'handler' => '',
				'active' => true,
				'visibility' => BADGE_VISIBILITY_ADMIN,
				'icon' => 'flag_red',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Yellow Flag',
				'description' => 'Denotes players being monitored for bad behaviour.',
				'category' => 'assigned',
				'handler' => '',
				'active' => true,
				'visibility' => BADGE_VISIBILITY_ADMIN,
				'icon' => 'flag_yellow',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'name' => 'Green Flag',
				'description' => 'Denotes players worthy of some recognition.',
				'category' => 'assigned',
				'handler' => '',
				'active' => true,
				'visibility' => BADGE_VISIBILITY_ADMIN,
				'icon' => 'flag_green',
				'refresh_from' => 0,
			],
			[
				'affiliate_id' => AFFILIATE_ID_SUB,
				'name' => 'Active Player',
				'description' => 'This badge indicates a player who is on a team roster for a current or upcoming season.',
				'category' => 'team',
				'handler' => 'player_active',
				'active' => true,
				'visibility' => BADGE_VISIBILITY_HIGH,
				'icon' => 'badge_player',
				'refresh_from' => 0,
			],
		];

		if (!defined('BADGE_ID_ACTIVE_PLAYER')) {
			$i = 0;
			define('BADGE_ID_ACTIVE_PLAYER', ++$i);
			define('BADGE_ID_MEMBER', ++$i);
			define('BADGE_ID_INTRO_MEMBER', ++$i);
			define('BADGE_ID_JUNIOR_MEMBER', ++$i);
			define('BADGE_ID_PAST_MEMBER', ++$i);
			define('BADGE_ID_5X_PAST_MEMBER', ++$i);
			define('BADGE_ID_CHAMPION', ++$i);
			define('BADGE_ID_5X_CHAMPION', ++$i);
			define('BADGE_ID_HALL_OF_FAME', ++$i);
			define('BADGE_ID_VOLUNTEER_OF_THE_YEAR', ++$i);
			define('BADGE_ID_VOLUNTEER_OF_THE_MONTH', ++$i);
			define('BADGE_ID_BOARD_OF_DIRECTORS', ++$i);
			define('BADGE_ID_RED_FLAG', ++$i);
			define('BADGE_ID_YELLOW_FLAG', ++$i);
			define('BADGE_ID_GREEN_FLAG', ++$i);
			define('BADGE_ID_ACTIVE_PLAYER_SUB', ++$i);
		}

		parent::init();
	}

}
