<?php
namespace App\Test\TestCase\View\Helper;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use App\View\Helper\ZuluruTimeHelper;

/**
 * App\Model\Helper\ZuluruTimeHelper Test Case
 */
class ZuluruTimeHelperTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \App\View\Helper\ZuluruTimeHelper
	 */
	public $ZuluruTimeHelper;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$view = new View();
		$this->ZuluruTimeHelper = new ZuluruTimeHelper($view);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->ZuluruTimeHelper);

		parent::tearDown();
	}

	/**
	 * Test time method
	 *
	 * @return void
	 */
	public function testTime(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test date method
	 *
	 * @return void
	 */
	public function testDate(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test datetime method
	 *
	 * @return void
	 */
	public function testDatetime(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method
	 *
	 * @return void
	 */
	public function testDay(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fulldate method
	 *
	 * @return void
	 */
	public function testFulldate(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fulldatetime method
	 *
	 * @return void
	 */
	public function testFulldatetime(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test dateRange method
	 *
	 * @return void
	 */
	public function testDateRange(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeRange method
	 *
	 * @return void
	 */
	public function testTimeRange(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test dateTimeRange method
	 *
	 * @return void
	 */
	public function testDateTimeRange(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iCal method
	 *
	 * @return void
	 */
	public function testICal(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iCalDateTimeRange method
	 *
	 * @return void
	 */
	public function testICalDateTimeRange(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
