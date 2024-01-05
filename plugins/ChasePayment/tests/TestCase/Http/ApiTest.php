<?php

namespace ChasePayment\Test\TestCase\Http;

use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use App\Model\Entity\Registration;
use App\Test\Factory\EventFactory;
use App\Test\Factory\PaymentFactory;
use App\Test\Factory\RegistrationFactory;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;
use ChasePayment\Http\API;
use ChasePayment\Http\Client;
use Faker\Factory;

class ApiTest extends TestCase
{
	private $transaction_id;
	private $amount;

	public function testParsePayment() {
		$this->markTestSkipped('todo');
	}

	public function testRefund() {
		/** @var Event $event */
		$event = EventFactory::make()
			->with('Prices')
			->persist();
		$this->amount = $event->prices[0]->cost;
		$this->transaction_id = Factory::create('en_CA')->numberBetween(100000000, 999999999);

		/** @var Registration $registration */
		$registration = RegistrationFactory::make([
			'event_id' => $event->id,
			'price_id' => $event->prices[0]->id,
			'payment' => 'Paid',
			'total_amount' => $this->amount,
		])
			->with('Payments',
				PaymentFactory::make([
					'payment_amount' => $this->amount,
				])->with('RegistrationAudits', [
					'payment_plugin' => 'Chase',
					'transaction_id' => $this->transaction_id,
					'approval_code' => 'AB1234',
					'charge_total' => $this->amount,
				])
			)
			->persist();

		/** @var Payment $refund */
		$refund = PaymentFactory::make([
			'payment_type' => 'Refund',
			'payment_amount' => - $this->amount,
			'registration_id' => $registration->id,
			'payment_id' => $registration->payments[0]->id,
		])
			->getEntity();

		$client = $this->getMockBuilder(Client::class)
			->setConstructorArgs([true])
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->setMethods(['get'])
			->getMock();

		$client->method('get')
			->willReturnCallback([$this, 'returnJson']);

		$api = new API(true);
		$api->setClient($client);
		$data = $api->refund($event, $registration->payments[0], $refund);

		$this->assertEquals($this->amount, $data['charge_total']);
		$this->assertNotEquals($this->transaction_id, $data['transaction_id']);
		$this->assertEquals('RETURN', $data['approval_code']);
		$this->assertEquals('0', $data['response_code']);
	}

	public function returnJson(string $endpoint, string $data): string {
		// Confirm that the payload has been built as expected
		$this->assertEquals('https://api.demo.e-xact.com/transaction', $endpoint);
		$this->assertEquals("{\"transaction_type\":\"34\",\"transaction_tag\":\"{$this->transaction_id}\",\"authorization_num\":\"AB1234\",\"amount\":{$this->amount}}", $data);

		$time = FrozenTime::now()->format('d M y h:i:s');
		$exp = FrozenDate::now()->addMonth()->format('mY');
		$string_amount = sprintf('%0.2f', $this->amount);

		return json_encode([
			'merchant_url' => 'www.e-xact.com',
			'cc_number' => '############1111',
			'secure_auth_required' => null,
			'cc_verification_str2' => null,
			'zip_code' => null,
			'user_name' => null,
			'reference_no' => null,
			'cc_expiry' => $exp,
			'avs' => null,
			'client_email' => null,
			'secure_auth_result' => null,
			'cavv_response' => null,
			'bank_resp_code' => '000',
			'password' => null,
			'merchant_address' => '127 - 6768 Front St',
			'transaction_tag' => 902010341,
			'cardholder_name' => 'Donald Duck',
			'retrieval_ref_no' => '08185104',
			'gateway_id' => 'ExactID',
			'merchant_country' => 'Canada',
			'error_description' => ' ',
			'bank_message' => 'APPROVED',
			'cavv' => null,
			'track1' => null,
			'tax1_amount' => null,
			'reference_3' => null,
			'surcharge_amount' => null,
			'transaction_type' => '34',
			'ctr' => "=========== TRANSACTION RECORD ==========\nAPI Testing\n127 - 6768 Front St\nVancouver, BC V6B 2H7\nCanada\nwww.e-xact.com\n\n" .
				"TYPE: Refund\n\nACCT: Visa  $ {$string_amount} CAD\n\nCARD NUMBER : ############1111\n" .
				"DATE/TIME   : $time\nREFERENCE # : 002 025851 M\nAUTHOR. #   : RETURN\nTRANS. REF. : \n\n" .
				"    Approved - Thank You 000\n\n\nPlease retain this copy for your records.\n\n=========================================",
			'ecommerce_flag' => 0,
			'bank_resp_code_2' => null,
			'language' => null,
			'merchant_city' => 'Vancouver',
			'logon_message' => null,
			'tax2_amount' => null,
			'track2' => null,
			'transaction_approved' => 1,
			'merchant_postal' => 'V6B 2H7',
			'transaction_error' => 0,
			'cvd_presence_ind' => 0,
			'xid' => null,
			'pan' => null,
			'tax1_number' => null,
			'exact_resp_code' => '00',
			'customer_ref' => null,
			'amount' => $this->amount,
			'cavv_algorithm' => null,
			'cvv2' => null,
			'cc_verification_str1' => null,
			'sequence_no' => '025851',
			'merchant_name' => 'API Testing (Chase)',
			'client_ip' => '10.1.1.20',
			'merchant_province' => 'British Columbia',
			'error_number' => 0,
			'tax2_number' => null,
			'authorization_num' => 'RETURN',
			'exact_message' => 'Transaction Normal',
		]);
	}
}
