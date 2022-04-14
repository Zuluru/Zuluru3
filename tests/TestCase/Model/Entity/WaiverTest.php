<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Waiver;
use App\Test\Factory\GameFactory;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Waiver Test Case
 */
class WaiverTest extends TestCase {

	/**
	 * @var \App\Model\Entity\Waiver
	 */
	public $WaiverAnnual;
	/**
	 * @var \App\Model\Entity\Waiver
	 */
	public $WaiverAnnual2;
	/**
	 * @var \App\Model\Entity\Waiver
	 */
	public $WaiverEvent;
	/**
	 * @var \App\Model\Entity\Waiver
	 */
	public $WaiverElapsed;
	/**
	 * @var \App\Model\Entity\Waiver
	 */
	public $WaiverPerpetual;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);
		$waivers = TableRegistry::getTableLocator()->get('Waivers');
		$this->WaiverAnnual = $waivers->get(WAIVER_ID_ANNUAL);
		$this->WaiverAnnual2 = $waivers->get(WAIVER_ID_ANNUAL2);
		$this->WaiverEvent = $waivers->get(WAIVER_ID_EVENT);
		$this->WaiverElapsed = $waivers->get(WAIVER_ID_ELAPSED);
		$this->WaiverPerpetual = $waivers->get(WAIVER_ID_PERPETUAL);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->WaiverAnnual);
		unset($this->WaiverAnnual2);
		unset($this->WaiverEvent);
		unset($this->WaiverElapsed);
		unset($this->WaiverPerpetual);

		parent::tearDown();
	}

	/**
	 * Test canSign method
	 */
	public function testCanSign(): void {
		// Check same day signing
		$now = FrozenDate::now();
		$this->assertTrue($this->WaiverElapsed->canSign());

		// Last day signing
		$this->assertTrue($this->WaiverElapsed->canSign($now->addDays(5)));

		// Signing before start date
		$this->assertFalse($this->WaiverElapsed->canSign($now->subDay()));

		// Signing after end date but we're allowed to
		$this->assertTrue($this->WaiverElapsed->canSign($now->addDays(6)));

		// Signing just within the year
		$this->assertTrue($this->WaiverAnnual->canSign($now->addYear()));

		// Signing just outside of the year
		$this->assertFalse($this->WaiverAnnual->canSign($now->addYear()->addDay()));
	}

	/**
	 * Test validRange method
	 */
	public function testValidRange(): void {
		// Set the date to something after the April 1 start of the the second annual waiver
		FrozenDate::setTestNow(new FrozenDate('May 31'));
		$now = FrozenDate::now();

		// Annual waivers are good for the entire year surrounding the date
		$this->assertEquals([$now->startOfYear(), $now->endOfYear()], $this->WaiverAnnual->validRange());
		$this->assertEquals([$now->startOfYear()->addYear(), $now->endOfYear()->addYear()], $this->WaiverAnnual->validRange($now->addYear()));
		$this->assertEquals([$now->month(4)->day(1), $now->addYear()->month(3)->day(31)], $this->WaiverAnnual2->validRange());
		$this->assertEquals([$now->addYear()->month(4)->day(1), $now->addYears(2)->month(3)->day(31)], $this->WaiverAnnual2->validRange($now->addYear()));

		// Event waivers are good only for the day of the event
		$this->assertEquals([$now, $now], $this->WaiverEvent->validRange());
		$this->assertEquals([$now->addDays(50), $now->addDays(50)], $this->WaiverEvent->validRange($now->addDays(50)));

		// Elapsed time waivers are good for a certain number of days from today, regardless of signing date
		$this->assertEquals([$now, $now->addDays(5)], $this->WaiverElapsed->validRange());
		$this->assertEquals([$now, $now->addDays(5)], $this->WaiverElapsed->validRange($now->addDays(50)));

		// Perpetual waivers are good from signing date until the end of time
		$this->assertEquals([$now, new FrozenDate('9999-12-31')], $this->WaiverPerpetual->validRange());
		$this->assertEquals([$now->addDays(50), new FrozenDate('9999-12-31')], $this->WaiverPerpetual->validRange($now->addDays(50)));

		// Try a date before the April 1 start of the the second annual waiver
		FrozenDate::setTestNow(new FrozenDate('January 1'));
		$now = FrozenDate::now();

		// Annual waivers are good for the entire year surrounding the date
		$this->assertEquals([$now->startOfYear(), $now->endOfYear()], $this->WaiverAnnual->validRange());
		$this->assertEquals([$now->startOfYear()->addYear(), $now->endOfYear()->addYear()], $this->WaiverAnnual->validRange($now->addYear()));
		$this->assertEquals([$now->subYear()->month(4)->day(1), $now->month(3)->day(31)], $this->WaiverAnnual2->validRange());
		$this->assertEquals([$now->month(4)->day(1), $now->addYear()->month(3)->day(31)], $this->WaiverAnnual2->validRange($now->addYear()));

		FrozenDate::setTestNow();
	}

}
