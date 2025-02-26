<?php

namespace PayPalPayment\Test\TestCase\Http;

use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use App\Model\Entity\Registration;
use App\Test\Factory\EventFactory;
use App\Test\Factory\PaymentFactory;
use App\Test\Factory\RegistrationFactory;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;
use PayPalPayment\Http\API;
use PayPalPayment\Http\Client;
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
		$event = EventFactory::make(['name' => 'Test Event'])
			->with('Prices')
			->persist();
		$this->amount = $event->prices[0]->cost;
		$this->transaction_id = Factory::create('en_CA')->regexify('^([A-Z0-9]){17}$');

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
					'payment_plugin' => 'PayPal',
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
			'notes' => 'Refund notes go here',
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
		$this->assertEquals('73S7057568258952G', $data['transaction_id']);
		$this->assertEquals('instant', $data['message']);
	}

	public function returnJson(string $method, array $data): array {
		// Confirm that the payload has been built as expected
		$this->assertEquals('RefundTransaction', $method);
		$expected = [
			'TRANSACTIONID' => $this->transaction_id,
			'NOTE' => 'Refund on Test Event (Refund notes go here)',
			'REFUNDTYPE' => 'Full',
		];
		$this->assertEquals($expected, $data);

		return [
			'REFUNDTRANSACTIONID' => '73S7057568258952G',
			'GROSSREFUNDAMT' => $this->amount,
			'REFUNDSTATUS' => 'instant',
			'TIMESTAMP' => FrozenTime::now()->format('Y-m-d\TH:i:s\Z'),
		];
	}
}
