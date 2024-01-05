<?php

namespace ElavonPayment\Test\TestCase\Http;

use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use App\Model\Entity\Registration;
use App\Test\Factory\EventFactory;
use App\Test\Factory\PaymentFactory;
use App\Test\Factory\RegistrationFactory;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;
use ElavonPayment\Http\API;
use ElavonPayment\Http\Client;
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
		$this->transaction_id = Factory::create('en_CA')->regexify('^([A-F0-9]){9}-([A-F0-9]){8}-([A-F0-9]){4}-([A-F0-9]){4}-([A-F0-9]){4}-([A-F0-9]){12}$');

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
					'payment_plugin' => 'Elavon',
					'transaction_id' => $this->transaction_id,
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
			->willReturnCallback([$this, 'returnXml']);

		$api = new API(true);
		$api->setClient($client);
		$data = $api->refund($event, $registration->payments[0], $refund);

		$this->assertEquals($this->amount, $data['charge_total']);
		$this->assertEquals($this->transaction_id, $data['transaction_id']);
		$this->assertEquals('401054', $data['approval_code']);
		$this->assertEquals('0', $data['response_code']);
		$this->assertEquals('12345', $data['order_id']);
	}

	public function returnXml(string $endpoint, string $data): string {
		// Confirm that the payload has been built as expected
		$this->assertEquals('https://api.demo.convergepay.com/VirtualMerchantDemo/processxml.do', $endpoint);
		$this->assertEquals("xmldata=<txn>\n\t<ssl_merchant_id></ssl_merchant_id>\n\t<ssl_user_id></ssl_user_id>\n\t<ssl_pin></ssl_pin>\n\t<ssl_transaction_type>ccreturn</ssl_transaction_type>\n\t<ssl_txn_id>{$this->transaction_id}</ssl_txn_id>\n\t<ssl_amount>{$this->amount}</ssl_amount>\n</txn>", $data);

		$time = FrozenTime::now()->format('m/d/Y h:i:s A');
		$exp = FrozenDate::now()->addMonth()->format('mY');

		return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><txn>
				<ssl_issuer_response>00</ssl_issuer_response>
				<ssl_last_name>Test Last</ssl_last_name>
				<ssl_company></ssl_company>
				<ssl_phone></ssl_phone>
				<ssl_card_number>41**********9990</ssl_card_number>
				<ssl_departure_date></ssl_departure_date>
				<ssl_result>0</ssl_result>
				<ssl_txn_id>{$this->transaction_id}</ssl_txn_id>
				<ssl_avs_response> </ssl_avs_response>
				<ssl_approval_code>401054</ssl_approval_code>
				<ssl_email></ssl_email>
				<ssl_amount>{$this->amount}</ssl_amount>
				<ssl_avs_zip>12345</ssl_avs_zip>
				<ssl_txn_time>$time</ssl_txn_time>
				<ssl_description></ssl_description>
				<ssl_exp_date>$exp</ssl_exp_date>
				<ssl_completion_date></ssl_completion_date>
				<ssl_address2></ssl_address2>
				<ssl_card_short_description>VISA</ssl_card_short_description>
				<ssl_customer_code></ssl_customer_code>
				<ssl_country></ssl_country>
				<ssl_card_type>CREDITCARD</ssl_card_type>
				<ssl_transaction_type>RETURN</ssl_transaction_type>
				<ssl_salestax></ssl_salestax>
				<ssl_avs_address>test road</ssl_avs_address>
				<ssl_account_balance>0.00</ssl_account_balance>
				<ssl_state></ssl_state>
				<ssl_city></ssl_city>
				<ssl_result_message>APPROVAL</ssl_result_message>
				<ssl_first_name>Test First</ssl_first_name>
				<ssl_invoice_number>12345</ssl_invoice_number>
				<ssl_cvv2_response></ssl_cvv2_response>
			</txn>";
	}
}
