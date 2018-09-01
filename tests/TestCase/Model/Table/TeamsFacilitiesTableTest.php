<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\TeamsFacilitiesTable;

/**
 * App\Model\Table\TeamsFacilitiesTable Test Case
 */
class TeamsFacilitiesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\TeamsFacilitiesTable
	 */
	public $TeamsFacilitiesTable;

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
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_facilities',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('TeamsFacilities') ? [] : ['className' => 'App\Model\Table\TeamsFacilitiesTable'];
		$this->TeamsFacilitiesTable = TableRegistry::get('TeamsFacilities', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TeamsFacilitiesTable);

		parent::tearDown();
	}

}
