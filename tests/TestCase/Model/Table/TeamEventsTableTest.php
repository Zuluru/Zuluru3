<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\TeamEventsTable;

/**
 * App\Model\Table\TeamEventsTable Test Case
 */
class TeamEventsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\TeamEventsTable
	 */
	public $TeamEventsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.team_events',
					'app.pools',
						'app.pools_teams',
					'app.games',
			'app.attendances',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('TeamEvents') ? [] : ['className' => 'App\Model\Table\TeamEventsTable'];
		$this->TeamEventsTable = TableRegistry::get('TeamEvents', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TeamEventsTable);

		parent::tearDown();
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
	 * Test createAttendance method
	 *
	 * @return void
	 */
	public function testCreateAttendance() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
