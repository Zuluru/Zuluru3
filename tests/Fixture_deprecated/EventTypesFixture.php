<?php


namespace App\Test\Fixture_deprecated;

use Cake\TestSuite\Fixture\TestFixture;

class EventTypesFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'event_types'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'name' => 'Membership',
				'type' => 'membership'
			],
			[
				'name' => 'Teams for Leagues',
				'type' => 'team'
			],
			[
				'name' => 'Individuals for Leagues',
				'type' => 'individual'
			],
			[
				'name' => 'Teams for Events',
				'type' => 'team'
			],
			[
				'name' => 'Individuals for Events',
				'type' => 'individual'
			],
			[
				'name' => 'Clinics',
				'type' => 'generic'
			],
			[
				'name' => 'Social Events',
				'type' => 'generic'
			]
		];

		if (!defined('EVENT_TYPE_ID_MEMBERSHIP')) {
			$i = 0;
			define('EVENT_TYPE_ID_MEMBERSHIP', ++$i);
			define('EVENT_TYPE_ID_TEAMS_FOR_LEAGUES', ++$i);
			define('EVENT_TYPE_ID_INDIVIDUALS_FOR_LEAGUES', ++$i);
			define('EVENT_TYPE_ID_TEAMS_FOR_EVENTS', ++$i);
			define('EVENT_TYPE_ID_INDIVIDUALS_FOR_EVENTS', ++$i);
			define('EVENT_TYPE_ID_CLINICS', ++$i);
			define('EVENT_TYPE_ID_SOCIAL_EVENTS', ++$i);
		}

		parent::init();
	}

}
