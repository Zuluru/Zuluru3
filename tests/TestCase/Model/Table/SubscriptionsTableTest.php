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
	 * @var SubscriptionsTable
	 */
	public $SubscriptionsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Subscriptions') ? [] : ['className' => SubscriptionsTable::class];
		$this->SubscriptionsTable = TableRegistry::getTableLocator()->get('Subscriptions', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->SubscriptionsTable);

		parent::tearDown();
	}

}
