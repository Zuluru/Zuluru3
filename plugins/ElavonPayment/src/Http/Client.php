<?php

namespace ElavonPayment\Http;

use App\Exception\PaymentException;
use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\Log\Log;
use Psr\Log\LogLevel;

class Client {

	private $merchant_id;
	private $user_id;
	private $pin;
	private $purchaseEndpoint;
	private $refundEndpoint;

	public function __construct(bool $test) {
		if ($test) {
			$this->merchant_id = Configure::read('payment.elavon_test_merchant_id');
			$this->user_id = Configure::read('payment.elavon_test_merchant_user_id');
			$this->pin = Configure::read('payment.elavon_test_pin');
			$this->purchaseEndpoint = 'https://api.demo.convergepay.com/hosted-payments/transaction_token';
			$this->refundEndpoint = 'https://api.demo.convergepay.com/VirtualMerchantDemo/processxml.do';
		} else {
			$this->merchant_id = Configure::read('payment.elavon_live_merchant_id');
			$this->user_id = Configure::read('payment.elavon_live_merchant_user_id');
			$this->pin = Configure::read('payment.elavon_live_pin');
			$this->refundEndpoint = 'https://api.convergepay.com/VirtualMerchant/processxml.do';
		}
	}

	public function checkoutSessionCreate(array $fields): string {
		$fields = [
			'ssl_merchant_id' => $this->merchant_id,
			'ssl_user_id' => $this->user_id,
			'ssl_pin' => $this->pin,
		] + $fields;

		$response = $this->get($this->purchaseEndpoint, http_build_query($fields));
		if ($response[0] === '<') {
			Log::write(LogLevel::ERROR, "Elavon error: $response");
			throw new PaymentException('The Elavon server returned the following error message: ' . addslashes(trim($response)));
		}

		return $response;
	}

	public function refund(Event $event, Payment $payment, Payment $refund): string {
		$amount = -$refund->payment_amount;
		$xml = <<<EOXML
<txn>
	<ssl_merchant_id>{$this->merchant_id}</ssl_merchant_id>
	<ssl_user_id>{$this->user_id}</ssl_user_id>
	<ssl_pin>{$this->pin}</ssl_pin>
	<ssl_transaction_type>ccreturn</ssl_transaction_type>
	<ssl_txn_id>{$payment->registration_audit->transaction_id}</ssl_txn_id>
	<ssl_amount>{$amount}</ssl_amount>
</txn>
EOXML;

		$fields = "xmldata=$xml";

		$response = $this->get($this->refundEndpoint, $fields);
		if (substr($response, 0, 5) !== '<?xml') {
			Log::write(LogLevel::ERROR, "Elavon error: $response");
			throw new PaymentException('The Elavon server returned the following unexpected response: ' . addslashes(trim($response)));
		}

		return $response;
	}

	protected function get(string $endpoint, string $fields): string {
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
			CURLOPT_POSTFIELDS => $fields,
		];

		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$response = trim(curl_exec($ch));

		// Check for cURL errors
		if (curl_errno($ch)) {
			Log::write(LogLevel::ERROR, 'cURL error: ' . curl_error($ch));
			curl_close($ch);
			throw new PaymentException('There was a problem communicating with the Elavon server. Please try again shortly.');
		}

		curl_close($ch);

		return $response;
	}
}
