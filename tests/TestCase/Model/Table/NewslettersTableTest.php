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
	 * @var NewslettersTable
	 */
	public $NewslettersTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Newsletters') ? [] : ['className' => NewslettersTable::class];
		$this->NewslettersTable = TableRegistry::getTableLocator()->get('Newsletters', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->NewslettersTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $newsletter = NewsletterFactory::make()->with('MailingLists', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->NewslettersTable->affiliate($newsletter->id));
	}

}
