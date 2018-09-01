<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * IncidentsFixture
 *
 */
class IncidentsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'incidents'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'team_id' => TEAM_ID_RED,
				'type' => 'Lorem ipsum dolor sit amet',
				'details' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			],
		];

		parent::init();
	}

}
