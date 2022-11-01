<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScoreDetailStatsFixture
 *
 */
class ScoreDetailStatsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'score_detail_stats'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'score_detail_id' => 1,
				'person_id' => PERSON_ID_ADMIN,
				'stat_type_id' => 1
			],
		];

		parent::init();
	}

}
