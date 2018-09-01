<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * StatsFixture
 *
 */
class StatsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'stats'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'team_id' => TEAM_ID_RED,
				'person_id' => PERSON_ID_ADMIN,
				'stat_type_id' => 1,
				'value' => 1,
			],
		];

		parent::init();
	}

}
