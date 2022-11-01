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
	 */
	public function setUp(): void {
		parent::setUp();
		$view = new View();
		$this->ZuluruTimeHelper = new ZuluruTimeHelper($view);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->ZuluruTimeHelper);

		parent::tearDown();
	}

	/**
	 * Test time method
	 */
	public function testTime(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test date method
	 */
	public function testDate(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test datetime method
	 */
	public function testDatetime(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method
	 */
	public function testDay(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fulldate method
	 */
	public function testFulldate(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fulldatetime method
	 */
	public function testFulldatetime(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test dateRange method
	 */
	public function testDateRange(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeRange method
	 */
	public function testTimeRange(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test dateTimeRange method
	 */
	public function testDateTimeRange(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iCal method
	 */
	public function testICal(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iCalDateTimeRange method
	 */
	public function testICalDateTimeRange(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
