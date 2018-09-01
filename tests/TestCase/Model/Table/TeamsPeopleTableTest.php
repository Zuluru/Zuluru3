<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\TeamsPeopleTable;

/**
 * App\Model\Table\TeamsPeopleTable Test Case
 */
class TeamsPeopleTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\TeamsPeopleTable
	 */
	public $TeamsPeopleTable;

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
						'app.teams_people',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('TeamsPeople') ? [] : ['className' => 'App\Model\Table\TeamsPeopleTable'];
		$this->TeamsPeopleTable = TableRegistry::get('TeamsPeople', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->TeamsPeopleTable);

		parent::tearDown();
	}

	/**
	 * Test afterSave method
	 *
	 * @return void
	 */
	public function testAfterSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeDelete method
	 *
	 * @return void
	 */
	public function testBeforeDelete() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
