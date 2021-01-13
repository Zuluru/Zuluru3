<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScoreEntriesFixture
 *
 */
class ScoreEntriesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'score_entries'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'team_id' => TEAM_ID_RED,
				'game_id' => GAME_ID_LADDER_FINALIZED_HOME_WIN,
				'person_id' => PERSON_ID_CAPTAIN,
				'score_for' => 17,
				'score_against' => 5,
				'created' => (new FrozenDate('first Monday of June'))->addDay(),
				'status' => 'normal',
				'modified' => (new FrozenDate('first Monday of June'))->addDay(),
				'home_carbon_flip' => 1,
				'women_present' => null,
			],
			[
				'team_id' => TEAM_ID_YELLOW,
				'game_id' => GAME_ID_LADDER_FINALIZED_HOME_WIN,
				'person_id' => PERSON_ID_CAPTAIN4,
				'score_for' => 5,
				'score_against' => 17,
				'created' => (new FrozenDate('first Monday of June'))->addDay(),
				'status' => 'normal',
				'modified' => (new FrozenDate('first Monday of June'))->addDay(),
				'home_carbon_flip' => 1,
				'women_present' => null,
			],
			[
				'team_id' => TEAM_ID_RED,
				'game_id' => GAME_ID_LADDER_HOME_DEFAULT,
				'person_id' => PERSON_ID_CAPTAIN,
				'score_for' => 0,
				'score_against' => 6,
				'created' => (new FrozenDate('first Monday of June'))->addDay(),
				'status' => 'home_default',
				'modified' => (new FrozenDate('first Monday of June'))->addDay(),
				'home_carbon_flip' => 1,
				'women_present' => null,
			],
			[
				'team_id' => TEAM_ID_BLUE,
				'game_id' => GAME_ID_LADDER_AWAY_DEFAULT,
				'person_id' => PERSON_ID_CAPTAIN2,
				'score_for' => 6,
				'score_against' => 0,
				'created' => (new FrozenDate('first Monday of June'))->addDay(),
				'status' => 'away_default',
				'modified' => (new FrozenDate('first Monday of June'))->addDay(),
				'home_carbon_flip' => 1,
				'women_present' => null,
			],
			[
				'team_id' => TEAM_ID_RED,
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'person_id' => PERSON_ID_CAPTAIN,
				'score_for' => 17,
				'score_against' => 12,
				'created' => (new FrozenDate('first Monday of June'))->addDay(),
				'status' => 'normal',
				'modified' => (new FrozenDate('first Monday of June'))->addDay(),
				'home_carbon_flip' => 1,
				'women_present' => null,
			],
			[
				'team_id' => TEAM_ID_BLUE,
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'person_id' => PERSON_ID_CAPTAIN2,
				'score_for' => 12,
				'score_against' => 17,
				'created' => (new FrozenDate('first Monday of June'))->addDay(),
				'status' => 'normal',
				'modified' => (new FrozenDate('first Monday of June'))->addDay(),
				'home_carbon_flip' => 1,
				'women_present' => null,
			],
			[
				'team_id' => TEAM_ID_GREEN,
				'game_id' => GAME_ID_LADDER_MISMATCHED_SCORES,
				'person_id' => PERSON_ID_CAPTAIN3,
				'score_for' => 15,
				'score_against' => 14,
				'created' => (new FrozenDate('first Monday of June'))->addDay(),
				'status' => 'normal',
				'modified' => (new FrozenDate('first Monday of June'))->addDay(),
				'home_carbon_flip' => 1,
				'women_present' => null,
			],
			[
				'team_id' => TEAM_ID_YELLOW,
				'game_id' => GAME_ID_LADDER_MISMATCHED_SCORES,
				'person_id' => PERSON_ID_CAPTAIN4,
				'score_for' => 13,
				'score_against' => 15,
				'created' => (new FrozenDate('first Monday of June'))->addDay(),
				'status' => 'normal',
				'modified' => (new FrozenDate('first Monday of June'))->addDay(),
				'home_carbon_flip' => 1,
				'women_present' => null,
			],
			[
				'team_id' => TEAM_ID_RED,
				'game_id' => GAME_ID_LADDER_HOME_SCORE_ONLY,
				'person_id' => PERSON_ID_CAPTAIN,
				'score_for' => 5,
				'score_against' => 4,
				'created' => new FrozenDate('second Monday of June'),
				'status' => 'normal',
				'modified' => new FrozenDate('second Monday of June'),
				'home_carbon_flip' => 1,
				'women_present' => null,
			],
		];

		if (!defined('SCORE_ID_LADDER_FINALIZED_HOME')) {
			$i = 0;
			define('SCORE_ID_LADDER_FINALIZED_HOME', ++$i);
			define('SCORE_ID_LADDER_FINALIZED_AWAY', ++$i);
			define('SCORE_ID_LADDER_HOME_DEFAULT_HOME', ++$i);
			define('SCORE_ID_LADDER_AWAY_DEFAULT_AWAY', ++$i);
			define('SCORE_ID_LADDER_MATCHED_SCORES_HOME', ++$i);
			define('SCORE_ID_LADDER_MATCHED_SCORES_AWAY', ++$i);
			define('SCORE_ID_LADDER_MISMATCHED_SCORES_HOME', ++$i);
			define('SCORE_ID_LADDER_MISMATCHED_SCORES_AWAY', ++$i);
			define('SCORE_ID_LADDER_HOME_SCORE_ONLY_HOME', ++$i);
		}

		parent::init();
	}

}
