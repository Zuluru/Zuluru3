<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\BadgesPersonFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\BadgesPeopleTable;

/**
 * App\Model\Table\BadgesPeopleTable Test Case
 */
class BadgesPeopleTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\BadgesPeopleTable
	 */
	public $BadgesPeopleTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('BadgesPeople') ? [] : ['className' => 'App\Model\Table\BadgesPeopleTable'];
		$this->BadgesPeopleTable = TableRegistry::get('BadgesPeople', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->BadgesPeopleTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
	    $affiliateId = rand();
	    $badgesPeople = BadgesPersonFactory::make()->with('Badges', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->BadgesPeopleTable->affiliate($badgesPeople->id));
	}

}
