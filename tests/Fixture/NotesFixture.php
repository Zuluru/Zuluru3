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
				'note' => 'Admin note from admin about team Red.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => TEAM_ID_RED,
				'person_id' => null,
				'game_id' => null,
				'field_id' => null,
				'visibility' => VISIBILITY_COORDINATOR,
				'created_team_id' => null,
				'created_person_id' => PERSON_ID_ADMIN,
				'note' => 'Coordinator note from admin about team Red.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => TEAM_ID_RED,
				'person_id' => null,
				'game_id' => null,
				'field_id' => null,
				'visibility' => VISIBILITY_TEAM,
				'created_team_id' => null,
				'created_person_id' => PERSON_ID_CAPTAIN,
				'note' => 'Team note from captain about team Red.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => TEAM_ID_RED,
				'person_id' => null,
				'game_id' => null,
				'field_id' => null,
				'visibility' => VISIBILITY_TEAM,
				'created_team_id' => null,
				'created_person_id' => PERSON_ID_PLAYER,
				'note' => 'Team note from player about team Red.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => TEAM_ID_RED,
				'person_id' => null,
				'game_id' => null,
				'field_id' => null,
				'visibility' => VISIBILITY_PRIVATE,
				'created_team_id' => null,
				'created_person_id' => PERSON_ID_VISITOR,
				'note' => 'Team note from player about team Red.',
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
				'note' => 'Admin note from admin about player.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => null,
				'person_id' => PERSON_ID_PLAYER,
				'game_id' => null,
				'field_id' => null,
				'visibility' => VISIBILITY_PRIVATE,
				'created_team_id' => null,
				'created_person_id' => PERSON_ID_CAPTAIN,
				'note' => 'Private note from captain about player.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => null,
				'person_id' => PERSON_ID_PLAYER,
				'game_id' => null,
				'field_id' => null,
				'visibility' => VISIBILITY_PRIVATE,
				'created_team_id' => null,
				'created_person_id' => PERSON_ID_PLAYER,
				'note' => 'Private note from player about player.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => null,
				'person_id' => PERSON_ID_PLAYER,
				'game_id' => null,
				'field_id' => null,
				'visibility' => VISIBILITY_PRIVATE,
				'created_team_id' => null,
				'created_person_id' => PERSON_ID_VISITOR,
				'note' => 'Private note from visitor about player.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => null,
				'person_id' => null,
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'field_id' => null,
				'visibility' => VISIBILITY_ADMIN,
				'created_team_id' => TEAM_ID_RED,
				'created_person_id' => PERSON_ID_ADMIN,
				'note' => 'Admin note from admin about game.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => null,
				'person_id' => null,
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'field_id' => null,
				'visibility' => VISIBILITY_COORDINATOR,
				'created_team_id' => TEAM_ID_RED,
				'created_person_id' => PERSON_ID_ADMIN,
				'note' => 'Coordinator note from admin about game.',
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
				'note' => 'Team note from captain about game.',
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
				'created_person_id' => PERSON_ID_PLAYER,
				'note' => 'Team note from player about game.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'team_id' => null,
				'person_id' => null,
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'field_id' => null,
				'visibility' => VISIBILITY_PRIVATE,
				'created_team_id' => TEAM_ID_RED,
				'created_person_id' => PERSON_ID_VISITOR,
				'note' => 'Private note from visitor about game.',
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
		];

		if (!defined('NOTE_ID_TEAM_RED_ADMIN')) {
			$i = 0;
			// Monday week 1
			define('NOTE_ID_TEAM_RED_ADMIN', ++$i);
			define('NOTE_ID_TEAM_RED_COORDINATOR', ++$i);
			define('NOTE_ID_TEAM_RED_CAPTAIN', ++$i);
			define('NOTE_ID_TEAM_RED_PLAYER', ++$i);
			define('NOTE_ID_TEAM_RED_VISITOR', ++$i);
			define('NOTE_ID_PERSON_PLAYER_ADMIN', ++$i);
			define('NOTE_ID_PERSON_PLAYER_CAPTAIN', ++$i);
			define('NOTE_ID_PERSON_PLAYER_PLAYER', ++$i);
			define('NOTE_ID_PERSON_PLAYER_VISITOR', ++$i);
			define('NOTE_ID_GAME_LADDER_MATCHED_SCORES_ADMIN', ++$i);
			define('NOTE_ID_GAME_LADDER_MATCHED_SCORES_COORDINATOR', ++$i);
			define('NOTE_ID_GAME_LADDER_MATCHED_SCORES_CAPTAIN', ++$i);
			define('NOTE_ID_GAME_LADDER_MATCHED_SCORES_PLAYER', ++$i);
			define('NOTE_ID_GAME_LADDER_MATCHED_SCORES_VISITOR', ++$i);
		}

		parent::init();
	}

}
