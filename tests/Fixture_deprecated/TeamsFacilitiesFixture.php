<?php
namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TeamsFacilitiesFixture
 *
 */
class TeamsFacilitiesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'teams_facilities'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init(): void {
		$this->records = [
			[
				'team_id' => TEAM_ID_RED,
				'facility_id' => FACILITY_ID_SUNNYBROOK,
				'rank' => 1
			],
		];

		parent::init();
	}

}
