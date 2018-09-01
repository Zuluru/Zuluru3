<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * ActivityLogsFixture
 *
 */
class ActivityLogsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'activity_logs'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'type' => 'email_score_reminder',
				'created' => (new FrozenDate('last Monday 12:00:00'))->addDay(),
				'team_id' => TEAM_ID_RED,
				'person_id' => null,
				'game_id' => GAME_ID_LADDER_MATCHED_SCORES,
				'team_event_id' => null,
				'custom' => '',
				'newsletter_id' => null,
			],
			[
				'type' => 'newsletter',
				'created' => (new FrozenDate('last Monday 12:00:00'))->addDay(),
				'team_id' => null,
				'person_id' => PERSON_ID_CHILD,
				'game_id' => null,
				'team_event_id' => null,
				'custom' => '',
				'newsletter_id' => NEWSLETTER_ID_JUNIOR_CLINICS,
			],
		];

		parent::init();
	}

}
