<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * LeaguesFixture
 *
 */
class LeaguesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'leagues'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Monday Night',
				'sport' => 'ultimate',
				'season' => 'Summer',
				'open' => (new FrozenDate('first Monday of June'))->subWeeks(52),
				'close' => (new FrozenDate('first Monday of September'))->subWeeks(52),
				'is_open' => false,
				'schedule_attempts' => 100,
				'display_sotg' => 'symbols_only',
				'sotg_questions' => 'team',
				'numeric_sotg' => true,
				'expected_max_score' => 17,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'stat_tracking' => 'always',
				'tie_breaker' => 'win,hth,hthpm,pm,gf,loss',
				'carbon_flip' => false,
			],
			[
				'name' => 'Monday Night',
				'sport' => 'ultimate',
				'season' => 'Summer',
				'open' => new FrozenDate('first Monday of June'),
				'close' => new FrozenDate('first Monday of September'),
				'is_open' => true,
				'schedule_attempts' => 100,
				'display_sotg' => 'symbols_only',
				'sotg_questions' => 'wfdf2',
				'numeric_sotg' => true,
				'expected_max_score' => 17,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'stat_tracking' => 'never',
				'tie_breaker' => 'win,hth,hthpm,pm,gf,loss',
				'carbon_flip' => false,
			],
			[
				'name' => 'Tuesday Night',
				'sport' => 'baseball',
				'season' => 'None',
				'open' => new FrozenDate('first Tuesday of June'),
				'close' => new FrozenDate('first Tuesday of September'),
				'is_open' => true,
				'schedule_attempts' => 100,
				'display_sotg' => 'symbols_only',
				'sotg_questions' => 'none',
				'numeric_sotg' => false,
				'expected_max_score' => 17,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'stat_tracking' => 'never',
				'tie_breaker' => 'win,hth,hthpm,pm,gf,loss',
				'carbon_flip' => true,
			],
			[
				'name' => 'Wednesday Night',
				'sport' => 'baseball',
				'season' => 'None',
				'open' => new FrozenDate('first Wednesday of June'),
				'close' => new FrozenDate('first Wednesday of September'),
				'is_open' => true,
				'schedule_attempts' => 100,
				'display_sotg' => 'symbols_only',
				'sotg_questions' => 'none',
				'numeric_sotg' => false,
				'expected_max_score' => 17,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'stat_tracking' => 'never',
				'tie_breaker' => 'win,hth,hthpm,pm,gf,loss',
				'carbon_flip' => true,
			],
			[
				'name' => 'Thursday Night',
				'sport' => 'ultimate',
				'season' => 'Fall',
				'open' => new FrozenDate('second Thursday of June'),
				'close' => new FrozenDate('first Thursday of September'),
				'is_open' => true,
				'schedule_attempts' => 100,
				'display_sotg' => 'symbols_only',
				'sotg_questions' => 'team',
				'numeric_sotg' => true,
				'expected_max_score' => 17,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'stat_tracking' => 'always',
				'tie_breaker' => 'win,hth,hthpm,pm,gf,loss',
				'carbon_flip' => false,
			],
			[
				'name' => 'Friday Night',
				'sport' => 'ultimate',
				'season' => 'Fall',
				'open' => new FrozenDate('third Friday of June'),
				'close' => new FrozenDate('first Friday of September'),
				'is_open' => true,
				'schedule_attempts' => 100,
				'display_sotg' => 'symbols_only',
				'sotg_questions' => 'team',
				'numeric_sotg' => true,
				'expected_max_score' => 17,
				'affiliate_id' => AFFILIATE_ID_CLUB,
				'stat_tracking' => 'always',
				'tie_breaker' => 'win,hth,hthpm,pm,gf,loss',
				'carbon_flip' => false,
			],
			[
				'name' => 'Sunday',
				'sport' => 'soccer',
				'season' => 'Summer',
				'open' => (new FrozenDate('first Sunday of June')),
				'close' => (new FrozenDate('first Sunday of September')),
				'is_open' => true,
				'schedule_attempts' => 100,
				'display_sotg' => 'coordinator_only',
				'sotg_questions' => 'none',
				'numeric_sotg' => false,
				'expected_max_score' => 5,
				'affiliate_id' => AFFILIATE_ID_SUB,
				'stat_tracking' => 'never',
				'tie_breaker' => 'win,hth,hthpm,pm,gf,loss',
				'carbon_flip' => false,
			],
		];

		if (!defined('LEAGUE_ID_MONDAY')) {
			$i = 0;
			define('LEAGUE_ID_MONDAY_PAST', ++$i);
			define('LEAGUE_ID_MONDAY', ++$i);
			define('LEAGUE_ID_TUESDAY', ++$i);
			define('LEAGUE_ID_WEDNESDAY', ++$i);
			define('LEAGUE_ID_THURSDAY', ++$i);
			define('LEAGUE_ID_FRIDAY', ++$i);
			define('LEAGUE_ID_SUNDAY_SUB', ++$i);
		}

		parent::init();
	}

}
