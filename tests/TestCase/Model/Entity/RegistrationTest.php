<?php
namespace App\Test\TestCase\Model\Entity;

use App\Middleware\ConfigurationLoader;
use App\Model\Entity\Registration;
use App\Test\Factory\GameFactory;
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
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
        $this->markTestSkipped(GameFactory::TODO_FACTORIES);

		ConfigurationLoader::loadConfiguration();

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
		$this->assertEquals([1.31, 0.09, 0.10], $this->Registration2->paymentAmounts()); // $1.50 outstanding
		$this->assertEquals([0, 0, 0], $this->Registration3->paymentAmounts()); // online payments not allowed
		$this->assertEquals([1.74, 0.12, 0.14], $this->Registration4->paymentAmounts()); // $2 deposit
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
		$this->assertEquals(10.0, $this->Registration2->total_payment);
		$this->assertEquals(0.0, $this->Registration3->total_payment);
	}

	/**
	 * Test _getBalance();
	 */
	public function testGetBalance() {
		$this->assertEquals(1.50, $this->Registration2->balance);
		$this->assertEquals(57.50, $this->Registration3->balance);
	}

}
