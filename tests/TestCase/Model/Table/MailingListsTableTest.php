<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\MailingListFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\MailingListsTable;

/**
 * App\Model\Table\MailingListsTable Test Case
 */
class MailingListsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var MailingListsTable
	 */
	public $MailingListsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('MailingLists') ? [] : ['className' => MailingListsTable::class];
		$this->MailingListsTable = TableRegistry::getTableLocator()->get('MailingLists', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->MailingListsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
	    $affiliateId = mt_rand();
	    $mailingList = MailingListFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->MailingListsTable->affiliate($mailingList->id));
	}

}
