<?php
namespace App\Test\TestCase\Model\Table;

use App\Test\Factory\PaymentFactory;
use Cake\ORM\TableRegistry;
use App\Model\Table\PaymentsTable;

/**
 * App\Model\Table\PaymentsTable Test Case
 */
class PaymentsTableTest extends TableTestCase {

	/**
	 * Test subject
	 *
	 * @var \App\Model\Table\PaymentsTable
	 */
	public $PaymentsTable;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('Payments') ? [] : ['className' => 'App\Model\Table\PaymentsTable'];
		$this->PaymentsTable = TableRegistry::get('Payments', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PaymentsTable);

		parent::tearDown();
	}

	/**
	 * Test validationAmount method
	 *
	 * @return void
	 */
	public function testValidationAmount(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationPayment method
	 *
	 * @return void
	 */
	public function testValidationPayment(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationRefund method
	 *
	 * @return void
	 */
	public function testValidationRefund(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationCredit method
	 *
	 * @return void
	 */
	public function testValidationCredit(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationTransferFrom method
	 *
	 * @return void
	 */
	public function testValidationTransferFrom(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationTransferTo method
	 *
	 * @return void
	 */
	public function testValidationTransferTo(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeMarshal method
	 *
	 * @return void
	 */
	public function testBeforeMarshal(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 *
	 * @return void
	 */
	public function testAffiliate(): void {
        $affiliateId = rand();
        $payment = PaymentFactory::make()->with('Registrations.Events', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->PaymentsTable->affiliate($payment->id));
	}

}
