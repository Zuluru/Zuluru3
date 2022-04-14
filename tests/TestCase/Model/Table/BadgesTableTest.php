<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\BadgeFactory;
use Cake\ORM\TableRegistry;

/**
 * App\Model\Table\BadgesTable Test Case
 */
class BadgesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\BadgesTable
	 */
	public $BadgesTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::exists('Badges') ? [] : ['className' => 'App\Model\Table\BadgesTable'];
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
        $affiliateId = rand();
        $badge = BadgeFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->BadgesTable->affiliate($badge->id));
	}

}
