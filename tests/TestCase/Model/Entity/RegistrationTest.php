<?php
namespace App\Test\TestCase\Model\Entity;

use App\Middleware\ConfigurationLoader;
use App\Model\Entity\Event;
use App\Test\Factory\EventFactory;
use App\Test\Factory\RegistrationFactory;
use Cake\Cache\Cache;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Registration Test Case
 */
class RegistrationTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.Settings',
	];

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		ConfigurationLoader::loadConfiguration();
	}

	public function tearDown(): void {
		parent::tearDown();
		Cache::clear('long_term');
	}

	private function createRegistrations() {
		/** @var Event $event */
		$event = EventFactory::make(['name' => 'Membership'])->with('Prices', [
			['name' => '', 'cost' => 10, 'tax1' => 0.70, 'tax2' => 0.80],
			['name' => 'price2', 'cost' => 50, 'tax1' => 3.50, 'tax2' => 4.00, 'online_payment_option' => ONLINE_NO_PAYMENT],
		])->persist();

		$registration1 = RegistrationFactory::make(['total_amount' => 11.50, 'payment' => 'Partial'])
			->with('Events', $event)
			->with('Prices', $event->prices[0])
			->with('Payments', [
				['payment_amount' => 5, 'payment_type' => 'Deposit'],
				['payment_amount' => 5, 'payment_type' => 'Installment'],
			])
			->persist();

		$registration2 = RegistrationFactory::make(['total_amount' => 57.50, 'payment' => 'Unpaid'])
			->with('Events', $event)
			->with('Prices', $event->prices[1])
			->persist();

		$registration3 = RegistrationFactory::make(['total_amount' => 11.50, 'payment' => 'Unpaid', 'deposit_amount' => 2])
			->with('Events', $event)
			->with('Prices', $event->prices[0])
			->with('Payments', ['payment_amount' => 10])
			->persist();

		return [$registration1, $registration2, $registration3];
	}

	/**
	 * Test paymentAmounts method
	 */
	public function testPaymentAmounts(): void {
		[$registration1, $registration2, $registration3] = $this->createRegistrations();

		$this->assertEquals([1.31, 0.09, 0.10], $registration1->paymentAmounts()); // $1.50 outstanding
		$this->assertEquals([0, 0, 0], $registration2->paymentAmounts()); // online payments not allowed
		$this->assertEquals([1.74, 0.12, 0.14], $registration3->paymentAmounts()); // $2 deposit
	}

	/**
	 * Test _getLongDescription();
	 */
	public function testGetLongDescription(): void {
		[$registration1, $registration2, $registration3] = $this->createRegistrations();

		$this->assertEquals('Membership', $registration1->long_description);
		$this->assertEquals('Membership (price2)', $registration2->long_description);
		$this->assertEquals('Membership (Deposit)', $registration3->long_description);

	}

	/**
	 * Test _getTotalPayment()
	 */
	public function testGetTotalPayment(): void {
		[$registration1, $registration2, $registration3] = $this->createRegistrations();

		$this->assertEquals(10.0, $registration1->total_payment);
		$this->assertEquals(0.0, $registration2->total_payment);
	}

	/**
	 * Test _getBalance();
	 */
	public function testGetBalance(): void {
		[$registration1, $registration2, $registration3] = $this->createRegistrations();

		$this->assertEquals(1.50, $registration1->balance);
		$this->assertEquals(57.50, $registration2->balance);
	}

}
