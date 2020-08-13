<?php
namespace PayPal\Http;

use App\Controller\RegistrationsController;
use Cake\Core\Configure;
use Psr\Log\LogLevel;

class API {

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
		if (RegistrationsController::isTest()) {
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
			CURLOPT_SSLVERSION => 6,
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
		if ($responseArray['ACK'] != 'Success') {
			return "The PayPal server returned the following error message: {$responseArray['L_LONGMESSAGE0']}";
		}

		return $responseArray;
	}

}
