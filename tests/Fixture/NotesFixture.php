<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * NotesFixture
 *
 */
class NotesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'notes'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'team_id' => TEAM_ID_RED,
				'person_id' => null,
				'game_id' => null,
				'field_id' => null,
				'visibility' => VISIBILITY_ADMIN,
				'created_team_id' => null,
				'created_person_id' => PERSON_ID_ADMIN,
				'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => null,
				'person_id' => PERSON_ID_PLAYER,
				'game_id' => null,
				'field_id' => null,
				'visibility' => VISIBILITY_ADMIN,
				'created_team_id' => null,
				'created_person_id' => PERSON_ID_ADMIN,
				'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => null,
				'person_id' => null,
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'field_id' => null,
				'visibility' => VISIBILITY_TEAM,
				'created_team_id' => TEAM_ID_RED,
				'created_person_id' => PERSON_ID_CAPTAIN,
				'note' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
		];

		parent::init();
	}

}
