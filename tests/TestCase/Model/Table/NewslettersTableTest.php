<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\NewsletterFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\NewslettersTable;

/**
 * App\Model\Table\NewslettersTable Test Case
 */
class NewslettersTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\NewslettersTable
	 */
	public $NewslettersTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Newsletters') ? [] : ['className' => 'App\Model\Table\NewslettersTable'];
		$this->NewslettersTable = TableRegistry::get('Newsletters', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->NewslettersTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $newsletter = NewsletterFactory::make()->with('MailingLists', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->NewslettersTable->affiliate($newsletter->id));
	}

}
