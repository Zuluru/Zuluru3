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
	 * @var \App\Model\Table\RegionsTable
	 */
	public $RegionsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Regions') ? [] : ['className' => 'App\Model\Table\RegionsTable'];
		$this->RegionsTable = TableRegistry::get('Regions', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->RegionsTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate(): void {
        $affiliateId = rand();
        $entity = RegionFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->RegionsTable->affiliate($entity->id));
	}

}
