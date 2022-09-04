<?php
namespace ElavonPayment\Http;

use Cake\Core\Configure;
use Psr\Log\LogLevel;

class API extends \App\Http\API {

	/**
	 * @param array $data
	 * @return string
	 */
	public function checkoutSessionCreate(array $fields) {
		if ($this->isTest()) {
			$merchant_id = Configure::read('payment.elavon_test_merchant_id');
			$user_id = Configure::read('payment.elavon_test_merchant_user_id');
			$pin = Configure::read('payment.elavon_test_pin');
			$endpoint = 'https://api.demo.convergepay.com/hosted-payments/transaction_token';
		} else {
			$merchant_id = Configure::read('payment.elavon_live_merchant_id');
			$user_id = Configure::read('payment.elavon_live_merchant_user_id');
			$pin = Configure::read('payment.elavon_live_pin');
			$endpoint = 'https://api.convergepay.com/hosted-payments/transaction_token';
		}

		$fields = [
			'ssl_merchant_id' => $merchant_id,
			'ssl_user_id' => $user_id,
			'ssl_pin' => $pin,
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
			return 'There was a problem communicating with the Elavon server. Please try again shortly.';
		}

		curl_close($ch);

		if ($response[0] === '<') {
			return 'The Elavon server returned the following error message: ' . addslashes(trim($response));
		}

		return ['token' => $response];
	}

	/**
	 * @return array
	 */
	public function parsePayment(Array $data) {
		if (array_key_exists('errorCode', $data)) {
			return [false, ['message' => $data['errorMessage']], []];
		}

		// Retrieve the parameters sent from the server
		$audit = [];
		foreach ([
			'transaction_id' => 'ssl_txn_id',
			'approval_code' => 'ssl_approval_code',
			'response_code' => 'ssl_result',
			'message' => 'ssl_result_message',
			'charge_total' => 'ssl_amount',
			'order_id' => 'ssl_invoice_number',
			'card' => 'ssl_card_short_description',
			'issuer_invoice' => 'ssl_invoice_number',
			'transaction_name' => 'ssl_transaction_type',
			'f4l4' => 'ssl_card_number',
		] as $field => $key) {
			if (array_key_exists($key, $data)) {
				$audit[$field] = $data[$key];
			}
		}

		if (preg_match('#(\d+/\d+/\d+) (\d+:\d+:\d+ [AP]M)#im', urldecode($data['ssl_txn_time']), $matches)) {
			$audit['date'] = $matches[1];
			$audit['time'] = $matches[2];
		}

		$registration_ids = explode(',', $data['ssl_description']);

		return [true, $audit, $registration_ids];
	}

}
