<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\GamesAllstarsTable;

/**
 * App\Model\Table\GamesAllstarsTable Test Case
 */
class GamesAllstarsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\GamesAllstarsTable
	 */
	public $GamesAllstarsTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.affiliates',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.games_allstars',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('GamesAllstars') ? [] : ['className' => 'App\Model\Table\GamesAllstarsTable'];
		$this->GamesAllstarsTable = TableRegistry::get('GamesAllstars', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->GamesAllstarsTable);

		parent::tearDown();
	}

}
