<?php
namespace PayPalPayment\Http;

use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Psr\Log\LogLevel;

class API extends \App\Http\API {

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

	public function SetExpressCheckout($fields) {
		return $this->fetch('SetExpressCheckout', $fields);
	}

	public function GetExpressCheckoutDetails($fields) {
		return $this->fetch('GetExpressCheckoutDetails', $fields);
	}

	public function DoExpressCheckoutPayment($fields) {
		return $this->fetch('DoExpressCheckoutPayment', $fields);
	}

	private function fetch($method, $fields) {
		if ($this->isTest()) {
			$login = Configure::read('payment.paypal_test_user');
			$key = Configure::read('payment.paypal_test_password');
			$signature = Configure::read('payment.paypal_test_signature');
			$endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		} else {
			$login = Configure::read('payment.paypal_live_user');
			$key = Configure::read('payment.paypal_live_password');
			$signature = Configure::read('payment.paypal_live_signature');
			$endpoint = 'https://api-3t.paypal.com/nvp';
		}

		$fields = [
			'USER' => $login,
			'PWD' => $key,
			'VERSION' => '124.0',
			'SIGNATURE' => $signature,
			'METHOD' => $method,
		] + $fields;

		// cURL settings
		$curlOptions = [
			CURLOPT_URL => $endpoint,
			CURLOPT_VERBOSE => 0,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POST => 1,
			// If we just use the fields array here, it seems to use the wrong post method
			CURLOPT_POSTFIELDS => http_build_query($fields),
		];

		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$response = curl_exec($ch);

		// Check for cURL errors
		if (curl_errno($ch)) {
			\Cake\Log\Log::write(LogLevel::ERROR, 'cURL error: ' . curl_error($ch));
			curl_close($ch);
			return 'There was a problem communicating with the PayPal server. Please try again shortly.';
		}

		curl_close($ch);
		parse_str($response, $responseArray); // Break the NVP string to an array
		if ($responseArray['ACK'] !== 'Success') {
			return "The PayPal server returned the following error message: {$responseArray['L_LONGMESSAGE0']}";
		}

		return $responseArray;
	}

	public function canRefund(Payment $payment): bool {
		return Configure::read('payment.paypal_refunds');
	}

}
