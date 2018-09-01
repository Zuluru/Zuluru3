<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FieldRankingStatsFixture
 *
 */
class FieldRankingStatsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'field_ranking_stats'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'team_id' => TEAM_ID_RED,
				'rank' => 1,
			],
		];

		parent::init();
	}

}
