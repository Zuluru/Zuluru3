<?php
namespace App\Test\Fixture_deprecated;

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
	public function init(): void {
		$this->records = [
			[
				'game_id' => GAME_ID_THURSDAY_ROUND_ROBIN,
				'team_id' => TEAM_ID_CHICKADEES,
				'person_id' => PERSON_ID_CAPTAIN,
				'stat_type_id' => 1,
				'value' => 1,
			],
		];

		parent::init();
	}

}
