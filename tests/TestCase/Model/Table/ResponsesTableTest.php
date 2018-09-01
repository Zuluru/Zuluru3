<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\ResponsesTable;

/**
 * App\Model\Table\ResponsesTable Test Case
 */
class ResponsesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\ResponsesTable
	 */
	public $ResponsesTable;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.credits',
			'app.groups',
				'app.groups_people',
			'app.leagues',
				'app.divisions',
			'app.questions',
				'app.answers',
			'app.questionnaires',
				'app.questionnaires_questions',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.responses',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Responses') ? [] : ['className' => 'App\Model\Table\ResponsesTable'];
		$this->ResponsesTable = TableRegistry::get('Responses', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ResponsesTable);

		parent::tearDown();
	}

	/**
	 * Test beforeSave method
	 *
	 * @return void
	 */
	public function testBeforeSave() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
