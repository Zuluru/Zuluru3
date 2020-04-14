<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\ScoreDetailStatsTable;

/**
 * App\Model\Table\ScoreDetailStatsTable Test Case
 */
class ScoreDetailStatsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\ScoreDetailStatsTable
	 */
	public $ScoreDetailStatsTable;

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
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.Pools',
						'app.PoolsTeams',
					'app.Games',
						'app.ScoreDetails',
							'app.ScoreDetailStats',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('ScoreDetailStats') ? [] : ['className' => 'App\Model\Table\ScoreDetailStatsTable'];
		$this->ScoreDetailStatsTable = TableRegistry::get('ScoreDetailStats', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ScoreDetailStatsTable);

		parent::tearDown();
	}

}
