<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use App\Model\Table\SubscriptionsTable;

/**
 * App\Model\Table\SubscriptionsTable Test Case
 */
class SubscriptionsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\SubscriptionsTable
	 */
	public $SubscriptionsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Subscriptions') ? [] : ['className' => 'App\Model\Table\SubscriptionsTable'];
		$this->SubscriptionsTable = TableRegistry::get('Subscriptions', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->SubscriptionsTable);

		parent::tearDown();
	}

}
