<?php
namespace ElavonPayment\Http;

use App\Exception\PaymentException;
use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use Cake\Core\Configure;
use Cake\Log\Log;
use Psr\Log\LogLevel;

class API extends \App\Http\API {

	/**
	 * @var Client
	 */
	private $client = null;

	public function setClient(Client $client): API {
		$this->client = $client;
		return $this;
	}

	private function client(): Client {
		if (!$this->client) {
			$this->client = new Client($this->isTest());
		}

		return $this->client;
	}

	public function checkoutSessionCreate(array $fields): string {
		return $this->client()->checkoutSessionCreate($fields);
	}

	public function parsePayment(array $data): array {
		if (array_key_exists('errorCode', $data)) {
			return [false, ['message' => $data['errorMessage']], [], []];
		}

		// Retrieve the parameters sent from the server
		$audit = ['payment_plugin' => 'Elavon'];
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

		if (preg_match('#(\d+/\d+/\d+) (\d+:\d+:\d+ [AP]M)#im', urldecode($data['ssl_txn_time'] ?? ''), $matches)) {
			$audit['date'] = $matches[1];
			$audit['time'] = $matches[2];
		}

		// Validate the response code
		if ($audit['response_code'] == 0) {
			[$registration_ids, $debit_ids] = $this->splitRegistrationIds($data['ssl_description'] ?? '');
			return [true, $audit, $registration_ids, $debit_ids];
		} else {
			return [false, $audit, [], []];
		}
	}

	public function canRefund(Payment $payment): bool {
		return Configure::read('payment.elavon_refunds');
	}

	public function refund(Event $event, Payment $payment, Payment $refund): array {
		$response = $this->client()->refund($event, $payment, $refund);

		// Parse the response
		$xml = simplexml_load_string($response);
		if (empty($xml)) {
			Log::write(LogLevel::ERROR, "Elavon error: $response");
			throw new PaymentException('The Elavon server returned an unexpected response: ' . addslashes(trim($response)));
		}

		$error = (string)$xml->errorCode;
		if ($error !== '') {
			$name = trim((string)$xml->errorName);
			$message = trim((string)$xml->errorMessage);
			Log::write(LogLevel::ERROR, "Elavon error: $name: $message");
			throw new PaymentException('The Elavon server returned an unexpected response: ' . addslashes("$name: $message"));
		}

		// Build an audit record for the refund response
		// Retrieve the parameters sent from the server
		$audit = ['payment_plugin' => 'Elavon'];
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
			$value = (string)$xml->$key;
			if ($value !== '') {
				$audit[$field] = $value;
			}
		}

		if (preg_match('#(\d+/\d+/\d+) (\d+:\d+:\d+ [AP]M)#im', (string)$xml->ssl_txn_time, $matches)) {
			$audit['date'] = $matches[1];
			$audit['time'] = $matches[2];
		}

		return $audit;
	}

}
