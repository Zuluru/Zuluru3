<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * GamesAllstarsFixture
 *
 */
class GamesAllstarsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'games_allstars'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'score_entry_id' => 1,
				'person_id' => PERSON_ID_ADMIN,
				'team_id' => TEAM_ID_RED,
			],
		];

		parent::init();
	}

}
