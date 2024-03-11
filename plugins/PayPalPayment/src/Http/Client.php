<?php

namespace PayPalPayment\Http;

use App\Exception\PaymentException;
use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Utility\Text;
use Psr\Log\LogLevel;

class Client {
	private $login;
	private $key;
	private $signature;
	private $endpoint;

	public function __construct(bool $test) {
		if ($test) {
			$this->login = Configure::read('payment.paypal_test_user');
			$this->key = Configure::read('payment.paypal_test_password');
			$this->signature = Configure::read('payment.paypal_test_signature');
			$this->endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
		} else {
			$this->login = Configure::read('payment.paypal_live_user');
			$this->key = Configure::read('payment.paypal_live_password');
			$this->signature = Configure::read('payment.paypal_live_signature');
			$this->endpoint = 'https://api-3t.paypal.com/nvp';
		}
	}

	/**
	 * See https://developer.paypal.com/api/nvp-soap/refund-transaction-nvp/
	 */
	public function refund(Event $event, Payment $payment, Payment $refund): array {
		$fields['TRANSACTIONID'] = $payment->registration_audit->transaction_id;
		$fields['NOTE'] = __('Refund on {0}', $event->name);
		if (!empty($refund->notes)) {
			$fields['NOTE'] .= " ({$refund->notes})";
		}
		$fields['NOTE'] = Text::truncate($fields['NOTE'], 255);

		if ($payment->payment_amount === - $refund->payment_amount) {
			$fields += [
				'REFUNDTYPE' => 'Full',
			];
		} else {
			$fields += [
				'REFUNDTYPE' => 'Partial',
				'AMT' => - $refund->payment_amount
			];
		}

		return $this->get('RefundTransaction', $fields);
	}

	public function get(string $method, array $fields): array {
		$fields = [
			'USER' => $this->login,
			'PWD' => $this->key,
			'VERSION' => '124.0',
			'SIGNATURE' => $this->signature,
			'METHOD' => $method,
		] + $fields;

		// cURL settings
		$curlOptions = [
			CURLOPT_URL => $this->endpoint,
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
			Log::write(LogLevel::ERROR, 'cURL error: ' . curl_error($ch));
			curl_close($ch);
			throw new PaymentException('There was a problem communicating with the PayPal server. Please try again shortly.');
		}

		curl_close($ch);
		parse_str($response, $responseArray); // Break the NVP string to an array
		if ($responseArray['ACK'] !== 'Success') {
			throw new PaymentException("The PayPal server returned the following error message: {$responseArray['L_LONGMESSAGE0']}");
		}

		return $responseArray;
	}
}
