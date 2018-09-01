<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Registration;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Registration Test Case
 */
class RegistrationTest extends TestCase {

	/**
	 * Test subject 2
	 *
	 * @var \App\Model\Entity\Registration
	 */
	public $Registration2;

	/**
	 * Test subject 3
	 *
	 * @var \App\Model\Entity\Registration
	 */
	public $Registration3;

	/**
	 * Test subject 3
	 *
	 * @var \App\Model\Entity\Registration
	 */
	public $Registration4;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
			'app.leagues',
				'app.divisions',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$registrations = TableRegistry::get('Registrations');
		$this->Registration2 = $registrations->get(REGISTRATION_ID_CAPTAIN_MEMBERSHIP, ['contain' => ['Prices', 'Payments', 'Events']]);
		$this->Registration3 = $registrations->get(REGISTRATION_ID_COORDINATOR_MEMBERSHIP, ['contain' => ['Prices', 'Payments', 'Events']]);
		$this->Registration4 = $registrations->get(REGISTRATION_ID_MANAGER_MEMBERSHIP, ['contain' => ['Prices', 'Payments', 'Events']]);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Registration2);
		unset($this->Registration3);
		unset($this->Registration4);

		parent::tearDown();
	}

	/**
	 * Test paymentAmounts method
	 *
	 * @return void
	 */
	public function testPaymentAmounts() {
		$this->assertEquals([4.66, 0.78, 1.56], $this->Registration2->paymentAmounts());
		$this->assertEquals([0, 0, 0], $this->Registration3->paymentAmounts());
		$this->assertEquals([1.34, 0.22, 0.44], $this->Registration4->paymentAmounts());
	}

	/**
	 * Test _getLongDescription();
	 */
	public function testGetLongDescription() {
		$this->assertEquals('Membership', $this->Registration2->long_description);
		$this->assertEquals('Membership (price2)', $this->Registration3->long_description);
		$this->assertEquals('Membership (Deposit)', $this->Registration4->long_description);

	}

	/**
	 * Test _getTotalPayment()
	 */
	public function testGetTotalPayment() {
		$this->assertEquals(3.0, $this->Registration2->total_payment);
		$this->assertEquals(0.0, $this->Registration3->total_payment);
	}

	/**
	 * Test _getBalance();
	 */
	public function testGetBalance() {
		$this->assertEquals(7.0, $this->Registration2->balance);
		$this->assertEquals(0.0, $this->Registration3->balance);
	}

}
