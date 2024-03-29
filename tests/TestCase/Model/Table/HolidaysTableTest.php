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
	 * @var HolidaysTable
	 */
	public $HolidaysTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Holidays') ? [] : ['className' => HolidaysTable::class];
		$this->HolidaysTable = TableRegistry::getTableLocator()->get('Holidays', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->HolidaysTable);

		parent::tearDown();
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
	    $affiliateId = mt_rand();
	    $holiday = HolidayFactory::make(['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->HolidaysTable->affiliate($holiday->id));
	}

}
