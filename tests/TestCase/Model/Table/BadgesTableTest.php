<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\BadgeFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\BadgesTable;

/**
 * App\Model\Table\BadgesTable Test Case
 */
class BadgesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var BadgesTable
	 */
	public $BadgesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Badges') ? [] : ['className' => BadgesTable::class];
		$this->BadgesTable = TableRegistry::getTableLocator()->get('Badges', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->BadgesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $badge = BadgeFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->BadgesTable->affiliate($badge->id));
	}

}
