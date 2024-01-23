<?php

namespace ChasePayment\Http;

use App\Exception\PaymentException;
use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\Log\Log;
use Psr\Log\LogLevel;

class Client {
	private $login;
	private $key;
	private $refundEndpoint;

	public function __construct(bool $test) {
		if ($test) {
			$this->login = Configure::read('payment.chase_test_gateway_id');
			$this->key = Configure::read('payment.chase_test_gateway_password');
			$this->refundEndpoint = 'https://api.demo.e-xact.com/transaction';
		} else {
			$this->login = Configure::read('payment.chase_live_gateway_id');
			$this->key = Configure::read('payment.chase_live_gateway_password');
			$this->refundEndpoint = 'https://api.e-xact.com/transaction';
		}
	}

	/**
	 * See https://support.exactpay.com/support/solutions/articles/150000065331-transaction-processing-api-reference-guide#1.2.3
	 */
	public function refund(Event $event, Payment $payment, Payment $refund): string {
		$fields = json_encode([
			// Transaction types documented at https://support.exactpay.com/support/solutions/articles/150000065330-transaction-types-available
			'transaction_type' => $payment->registration_audit->transaction_name === 'idebit_purchase' ? '35' : '34',
			'transaction_tag' => $payment->registration_audit->transaction_id,
			'authorization_num' => $payment->registration_audit->approval_code,
			'amount' => -$refund->payment_amount,
		]);

		$response = $this->get($this->refundEndpoint, $fields);

		if ($response[0] !== '{') {
			Log::write(LogLevel::ERROR, "Chase error: $response");
			throw new PaymentException('The Chase server returned the following unexpected response: ' . addslashes(trim($response)));
		}

		return $response;
	}

	protected function get(string $endpoint, string $fields): string {
		// cURL settings
		$curlOptions = [
			CURLOPT_URL => $endpoint,
			CURLOPT_USERPWD => "{$this->login}:{$this->key}",
			CURLOPT_VERBOSE => 0,
			CURLOPT_SSL_VERIFYPEER => true,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTPHEADER => ['Content-Type:application/json', 'Accept:application/json'],
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $fields,
		];

		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$response = trim(curl_exec($ch));

		// Check for cURL errors
		if (curl_errno($ch)) {
			Log::write(LogLevel::ERROR, 'cURL error: ' . curl_error($ch));
			curl_close($ch);
			throw new PaymentException('There was a problem communicating with the Chase server. Please try again shortly.');
		}

		curl_close($ch);

		return $response;
	}
}
