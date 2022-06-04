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
	 * @var BadgesPeopleTable
	 */
	public $BadgesPeopleTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('BadgesPeople') ? [] : ['className' => BadgesPeopleTable::class];
		$this->BadgesPeopleTable = TableRegistry::getTableLocator()->get('BadgesPeople', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->BadgesPeopleTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
	    $affiliateId = mt_rand();
	    $badgesPeople = BadgesPersonFactory::make()->with('Badges', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->BadgesPeopleTable->affiliate($badgesPeople->id));
	}

}
