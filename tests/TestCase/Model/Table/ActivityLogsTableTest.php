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
		'app.Affiliates',
			'app.Leagues',
				'app.Divisions',
					'app.Teams',
			'app.MailingLists',
				'app.Newsletters',
			'app.ActivityLogs',
		'app.I18n',
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
