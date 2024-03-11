<?php
namespace StripePayment\Http;

use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Stripe\Charge;
use Stripe\Checkout\Session;

class API extends \App\Http\API {

	/**
	 * @var Client
	 */
	private $client = null;

	public function setClient(Client $client) {
		$this->client = $client;
	}

	private function client(): Client {
		if (!$this->client) {
			$this->client = new Client($this->isTest());
		}

		return $this->client;
	}

	public static function isTestData($data): bool {
		$data = json_decode($data, true);
		return !$data['livemode'];
	}

	/**
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function checkoutSessionCreate(array $options): Session {
		return $this->client()->checkoutSessionCreate($options);
	}

	/**
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function parsePayment(string $input): array {
		try {
			$event = $this->client()->constructEvent($input);
		} catch(\UnexpectedValueException $ex) {
			// Invalid payload
			return [false, ['message' => $ex->getMessage()], [], []];
		} catch(\Stripe\Exception\SignatureVerificationException $ex) {
			// Invalid signature
			return [false, ['message' => $ex->getMessage()], [], []];
		}

		/** @var Session $session */
		$session = $event->data->object;
		if ($event->type === 'checkout.session.completed') {
			$payment = $this->client()->paymentIntentsRetrieve($session->payment_intent);
			if ($payment->status === 'succeeded') {
				/** @var Charge $charge */
				$charge = $payment->charges->data[0];

				$audit = [
					'payment_plugin' => 'Stripe',
					'order_id' => $session->client_reference_id,
					'response_code' => '0',
					'transaction_id' => $session->id,
					'transaction_name' => $charge->payment_method_details->card->funding,
					'charge_total' => $payment->amount / 100,
					'cardholder' => $charge->billing_details->name,
					'expiry' => sprintf('%02d %04d', $charge->payment_method_details->card->exp_month, $charge->payment_method_details->card->exp_year),
					'f4l4' => '***' . $charge->payment_method_details->card->last4,
					'card' => $charge->payment_method_details->card->brand,
					'message' => $charge->outcome->seller_message,
					'date' => date('Y-m-d', $payment->created),
					'time' => date('H:i:s', $payment->created),
				];

				[$registration_ids, $debit_ids] = $this->client()->getRegistrationIds($session->id);

				return [true, $audit, $registration_ids, $debit_ids];
			}
		}

		return [true, [], [], []];
	}

	public function canRefund(Payment $payment): bool {
		return Configure::read('payment.stripe_refunds');
	}

	public function refund(Event $event, Payment $payment, Payment $refund): array {
		$response = $this->client()->refund($event, $payment, $refund);
		$time = FrozenTime::createFromTimestamp($response->created);

		// Build an audit record for the refund response
		// Retrieve the parameters sent from the server
		return [
			'payment_plugin' => 'Stripe',
			'transaction_id' => $response->id,
			'message' => $response->status,
			'charge_total' => $response->amount,
			'issuer_invoice' => $response->destination_details->card->reference,
			'transaction_name' => $response->destination_details->card->type,
			'date' => $time->format('M d y'),
			'time' => $time->format('h:i:s'),
		];
	}

}
