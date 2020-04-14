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
		'app.Affiliates',
			'app.Users',
				'app.People',
					'app.AffiliatesPeople',
			'app.Regions',
				'app.Facilities',
					'app.Fields',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
						'app.TeamEvents',
					'app.Pools',
						'app.PoolsTeams',
					'app.Games',
			'app.Attendances',
		'app.I18n',
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
