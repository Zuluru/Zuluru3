<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use EventTypesSeed;

class EventTypesFixture extends TestFixture {

	use SeedFixtureTrait;

	/**
	 * Seed name to use
	 */
	public $seed = EventTypesSeed::class;

	public function __construct() {
		parent::__construct();

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
	}

}
