<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FranchisesTeamsFixture
 *
 */
class FranchisesTeamsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'franchises_teams'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'franchise_id' => FRANCHISE_ID_RED,
				'team_id' => TEAM_ID_RED,
			],
			[
				'franchise_id' => FRANCHISE_ID_RED2,
				'team_id' => TEAM_ID_RED,
			],
			[
				'franchise_id' => FRANCHISE_ID_BLUE,
				'team_id' => TEAM_ID_BLUE,
			],
			[
				'franchise_id' => FRANCHISE_ID_RED,
				'team_id' => TEAM_ID_RED_PLAYOFF,
			],
		];

		parent::init();
	}

}
