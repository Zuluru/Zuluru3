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
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Badges') ? [] : ['className' => 'App\Model\Table\BadgesTable'];
		$this->BadgesTable = TableRegistry::get('Badges', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->BadgesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $badge = BadgeFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->BadgesTable->affiliate($badge->id));
	}

}
