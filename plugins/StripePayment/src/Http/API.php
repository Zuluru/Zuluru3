<?php
namespace StripePayment\Http;

use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Stripe\StripeClient;

class API extends \App\Http\API {

	/**
	 * @var StripeClient
	 */
	private $client = null;

	/**
	 * @return bool
	 */
	public static function isTestData($data): bool {
		$data = json_decode($data, true);
		return !$data['livemode'];
	}

	/**
	 * @return StripeClient
	 */
	private function client() {
		if (!$this->client) {
			if ($this->isTest()) {
				$key = Configure::read('payment.stripe_test_secret_key');
			} else {
				$key = Configure::read('payment.stripe_live_secret_key');
			}

			$this->client = new \Stripe\StripeClient($key);
		}

		return $this->client;
	}

	/**
	 * @param array $options
	 * @return \Stripe\Checkout\Session
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function checkoutSessionCreate(array $options) {
		return $this->client()->checkout->sessions->create($options);
	}

	/**
	 * @param $input
	 * @return array
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function parsePayment($input) {
		try {
			$event = $this->constructEvent($input);
		} catch(\UnexpectedValueException $ex) {
			// Invalid payload
			return [false, ['message' => $ex->getMessage()], [], []];
		} catch(\Stripe\Exception\SignatureVerificationException $ex) {
			// Invalid signature
			return [false, ['message' => $ex->getMessage()], [], []];
		}

		$session = $event->data->object;
		if ($event->type === 'checkout.session.completed') {
			$payment = $this->paymentIntentsRetrieve($session->payment_intent);
			if ($payment->status === 'succeeded') {
				$charge = $payment->charges->data[0];

				$audit = [
					'payment_plugin' => 'Stripe',
					'order_id' => $session->client_reference_id,
					'response_code' => '0',
					'transaction_id' => '0',
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

				[$registration_ids, $debit_ids] = $this->getRegistrationIds($session->id);

				return [true, $audit, $registration_ids, $debit_ids];
			}
		}

		return [true, [], [], []];
	}

	/**
	 * @param $input
	 * @return \Stripe\Event
	 * @throws \Stripe\Exception\SignatureVerificationException
	 */
	public function constructEvent($input) {
		if ($this->isTest()) {
			$key = Configure::read('payment.stripe_test_secret_key');
			$endpoint_secret = Configure::read('payment.stripe_test_webhook_signing');
		} else {
			$key = Configure::read('payment.stripe_live_secret_key');
			$endpoint_secret = Configure::read('payment.stripe_live_webhook_signing');
		}

		\Stripe\Stripe::setApiKey($key);
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

		return \Stripe\Webhook::constructEvent(
			$input, $sig_header, $endpoint_secret
		);
	}

	/**
	 * @param string $intent
	 * @return \Stripe\PaymentIntent
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function paymentIntentsRetrieve($intent) {
		return $this->client()->paymentIntents->retrieve($intent);
	}

	/**
	 * @param $id
	 * @return array
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function getRegistrationIds($id) {
		$line_items = \Stripe\Checkout\Session::allLineItems($id);
		$registration_ids = $debit_ids = [];
		foreach ($line_items->data as $item) {
			$product = $this->client()->products->retrieve($item->price->product);
			if ($product->metadata->offsetExists('registration_id')) {
				$registration_ids[] = $product->metadata->registration_id;
			} else if ($product->metadata->offsetExists('debit_id')) {
				$debit_ids[] = $product->metadata->debit_id;
			}
		}

		return [$registration_ids, $debit_ids];
	}

	public function canRefund(Payment $payment): bool {
		return Configure::read('payment.stripe_refunds');
	}

}
