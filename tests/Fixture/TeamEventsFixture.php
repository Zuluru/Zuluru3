<?php
namespace App\Test\Fixture;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * TeamEventsFixture
 *
 */
class TeamEventsFixture extends TestFixture {

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'team_events'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'team_id' => TEAM_ID_RED,
				'name' => 'Practice',
				'description' => 'Team practice, followed by optional BBQ.',
				'website' => '',
				'date' => FrozenDate::now(),
				'start' => new FrozenTime('18:00:00'),
				'end' => new FrozenTime('20:00:00'),
				'location_name' => 'Ann\'s house',
				'location_street' => '123 Main St.',
				'location_city' => 'Toronto',
				'location_province' => 'Ontario',
				'created' => FrozenDate::now(),
			],
			[
				'team_id' => TEAM_ID_BEARS,
				'name' => 'Practice',
				'description' => 'Team practice, followed by optional BBQ.',
				'website' => '',
				'date' => FrozenDate::now(),
				'start' => new FrozenTime('18:00:00'),
				'end' => new FrozenTime('20:00:00'),
				'location_name' => 'Ann\'s house',
				'location_street' => '123 Main St.',
				'location_city' => 'Toronto',
				'location_province' => 'Ontario',
				'created' => FrozenDate::now(),
			],
		];

		if (!defined('TEAM_EVENT_ID_RED_PRACTICE')) {
			$i = 0;
			define('TEAM_EVENT_ID_RED_PRACTICE', ++$i);
			define('TEAM_EVENT_ID_BEARS_PRACTICE', ++$i);
		}

		parent::init();
	}

}
