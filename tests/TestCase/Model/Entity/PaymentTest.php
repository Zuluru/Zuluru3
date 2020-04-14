<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Payment;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class PaymentTest extends TestCase {

	/**
	 * The Entity we'll be using in the test
	 *
	 * @var \App\Model\Entity\Payment
	 */
	public $Payment;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.EventTypes',
		'app.Affiliates',
			'app.Leagues',
				'app.Divisions',
			'app.Events',
				'app.Prices',
					'app.Registrations',
						'app.Payments',
		'app.I18n',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$payments = TableRegistry::get('Payments');
		$this->Payment = $payments->get(1);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Payment);

		parent::tearDown();
	}

	/**
	 * Test _getPaid() method
	 *
	 * @return void
	 */
	public function testGetPaid() {
		$this->assertEquals(11.50, $this->Payment->paid);
	}

}
