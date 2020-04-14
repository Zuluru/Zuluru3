<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\SpiritEntriesTable;

/**
 * App\Model\Table\SpiritEntriesTable Test Case
 */
class SpiritEntriesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\SpiritEntriesTable
	 */
	public $SpiritEntriesTable;

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
						'app.SpiritEntries',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('SpiritEntries') ? [] : ['className' => 'App\Model\Table\SpiritEntriesTable'];
		$this->SpiritEntriesTable = TableRegistry::get('SpiritEntries', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->SpiritEntriesTable);

		parent::tearDown();
	}

	/**
	 * Test addValidation method
	 *
	 * @return void
	 */
	public function testAddValidation() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
