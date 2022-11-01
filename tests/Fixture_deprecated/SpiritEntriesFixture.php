<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SpiritEntriesFixture
 *
 */
class SpiritEntriesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'spirit_entries'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'created_team_id' => TEAM_ID_RED,
				'team_id' => TEAM_ID_YELLOW,
				'game_id' => GAME_ID_LADDER_FINALIZED_HOME_WIN,
				'person_id' => PERSON_ID_CAPTAIN,
				'entered_sotg' => 10,
				'score_entry_penalty' => 0,
				'q1' => 2,
				'q2' => 2,
				'q3' => 2,
				'q4' => 2,
				'q5' => 2,
				'q6' => 0,
				'q7' => 0,
				'q8' => 0,
				'q9' => 0,
				'q10' => 0,
				'comments' => '',
				'highlights' => '',
				'most_spirited_id' => PERSON_ID_PLAYER,
			],
			[
				'created_team_id' => TEAM_ID_YELLOW,
				'team_id' => TEAM_ID_RED,
				'game_id' => GAME_ID_LADDER_FINALIZED_HOME_WIN,
				'person_id' => PERSON_ID_CAPTAIN2,
				'entered_sotg' => 12,
				'score_entry_penalty' => 0,
				'q1' => 2,
				'q2' => 2,
				'q3' => 3,
				'q4' => 2,
				'q5' => 3,
				'q6' => 0,
				'q7' => 0,
				'q8' => 0,
				'q9' => 0,
				'q10' => 0,
				'comments' => '',
				'highlights' => 'Great game',
				'most_spirited_id' => null,
			],
			[
				'created_team_id' => TEAM_ID_RED,
				'team_id' => TEAM_ID_BLUE,
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'person_id' => PERSON_ID_CAPTAIN,
				'entered_sotg' => 10,
				'score_entry_penalty' => 0,
				'q1' => 2,
				'q2' => 2,
				'q3' => 2,
				'q4' => 2,
				'q5' => 2,
				'q6' => 0,
				'q7' => 0,
				'q8' => 0,
				'q9' => 0,
				'q10' => 0,
				'comments' => '',
				'highlights' => '',
				'most_spirited_id' => PERSON_ID_PLAYER,
			],
			[
				'created_team_id' => TEAM_ID_BLUE,
				'team_id' => TEAM_ID_RED,
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'person_id' => PERSON_ID_CAPTAIN2,
				'entered_sotg' => 12,
				'score_entry_penalty' => 0,
				'q1' => 2,
				'q2' => 2,
				'q3' => 3,
				'q4' => 2,
				'q5' => 3,
				'q6' => 0,
				'q7' => 0,
				'q8' => 0,
				'q9' => 0,
				'q10' => 0,
				'comments' => '',
				'highlights' => 'Great game',
				'most_spirited_id' => null,
			],
			[
				'created_team_id' => TEAM_ID_GREEN,
				'team_id' => TEAM_ID_YELLOW,
				'game_id' => GAME_ID_LADDER_MISMATCHED_SCORES,
				'person_id' => null,
				'entered_sotg' => 8,
				'score_entry_penalty' => 0,
				'q1' => 1,
				'q2' => 2,
				'q3' => 2,
				'q4' => 2,
				'q5' => 1,
				'q6' => 0,
				'q7' => 0,
				'q8' => 0,
				'q9' => 0,
				'q10' => 0,
				'comments' => '',
				'highlights' => '',
				'most_spirited_id' => null,
			],
			[
				'created_team_id' => TEAM_ID_YELLOW,
				'team_id' => TEAM_ID_GREEN,
				'game_id' => GAME_ID_LADDER_MISMATCHED_SCORES,
				'person_id' => null,
				'entered_sotg' => 9,
				'score_entry_penalty' => 0,
				'q1' => 2,
				'q2' => 2,
				'q3' => 1,
				'q4' => 1,
				'q5' => 2,
				'q6' => 0,
				'q7' => 0,
				'q8' => 0,
				'q9' => 0,
				'q10' => 0,
				'comments' => 'Got a little chippy in the second half',
				'highlights' => '',
				'most_spirited_id' => null,
			],
			[
				'created_team_id' => TEAM_ID_RED,
				'team_id' => TEAM_ID_GREEN,
				'game_id' => GAME_ID_LADDER_HOME_SCORE_ONLY,
				'person_id' => null,
				'entered_sotg' => 9,
				'score_entry_penalty' => 0,
				'q1' => 2,
				'q2' => 2,
				'q3' => 1,
				'q4' => 1,
				'q5' => 2,
				'q6' => 0,
				'q7' => 0,
				'q8' => 0,
				'q9' => 0,
				'q10' => 0,
				'comments' => '',
				'highlights' => '',
				'most_spirited_id' => null,
			],
		];

		if (!defined('SPIRIT_ID_LADDER_FINALIZED_HOME')) {
			$i = 0;
			define('SPIRIT_ID_LADDER_FINALIZED_HOME_WIN_HOME', ++$i);
			define('SPIRIT_ID_LADDER_FINALIZED_HOME_WIN_AWAY', ++$i);
			define('SPIRIT_ID_LADDER_MATCHED_SCORES_HOME', ++$i);
			define('SPIRIT_ID_LADDER_MATCHED_SCORES_AWAY', ++$i);
			define('SPIRIT_ID_LADDER_MISMATCHED_SCORES_HOME', ++$i);
			define('SPIRIT_ID_LADDER_MISMATCHED_SCORES_AWAY', ++$i);
			define('SPIRIT_ID_LADDER_HOME_SCORE_ONLY_HOME', ++$i);
		}

		parent::init();
	}

}
