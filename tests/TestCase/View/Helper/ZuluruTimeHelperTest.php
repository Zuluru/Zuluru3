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
	public function testTime() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test date method
	 *
	 * @return void
	 */
	public function testDate() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test datetime method
	 *
	 * @return void
	 */
	public function testDatetime() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test day method
	 *
	 * @return void
	 */
	public function testDay() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fulldate method
	 *
	 * @return void
	 */
	public function testFulldate() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test fulldatetime method
	 *
	 * @return void
	 */
	public function testFulldatetime() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test dateRange method
	 *
	 * @return void
	 */
	public function testDateRange() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test timeRange method
	 *
	 * @return void
	 */
	public function testTimeRange() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test dateTimeRange method
	 *
	 * @return void
	 */
	public function testDateTimeRange() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iCal method
	 *
	 * @return void
	 */
	public function testICal() {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test iCalDateTimeRange method
	 *
	 * @return void
	 */
	public function testICalDateTimeRange() {
		$this->markTestIncomplete('Not implemented yet.');
	}

}
