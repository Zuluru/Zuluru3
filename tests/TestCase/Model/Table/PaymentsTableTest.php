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
	 * @var PaymentsTable
	 */
	public $PaymentsTable;

	/**
	 * setUp method
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('Payments') ? [] : ['className' => PaymentsTable::class];
		$this->PaymentsTable = TableRegistry::getTableLocator()->get('Payments', $config);
	}

	/**
	 * tearDown method
	 */
	public function tearDown(): void {
		unset($this->PaymentsTable);

		parent::tearDown();
	}

	/**
	 * Test validationAmount method
	 */
	public function testValidationAmount(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationPayment method
	 */
	public function testValidationPayment(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationRefund method
	 */
	public function testValidationRefund(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationCredit method
	 */
	public function testValidationCredit(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationTransferFrom method
	 */
	public function testValidationTransferFrom(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test validationTransferTo method
	 */
	public function testValidationTransferTo(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test beforeMarshal method
	 */
	public function testBeforeMarshal(): void {
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test affiliate method
	 */
	public function testAffiliate(): void {
        $affiliateId = mt_rand();
        $payment = PaymentFactory::make()->with('Registrations.Events', ['affiliate_id' => $affiliateId])->persist();
		$this->assertEquals($affiliateId, $this->PaymentsTable->affiliate($payment->id));
	}

}
