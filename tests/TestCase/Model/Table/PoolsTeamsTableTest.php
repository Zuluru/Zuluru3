<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\PoolsTeamsTable;

/**
 * App\Model\Table\PoolsTeamsTable Test Case
 */
class PoolsTeamsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\PoolsTeamsTable
	 */
	public $PoolsTeamsTable;

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
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
					'app.Pools',
						'app.PoolsTeams',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('PoolsTeams') ? [] : ['className' => 'App\Model\Table\PoolsTeamsTable'];
		$this->PoolsTeamsTable = TableRegistry::get('PoolsTeams', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PoolsTeamsTable);

		parent::tearDown();
	}

	/**
	 * Test validationQualifiers method
	 *
	 * @return void
	 */
	public function testValidationQualifiers() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
