<?php
namespace Stripe\Controller;

use App\Controller\PaymentsTrait;
use Cake\Core\Configure;

/**
 *  Controller for handling payments from the Stripe Checkout system.
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class PaymentController extends AppController {

	use PaymentsTrait;

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		return ['index'];
	}

	/**
	 * Initialization hook method.
	 *
	 * Use this method to add common initialization code like loading components.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function initialize() {
		parent::initialize();
		$this->loadModel('Registrations');
	}

	// TODO: Proper fix for black-holing of payment details posted to us from processors
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		if (isset($this->Security)) {
			$this->Security->setConfig('unlockedActions', ['index']);
		}
	}

	public function index() {
		// Stripe sends data back through an event
		[$result, $audit, $registration_ids] = $this->_parsePost($this->request->input());
		$this->_processPayment($result, $audit, $registration_ids);

		if (!$result) {
			return $this->response->withStatus(400);
		}
	}

	private function _parsePost($input) {
		$data = json_decode($input, true);

		if ($data['livemode']) {
			$key = Configure::read('payment.stripe_live_secret_key');
			$endpoint_secret = Configure::read('payment.stripe_live_webhook_signing');
		} else {
			$key = Configure::read('payment.stripe_test_secret_key');
			$endpoint_secret = Configure::read('payment.stripe_test_webhook_signing');
		}

		\Stripe\Stripe::setApiKey($key);
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
		$event = null;

		try {
			$event = \Stripe\Webhook::constructEvent(
				$input, $sig_header, $endpoint_secret
			);
		} catch(\UnexpectedValueException $ex) {
			// Invalid payload
			return [false, ['message' => $ex->getMessage()], []];
		} catch(\Stripe\Exception\SignatureVerificationException $ex) {
			// Invalid signature
			return [false, ['message' => $ex->getMessage()], []];
		}

		$session = $event->data->object;
		if ($event->type == 'checkout.session.completed') {
			$stripe = new \Stripe\StripeClient($key);
			$payment = $stripe->paymentIntents->retrieve($session->payment_intent);
			if ($payment->status == 'succeeded') {
				$charge = $payment->charges->data[0];

				$audit = [
					'order_id' => $session->client_reference_id,
					'response_code' => '0',
					'transaction_id' => '0',
					'transaction_name' => $charge->payment_method_details->card->funding,
					'charge_total' => $payment->amount,
					'cardholder' => $charge->billing_details->name,
					'expiry' => sprintf('%02d %04d', $charge->payment_method_details->card->exp_month, $charge->payment_method_details->card->exp_year),
					'f4l4' => '***' . $charge->payment_method_details->card->last4,
					'card' => $charge->payment_method_details->card->brand,
					'message' => $charge->outcome->seller_message,
					'date' => date('Y-m-d', $payment->created),
					'time' => date('H:i:s', $payment->created),
				];

				$line_items = \Stripe\Checkout\Session::allLineItems($session->id);
				$registration_ids = [];
				foreach ($line_items->data as $item) {
					$product = $stripe->products->retrieve($item->price->product);
					$registration_ids[] = $product->metadata->registration_id;
				}

				return [true, $audit, $registration_ids];
			}
		}

		return [true, [], []];
	}

	public function success() {
		$this->Authorization->authorize($this);
		$this->Flash->success(__('Your payment has been recorded. It may take a short time to update in this system. If your registration remains unpaid for more than a few minutes, please contact an administrator. Do not re-pay unless you can confirm that no payment has been applied to your card or bank account.'));
		return $this->redirect(['plugin' => false, 'controller' => 'People', 'action' => 'splash']);
	}

}
