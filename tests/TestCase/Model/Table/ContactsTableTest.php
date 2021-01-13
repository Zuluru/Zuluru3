<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\ContactFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\ContactsTable;

/**
 * App\Model\Table\ContactsTable Test Case
 */
class ContactsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\ContactsTable
	 */
	public $ContactsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Contacts') ? [] : ['className' => 'App\Model\Table\ContactsTable'];
		$this->ContactsTable = TableRegistry::get('Contacts', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ContactsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $contact = ContactFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->ContactsTable->affiliate($contact->id));
	}

}
