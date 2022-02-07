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
	 * @var \App\Model\Table\MailingListsTable
	 */
	public $MailingListsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('MailingLists') ? [] : ['className' => 'App\Model\Table\MailingListsTable'];
		$this->MailingListsTable = TableRegistry::get('MailingLists', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->MailingListsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate(): void {
	    $affiliateId = rand();
	    $mailingList = MailingListFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->MailingListsTable->affiliate($mailingList->id));
	}

}
