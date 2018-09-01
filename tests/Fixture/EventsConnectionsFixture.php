<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventsConnectionsFixture
 *
 */
class EventsConnectionsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'events_connections'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'event_id' => EVENT_ID_LEAGUE_TEAM,
				'connection' => EVENT_ALTERNATE,
				'connected_event_id' => EVENT_ID_LEAGUE_INDIVIDUAL_MONDAY,
			],
		];

		parent::init();
	}

}
