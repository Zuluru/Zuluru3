<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\GamesTable;

/**
 * App\Model\Table\GamesTable Test Case
 */
class GamesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\GamesTable
	 */
	public $GamesTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_people',
					'app.game_slots',
						'app.divisions_gameslots',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.games_allstars',
						'app.score_entries',
						'app.spirit_entries',
						'app.score_details',
			'app.badges',
				'app.badges_people',
		'app.i18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Games') ? [] : ['className' => 'App\Model\Table\GamesTable'];
		$this->GamesTable = TableRegistry::get('Games', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->GamesTable);

		parent::tearDown();
	}

	/**
	 * Test validationGameEdit method
	 *
	 * @return void
	 */
	public function testValidationGameEdit() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationScheduleAdd method
	 *
	 * @return void
	 */
	public function testValidationScheduleAdd() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationScheduleEdit method
	 *
	 * @return void
	 */
	public function testValidationScheduleEdit() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeMarshal method
	 *
	 * @return void
	 */
	public function testBeforeMarshal() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeRules method
	 *
	 * @return void
	 */
	public function testBeforeRules() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeSave method
	 *
	 * @return void
	 */
	public function testBeforeSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 *
	 * @return void
	 */
	public function testBeforeDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test afterDelete method
	 *
	 * @return void
	 */
	public function testAfterDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareSportDateAndField method
	 *
	 * @return void
	 */
	public function testCompareSportDateAndField() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test compareDateAndField method
	 *
	 * @return void
	 */
	public function testCompareDateAndField() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test adjustEntryIndices method
	 *
	 * @return void
	 */
	public function testAdjustEntryIndices() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test readAttendance method
	 *
	 * @return void
	 */
	public function testReadAttendance() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test matchDates method
	 *
	 * @return void
	 */
	public function testMatchDates() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test attendanceOptions method
	 *
	 * @return void
	 */
	public function testAttendanceOptions() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test twitterScore method
	 *
	 * @return void
	 */
	public function testTwitterScore() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
		$this->assertEquals(AFFILIATE_ID_CLUB, $this->GamesTable->affiliate(1));
	}

}
