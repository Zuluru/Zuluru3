<?php
namespace PayPalPayment\Http;

use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use Cake\Core\Configure;

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

	/**
	 * @param array $data
	 * @return array
	 */
	public function parsePayment(Array $data) {
		$details = $this->GetExpressCheckoutDetails(['TOKEN' => $data['token']]);
		if (!is_array($details)) {
			return [false, ['message' => $details], [], []];
		}

		$response = $this->DoExpressCheckoutPayment([
			'PAYMENTACTION' => 'Sale',
			'PAYERID' => $details['PAYERID'],
			'TOKEN' => $details['TOKEN'],
			'PAYMENTREQUEST_0_AMT' => $details['PAYMENTREQUEST_0_AMT'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => $details['PAYMENTREQUEST_0_CURRENCYCODE'],
		]);
		if (!is_array($response)) {
			return [false, ['message' => $response], [], []];
		}

		// Retrieve the parameters sent from the server
		$audit = [
			'payment_plugin' => 'PayPal',
			'order_id' => $details['PAYMENTREQUEST_0_INVNUM'],
			'charge_total' => $details['PAYMENTREQUEST_0_AMT'],
			'cardholder' => "{$details['FIRSTNAME']} {$details['LASTNAME']}",
			'response_code' => $response['PAYMENTINFO_0_ERRORCODE'],
			'transaction_id' => $response['PAYMENTINFO_0_TRANSACTIONID'],
			'transaction_name' => "{$response['PAYMENTINFO_0_TRANSACTIONTYPE']}:{$response['PAYMENTINFO_0_PAYMENTTYPE']}",
		];

		if (array_key_exists('NOTE', $response)) {
			$audit['message'] = $response['NOTE'];
		}

		preg_match ('#(\d{4}-\d{2}-\d{2})T(\d+:\d+:\d+)Z#', $response['TIMESTAMP'], $matches);
		$audit['date'] = $matches[1];
		$audit['time'] = $matches[2];

		// Validate the response code
		if ($response['PAYMENTINFO_0_ERRORCODE'] == 0) {
			[$user_id, $registration_ids] = explode(':', $details['PAYMENTREQUEST_0_CUSTOM']);
			[$registration_ids, $debit_ids] = $this->splitRegistrationIds($registration_ids);
			return [true, $audit, $registration_ids, $debit_ids];
		} else {
			return [false, $audit, [], []];
		}
	}

	/**
	 * See https://developer.paypal.com/api/nvp-soap/set-express-checkout-nvp/
	 */
	public function SetExpressCheckout($fields) {
		return $this->client()->get('SetExpressCheckout', $fields);
	}

	/**
	 * See https://developer.paypal.com/api/nvp-soap/get-express-checkout-details-nvp/
	 */
	public function GetExpressCheckoutDetails($fields) {
		return $this->client()->get('GetExpressCheckoutDetails', $fields);
	}

	/**
	 * See https://developer.paypal.com/api/nvp-soap/do-express-checkout-payment-nvp/
	 */
	public function DoExpressCheckoutPayment($fields) {
		return $this->client()->get('DoExpressCheckoutPayment', $fields);
	}

	public function canRefund(Payment $payment): bool {
		return Configure::read('payment.paypal_refunds');
	}

	public function refund(Event $event, Payment $payment, Payment $refund): array {
		$data = $this->client()->refund($event, $payment, $refund);

		return $this->parseRefund($data);
	}

	public function parseRefund(array $data): array {
		// Retrieve the parameters sent from the server
		$audit = ['payment_plugin' => 'PayPal'];

		foreach ([
			'transaction_id' => 'REFUNDTRANSACTIONID',
			'charge_total' => 'GROSSREFUNDAMT',
			'message' => 'REFUNDSTATUS',
		] as $field => $key) {
			if (array_key_exists($key, $data)) {
				$audit[$field] = $data[$key];
			}
		}

		return $audit;
	}

}
