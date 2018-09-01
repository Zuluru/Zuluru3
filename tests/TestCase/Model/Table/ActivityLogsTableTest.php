<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\ActivityLogsTable;

/**
 * App\Model\Table\ActivityLogsTable Test Case
 */
class ActivityLogsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\ActivityLogsTable
	 */
	public $ActivityLogsTable;

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
			'app.mailing_lists',
				'app.newsletters',
			'app.activity_logs',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('ActivityLogs') ? [] : ['className' => 'App\Model\Table\ActivityLogsTable'];
		$this->ActivityLogsTable = TableRegistry::get('ActivityLogs', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ActivityLogsTable);

		parent::tearDown();
	}

}
