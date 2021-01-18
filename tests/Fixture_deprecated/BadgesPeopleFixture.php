<?php
namespace App\Test\Fixture_deprecated;

use Cake\I18n\FrozenDate;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * BadgesPeopleFixture
 *
 */
class BadgesPeopleFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public $table = 'badges_people';

	/**
	 * Import
	 *
	 * @var array
	 */
	public $import = ['table' => 'badges_people'];

	/**
	 * Initialize function: Mostly, set up records
	 */
	public function init() {
		$this->records = [
			[
				'badge_id' => BADGE_ID_ACTIVE_PLAYER,
				'person_id' => PERSON_ID_CAPTAIN,
				'nominated_by_id' => null,
				'game_id' => null,
				'team_id' => TEAM_ID_RED,
				'registration_id' => null,
				'reason' => null,
				'approved' => true,
				'approved_by_id' => null,
				'visible' => true,
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'badge_id' => BADGE_ID_HALL_OF_FAME,
				'person_id' => PERSON_ID_PLAYER,
				'nominated_by_id' => PERSON_ID_CAPTAIN,
				'game_id' => null,
				'team_id' => null,
				'registration_id' => null,
				'reason' => 'Super cool guy.',
				'approved' => true,
				'approved_by_id' => PERSON_ID_ADMIN,
				'visible' => true,
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
			[
				'badge_id' => BADGE_ID_HALL_OF_FAME,
				'person_id' => PERSON_ID_CAPTAIN,
				'nominated_by_id' => PERSON_ID_PLAYER,
				'game_id' => null,
				'team_id' => null,
				'registration_id' => null,
				'reason' => 'Pretty cool guy.',
				'approved' => false,
				'approved_by_id' => null,
				'visible' => true,
				'created' => FrozenDate::now(),
				'modified' => FrozenDate::now(),
			],
		];

		if (!defined('BADGE_ID_FOR_ACTIVE_PLAYER_CAPTAIN')) {
			$i = 0;
			define('BADGE_ID_FOR_ACTIVE_PLAYER_CAPTAIN', ++$i);
			define('BADGE_ID_FOR_HALL_OF_FAME_PLAYER', ++$i);
			define('BADGE_ID_FOR_HALL_OF_FAME_CAPTAIN', ++$i);
		}

		parent::init();
	}

}
