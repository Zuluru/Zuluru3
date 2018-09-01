<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * TeamsPeopleFixture
 *
 */
class TeamsPeopleFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $table = 'teams_people';

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'teams_people'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'team_id' => TEAM_ID_RED_PAST,
				'person_id' => PERSON_ID_CAPTAIN,
				'role' => 'captain',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now()->subYear(),
				'number' => 1,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_RED_PAST,
				'person_id' => PERSON_ID_CHILD,
				'role' => 'player',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now()->subYear(),
				'number' => 2,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_RED_PAST,
				'person_id' => PERSON_ID_MANAGER,
				'role' => 'player',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now()->subYear(),
				'number' => 2,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_RED_PAST,
				'person_id' => PERSON_ID_PLAYER,
				'role' => 'player',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now()->subYear(),
				'number' => 2,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_RED,
				'person_id' => PERSON_ID_CAPTAIN,
				'role' => 'captain',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now(),
				'number' => 1,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_RED,
				'person_id' => PERSON_ID_PLAYER,
				'role' => 'player',
				'status' => ROSTER_INVITED,
				'created' => FrozenDate::now(),
				'number' => 13,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_BLUE,
				'person_id' => PERSON_ID_CAPTAIN2,
				'role' => 'captain',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now(),
				'number' => 6,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_BLUE,
				'person_id' => PERSON_ID_CHILD,
				'role' => 'player',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now(),
				'number' => 2,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_GREEN,
				'person_id' => PERSON_ID_CAPTAIN3,
				'role' => 'captain',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now(),
				'number' => 50,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_YELLOW,
				'person_id' => PERSON_ID_CAPTAIN4,
				'role' => 'captain',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now(),
				'number' => 99,
				'position' => 'unspecified'
			],
			[
				'team_id' => TEAM_ID_BEARS,
				'person_id' => PERSON_ID_ANDY_SUB,
				'role' => 'captain',
				'status' => ROSTER_APPROVED,
				'created' => FrozenDate::now(),
				'number' => 17,
				'position' => 'unspecified'
			],
		];

		parent::init();
	}

}
