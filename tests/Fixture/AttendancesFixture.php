<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * AttendancesFixture
 *
 */
class AttendancesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'attendances'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'team_id' => TEAM_ID_RED,
				'game_date' => new FrozenDate('last Monday'),
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'team_event_id' => null,
				'person_id' => PERSON_ID_CAPTAIN,
				'status' => ATTENDANCE_ATTENDING,
				'comment' => 'Might be a bit late',
				'created' => (new FrozenDate('last Monday'))->subDays(7),
				'modified' => (new FrozenDate('last Monday'))->subDays(6),
			],
			[
				'team_id' => TEAM_ID_RED,
				'game_date' => new FrozenDate('last Monday'),
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'team_event_id' => null,
				'person_id' => PERSON_ID_CAPTAIN3,
				'status' => ATTENDANCE_UNKNOWN,
				'comment' => null,
				'created' => (new FrozenDate('last Monday'))->subDays(7),
				'modified' => (new FrozenDate('last Monday'))->subDays(7),
			],
			[
				'team_id' => TEAM_ID_RED,
				'game_date' => null,
				'game_id' => null,
				'team_event_id' => TEAM_EVENT_ID_RED_PRACTICE,
				'person_id' => PERSON_ID_CAPTAIN,
				'status' => ATTENDANCE_ATTENDING,
				'comment' => 'Might be a bit late',
				'created' => (new FrozenDate('last Monday'))->subDays(6),
				'modified' => (new FrozenDate('last Monday'))->subDays(7),
			],
			[
				'team_id' => TEAM_ID_RED,
				'game_date' => null,
				'game_id' => null,
				'team_event_id' => TEAM_EVENT_ID_RED_PRACTICE,
				'person_id' => PERSON_ID_CAPTAIN3,
				'status' => ATTENDANCE_UNKNOWN,
				'comment' => null,
				'created' => (new FrozenDate('last Monday'))->subDays(7),
				'modified' => (new FrozenDate('last Monday'))->subDays(7),
			],
		];

		parent::init();
	}

}
