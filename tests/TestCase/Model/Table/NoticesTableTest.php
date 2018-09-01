<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\NoticesTable;

/**
 * App\Model\Table\NoticesTable Test Case
 */
class NoticesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\NoticesTable
	 */
	public $NoticesTable;

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
			//'app.notices',
				'app.notices_people',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Notices') ? [] : ['className' => 'App\Model\Table\NoticesTable'];
		$this->NoticesTable = TableRegistry::get('Notices', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->NoticesTable);

		parent::tearDown();
	}

}
