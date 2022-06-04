<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\RegionFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\RegionsTable;

/**
 * App\Model\Table\RegionsTable Test Case
 */
class RegionsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var RegionsTable
	 */
	public $RegionsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Regions') ? [] : ['className' => RegionsTable::class];
		$this->RegionsTable = TableRegistry::getTableLocator()->get('Regions', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->RegionsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $entity = RegionFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->RegionsTable->affiliate($entity->id));
	}

}
