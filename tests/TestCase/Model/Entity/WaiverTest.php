<?php
namespace App\Test\TestCase\Model\Entity;

use App\Test\Factory\WaiverFactory;
use Cake\I18n\FrozenDate;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Waiver Test Case
 */
class WaiverTest extends TestCase {

	public function tearDown(): void {
		FrozenDate::setTestNow();
		parent::tearDown();
	}

	/**
	 * Test canSign method for annual waivers
	 */
	public function testCanSignAnnual(): void {
		$waiver = WaiverFactory::make([
			'expiry_type' => 'fixed_dates',
			'start_month' => 1,
			'start_day' => 1,
			'end_month' => 12,
			'end_day' => 31,
		])->getEntity();

		$now = FrozenDate::now();

		// Signing just within the year
		$this->assertTrue($waiver->canSign($now->addYears(1)));

		// Signing just outside the year
		$this->assertFalse($waiver->canSign($now->addYears(1)->addDays(1)));
	}

	/**
	 * Test canSign method for elapsed time waivers
	 */
	public function testCanSignElapsed(): void {
		$waiver = WaiverFactory::make([
			'expiry_type' => 'elapsed_time',
			'duration' => 5,
		])->getEntity();

		// Check same day signing
		$now = FrozenDate::now();
		$this->assertTrue($waiver->canSign());

		// Last day signing
		$this->assertTrue($waiver->canSign($now->addDays(5)));

		// Signing before start date
		$this->assertFalse($waiver->canSign($now->subDays(1)));

		// Signing after end date but we're allowed to
		$this->assertTrue($waiver->canSign($now->addDays(6)));
	}

	/**
	 * Test validRange method for annual waivers
	 */
	public function testValidRangeAnnual(): void {
		$waiver = WaiverFactory::make([
			'expiry_type' => 'fixed_dates',
			'start_month' => 1,
			'start_day' => 1,
			'end_month' => 12,
			'end_day' => 31,
		])->getEntity();

		$waiver_april = WaiverFactory::make([
			'expiry_type' => 'fixed_dates',
			'start_month' => 4,
			'start_day' => 1,
			'end_month' => 3,
			'end_day' => 31,
		])->getEntity();

		// Set the date to something after the April 1 start of the second annual waiver
		FrozenDate::setTestNow(new FrozenDate('May 31'));
		$now = FrozenDate::now();

		$apr1 = new FrozenDate('Apr 1');
		$mar31 = (new FrozenDate('Mar 31'))->addYears(1);

		// Annual waivers are good for the entire year surrounding the date
		$this->assertEquals([$now->startOfYear(), $now->endOfYear()], $waiver->validRange());
		$this->assertEquals([$now->startOfYear()->addYears(1), $now->endOfYear()->addYears(1)], $waiver->validRange($now->addYears(1)));
		$this->assertEquals([$apr1, $mar31], $waiver_april->validRange());
		$this->assertEquals([$apr1->addYears(1), $mar31->addYears(1)], $waiver_april->validRange($now->addYears(1)));
	}

	/**
	 * Test validRange method for elapsed time waivers
	 */
	public function testValidRangeElapsed(): void {
		$waiver = WaiverFactory::make([
			'expiry_type' => 'elapsed_time',
			'duration' => 5,
		])->getEntity();

		$now = FrozenDate::now();

		// Elapsed time waivers are good for a certain number of days from today, regardless of signing date
		$this->assertEquals([$now, $now->addDays(5)], $waiver->validRange());
		$this->assertEquals([$now, $now->addDays(5)], $waiver->validRange($now->addDays(50)));
	}

	/**
	 * Test validRange method for event waivers
	 */
	public function testValidRangeEvent(): void {
		$waiver = WaiverFactory::make([
			'expiry_type' => 'event',
		])->getEntity();

		$now = FrozenDate::now();

		// Event waivers are good only for the day of the event
		$this->assertEquals([$now, $now], $waiver->validRange());
		$this->assertEquals([$now->addDays(50), $now->addDays(50)], $waiver->validRange($now->addDays(50)));
	}

	/**
	 * Test validRange method for perpetual waivers
	 */
	public function testValidRangePerpetual(): void {
		$waiver = WaiverFactory::make([
			'expiry_type' => 'never',
		])->getEntity();

		$now = FrozenDate::now();

		// Perpetual waivers are good from signing date until the end of time
		$this->assertEquals([$now, new FrozenDate('9999-12-31')], $waiver->validRange());
		$this->assertEquals([$now->addDays(50), new FrozenDate('9999-12-31')], $waiver->validRange($now->addDays(50)));
	}
}
