<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\FacilityFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\FacilitiesTable;

/**
 * App\Model\Table\FacilitiesTable Test Case
 */
class FacilitiesTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\FacilitiesTable
	 */
	public $FacilitiesTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Facilities') ? [] : ['className' => 'App\Model\Table\FacilitiesTable'];
		$this->FacilitiesTable = TableRegistry::get('Facilities', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->FacilitiesTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
        $affiliateId = rand();
        $facility = FacilityFactory::make()
            ->with('Regions', ['affiliate_id' => $affiliateId])
            ->persist();
		$this->assertEquals($affiliateId, $this->FacilitiesTable->affiliate($facility->id));
	}

}
