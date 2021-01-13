<?php
namespace TestCase\Model\Entity;

use App\Model\Entity\Price;
use App\Test\Factory\PriceFactory;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class PriceTest extends TestCase {

	/**
	 * The Entity we'll be using in the test
	 *
	 * @var \App\Model\Entity\Price
	 */
	public $Price;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->Price = PriceFactory::make([
            'event_id' => 1,
            'cost' => 10,
            'tax1' => 0.70,
            'tax2' => 0.80,
            'open' => new FrozenDate('January 1 00:00:00'),
            'close' => new FrozenDate('December 31 23:59:00'),
            'register_rule' => '',
            'minimum_deposit' => 0,
            'allow_late_payment' => false,
            'online_payment_option' => ONLINE_FULL_PAYMENT,
            'allow_reservations' => false,
            'reservation_duration' => 0,
        ])->getEntity();

	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Price);

		parent::tearDown();
	}

	/**
	 * Test _getTotal
	 */
	public function testGetTotal() {
		$defaultTax1 = Configure::read('payment.tax1_enable');
		$defaultTax2 = Configure::read('payment.tax2_enable');

		Configure::write('payment.tax1_enable', false);
		Configure::write('payment.tax2_enable', false);
		$this->assertEquals(10, $this->Price->total);

		Configure::write('payment.tax1_enable', true);
		$this->assertEquals(10.70, $this->Price->total);

		Configure::write('payment.tax2_enable', true);
		$this->assertEquals(11.50, $this->Price->total);

		Configure::write('payment.tax1_enable', false);
		$this->assertEquals(10.80, $this->Price->total);

		Configure::write('payment.tax1_enable', $defaultTax1);
		Configure::write('payment.tax2_enable', $defaultTax2);
	}

	/**
	 * Test _getAllowDeposit()
	 */
	public function testGetAllowDeposit() {
		$this->assertFalse($this->Price->allow_deposit);
	}

	/**
	 * Test _getFixedDeposit()
	 */
	public function testGetFixedDeposit() {
		$this->assertFalse($this->Price->fixed_deposit);
	}

	/**
	 * Test _getDepositOnly
	 */
	public function testGetDepositOnly() {
		$this->assertFalse($this->Price->deposit_only);
	}

}
