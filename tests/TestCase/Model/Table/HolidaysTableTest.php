<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\HolidayFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\HolidaysTable;

/**
 * App\Model\Table\HolidaysTable Test Case
 */
class HolidaysTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\HolidaysTable
	 */
	public $HolidaysTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Holidays') ? [] : ['className' => 'App\Model\Table\HolidaysTable'];
		$this->HolidaysTable = TableRegistry::get('Holidays', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->HolidaysTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate() {
	    $affiliateId = rand();
	    $holiday = HolidayFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->HolidaysTable->affiliate($holiday->id));
	}

}
