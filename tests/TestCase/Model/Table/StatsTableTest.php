<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\StatsTable;

/**
 * App\Model\Table\StatsTable Test Case
 */
class StatsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\StatsTable
	 */
	public $StatsTable;

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
			'app.Groups',
				'app.GroupsPeople',
			'app.Regions',
				'app.Facilities',
					'app.Fields',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.Pools',
						'app.PoolsTeams',
					'app.Games',
						'app.Stats',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Stats') ? [] : ['className' => 'App\Model\Table\StatsTable'];
		$this->StatsTable = TableRegistry::get('Stats', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->StatsTable);

		parent::tearDown();
	}

	/**
	 * Test applicable method
	 *
	 * @return void
	 */
	public function testApplicable() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
