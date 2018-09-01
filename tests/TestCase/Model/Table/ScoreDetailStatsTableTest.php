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
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.score_details',
							'app.score_detail_stats',
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
