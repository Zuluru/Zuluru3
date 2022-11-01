<?php
namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Payment;
use App\Test\Factory\PaymentFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class PaymentTest extends TestCase {

	/**
	 * Test _getPaid() method
	 */
	public function testGetPaid(): void {
	    $payment = PaymentFactory::make([
	        'payment_amount' => 20,
            'refunded_amount' => 8.5,
        ])->getEntity();
		$this->assertEquals(11.50, $payment->paid);
	}

}
