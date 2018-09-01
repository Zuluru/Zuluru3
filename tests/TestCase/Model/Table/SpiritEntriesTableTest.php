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
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
			'app.groups',
				'app.groups_people',
			'app.regions',
				'app.facilities',
					'app.fields',
			'app.leagues',
				'app.divisions',
					'app.teams',
					'app.pools',
						'app.pools_teams',
					'app.games',
						'app.spirit_entries',
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
