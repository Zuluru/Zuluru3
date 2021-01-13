<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventsFixture
 *
 */
class EventsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'events'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Membership',
				'description' => 'Membership registration',
				'event_type_id' => EVENT_TYPE_ID_MEMBERSHIP,
				'open' => FrozenTime::now()->startOfYear(),
				'close' => FrozenTime::now()->endOfYear(),
				'open_cap' => -1,
				'women_cap' => -1,
				'multiple' => false,
				'questionnaire_id' => null,
				'custom' => serialize([
					'membership_begins' => FrozenDate::now()->startOfYear(),
					'membership_ends' => FrozenDate::now()->endOfYear(),
					'membership_type' => 'full',
				]),
				'division_id' => null,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Team',
				'description' => 'Team registration',
				'event_type_id' => EVENT_TYPE_ID_TEAMS_FOR_LEAGUES,
				'open' => new FrozenDate('first Monday of April'),
				'close' => new FrozenDate('second Friday of May'),
				'open_cap' => 2,
				'women_cap' => -2,
				'multiple' => false,
				'questionnaire_id' => 1,
				'custom' => serialize([
					'level_of_play' => 'Competitive',
					'ask_status' => true,
					'ask_attendance' => true,
				]),
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Individual Monday',
				'description' => 'Individual registration',
				'event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES,
				'open' => (new FrozenDate('last Friday of April'))->addWeekday(),
				'close' => new FrozenDate('second Friday of May'),
				'open_cap' => 1,
				'women_cap' => 1,
				'multiple' => false,
				'questionnaire_id' => null,
				'custom' => serialize([
					'level_of_play' => 'Intermediate',
				]),
				'division_id' => DIVISION_ID_MONDAY_LADDER,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Individual Tuesday',
				'description' => 'Individual registration',
				'event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES,
				'open' => (new FrozenDate('last Friday of April'))->addWeekday(),
				'close' => new FrozenDate('second Friday of May'),
				'open_cap' => 1,
				'women_cap' => 1,
				'multiple' => false,
				'questionnaire_id' => null,
				'custom' => serialize([
					'level_of_play' => 'Intermediate',
				]),
				'division_id' => DIVISION_ID_TUESDAY_ROUND_ROBIN,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Individual Thursday',
				'description' => 'Individual registration',
				'event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES,
				'open' => (new FrozenDate('last Friday of April'))->addWeekday(),
				'close' => new FrozenDate('second Friday of May'),
				'open_cap' => 1,
				'women_cap' => 1,
				'multiple' => false,
				'questionnaire_id' => null,
				'custom' => serialize([
					'level_of_play' => 'Intermediate',
				]),
				'division_id' => DIVISION_ID_THURSDAY_ROUND_ROBIN,
				'affiliate_id' => AFFILIATE_ID_CLUB,
			],
			[
				'name' => 'Individual Sub',
				'description' => 'Individual registration',
				'event_type_id' => EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES,
				'open' => new FrozenDate('first Monday of April'),
				'close' => new FrozenDate('last Friday of April'),
				'open_cap' => 1,
				'women_cap' => 1,
				'multiple' => false,
				'questionnaire_id' => null,
				'custom' => serialize([
					'level_of_play' => 'Intermediate',
				]),
				'division_id' => null,
				'affiliate_id' => AFFILIATE_ID_SUB,
			],
		];

		if (!defined('EVENT_ID_MEMBERSHIP')) {
			$i = 0;
			define('EVENT_ID_MEMBERSHIP', ++$i);
			define('EVENT_ID_LEAGUE_TEAM', ++$i);
			define('EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY', ++$i);
			define('EVENT_ID_LEAGUE_INDIVIDUAL_TUESDAY', ++$i);
			define('EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY', ++$i);
			define('EVENT_ID_LEAGUE_INDIVIDUAL_SUB', ++$i);
		}

		parent::init();
	}

}
