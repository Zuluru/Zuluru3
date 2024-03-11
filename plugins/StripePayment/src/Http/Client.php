<?php

namespace StripePayment\Http;

use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;

class Client
{
	/**
	 * @var StripeClient
	 */
	private $client = null;
	private $key;
	private $endpoint_secret;

	public function __construct(bool $test) {
		if ($test) {
			$this->key = Configure::read('payment.stripe_test_secret_key');
			$this->endpoint_secret = Configure::read('payment.stripe_test_webhook_signing');
		} else {
			$this->key = Configure::read('payment.stripe_live_secret_key');
			$this->endpoint_secret = Configure::read('payment.stripe_live_webhook_signing');
		}

		$this->client = new StripeClient($this->key);
	}

	/**
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function checkoutSessionCreate(array $options): Session {
		return $this->client->checkout->sessions->create($options);
	}

	/**
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function paymentIntentsRetrieve(string $intent): PaymentIntent {
		return $this->client->paymentIntents->retrieve($intent);
	}

	/**
	 * @throws \Stripe\Exception\SignatureVerificationException
	 */
	public function constructEvent(string $input): Event {
		Stripe::setApiKey($this->key);
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

		return Webhook::constructEvent(
			$input, $sig_header, $this->endpoint_secret
		);
	}

	/**
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function getRegistrationIds(string $id): array {
		$line_items = Session::allLineItems($id);
		$registration_ids = $debit_ids = [];
		foreach ($line_items->data as $item) {
			$product = $this->client->products->retrieve($item->price->product);
			if ($product->metadata->offsetExists('registration_id')) {
				$registration_ids[] = $product->metadata->registration_id;
			} else if ($product->metadata->offsetExists('debit_id')) {
				$debit_ids[] = $product->metadata->debit_id;
			}
		}

		return [$registration_ids, $debit_ids];
	}

	/**
	 * See https://docs.stripe.com/api/refunds
	 * @throws \Stripe\Exception\ApiErrorException
	 */
	public function refund(\App\Model\Entity\Event $event, Payment $payment, Payment $refund): Refund {
		return $this->client->refunds->create([
			'amount' => -$refund->payment_amount * 100,
			'charge' => $payment->registration_audit->transaction_id,
			'reason' => 'requested_by_customer',
		]);
	}

}
