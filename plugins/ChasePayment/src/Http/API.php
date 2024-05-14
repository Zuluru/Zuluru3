<?php
namespace ChasePayment\Http;

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

	public function parsePayment(array $data, bool $checkHash = true): array {
		// Retrieve the parameters sent from the server
		$audit = ['payment_plugin' => 'Chase'];
		foreach ([
			'order_id' => 'Reference_No',
			'response_code' => 'Bank_Resp_Code',
			'iso_code' => 'x_response_code',
			'transaction_id' => 'x_trans_id',
			'approval_code' => 'x_auth_code',
			'charge_total' => 'x_amount',
			'cardholder' => 'CardHoldersName',
			'expiry' => 'Expiry_Date',
			'f4l4' => 'Card_Number',
			'card' => 'TransactionCardType',
			'message' => 'Bank_Message',
		] as $field => $key) {
			if (array_key_exists($key, $data)) {
				$audit[$field] = $data[$key];
			}
		}

		// See if we can get a better card number from the receipt
		if (preg_match ('#CARD NUMBER : ([\*\#]+\d+)#im', $data['exact_ctr'], $matches)) {
			$audit['f4l4'] = $matches[1];
		}

		// Second method of getting the charge total, if we didn't get it earlier
		if (empty($audit['charge_total']) && preg_match ('#ACCT: ([A-Za-z]+) * \$ ([0-9,]+\.[0-9]{2}) CAD#im', $data['exact_ctr'], $matches)) {
			$audit['card'] = strtoupper($matches[1]);
			$total_arr = explode(',', $matches[2]);
			$audit['charge_total'] = 0;
			while (!empty($total_arr)) {
				$audit['charge_total'] *= 1000;
				$audit['charge_total'] += array_shift($total_arr);
			}
		}

		// TODO: no better way to get these from the response?
		if (stripos ($audit['card'], 'interac') === false) {
			$audit['transaction_name'] = 'purchase';
			$audit['issuer'] = $audit['issuer_invoice'] = $audit['issuer_confirmation'] = '';
		} else {
			$audit['transaction_name'] = 'idebit_purchase';
			$audit['issuer'] = $data['exact_issname'];
			$audit['issuer_invoice'] = $data['x_invoice_num'];
			$audit['issuer_confirmation'] = $data['exact_issconf'];
		}
		if (preg_match ('#DATE/TIME *: (\d+ [a-z]{3} \d+) (\d+:\d+:\d+)#im', $data['exact_ctr'], $matches)) {
			$audit['date'] = $matches[1];
			$audit['time'] = $matches[2];
		}
		if (empty($audit['approval_code']) && preg_match ('#AUTHOR. \# *: ([0-9A-Z]{6})#im', $data['exact_ctr'], $matches)) {
			$audit['approval_code'] = $matches[1];
		}

		if ($checkHash) {
			// Validate the hash
			if ($this->isTest()) {
				$login = Configure::read('payment.chase_test_store');
				$key = Configure::read('payment.chase_test_response');
			} else {
				$login = Configure::read('payment.chase_live_store');
				$key = Configure::read('payment.chase_live_response');
			}
			$calculated_hash = md5("$key$login{$audit['transaction_id']}{$audit['charge_total']}");

			// Validate the response code
			if ($audit['iso_code'] != 1 || $data['x_MD5_Hash'] != $calculated_hash) {
				return [false, $audit, [], []];
			}
		} else if (!array_key_exists('response_code', $audit)) {
			$audit['response_code'] = '0';
		}

		[$registration_ids, $debit_ids] = $this->splitRegistrationIds($data['x_description']);
		return [true, $audit, $registration_ids, $debit_ids];
	}

	public function canRefund(Payment $payment): bool {
		return Configure::read('payment.chase_refunds');
	}

	public function refund(Event $event, Payment $payment, Payment $refund): array {
		$response = $this->client()->refund($event, $payment, $refund);

		// Parse the response
		try {
			$data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $ex) {
			$data = [];
		}

		if (empty($data)) {
			Log::write(LogLevel::ERROR, "Chase error: $response");
			throw new PaymentException('The Chase server returned an unexpected response: ' . addslashes(trim($response)));
		}

		return $this->parseRefund($data);
	}

	public function parseRefund(array $data): array {
		// Retrieve the parameters sent from the server
		$audit = ['payment_plugin' => 'Chase'];
		foreach ([
			 'response_code' => 'bank_resp_code',
			 'transaction_id' => 'transaction_tag',
			 'charge_total' => 'amount',
			 'cardholder' => 'cardholder_name',
			 'expiry' => 'cc_expiry',
			 'f4l4' => 'cc_number',
			 'message' => 'bank_message',
		 ] as $field => $key) {
			if (array_key_exists($key, $data)) {
				$audit[$field] = $data[$key];
			}
		}

		// See if we can get a better card number from the receipt
		if (preg_match ('#CARD NUMBER : ([\*\#]+\d+)#im', $data['ctr'], $matches)) {
			$audit['f4l4'] = $matches[1];
		}

		// Second method of getting the charge total, if we didn't get it earlier
		if (empty($audit['charge_total']) && preg_match ('#ACCT: ([A-Za-z]+) * \$ ([0-9,]+\.[0-9]{2}) CAD#im', $data['ctr'], $matches)) {
			$audit['card'] = strtoupper($matches[1]);
			$total_arr = explode(',', $matches[2]);
			$audit['charge_total'] = 0;
			while (!empty($total_arr)) {
				$audit['charge_total'] *= 1000;
				$audit['charge_total'] += array_shift($total_arr);
			}
		}

		if (preg_match ('#DATE/TIME *: (\d+ [a-z]{3} \d+) (\d+:\d+:\d+)#im', $data['ctr'], $matches)) {
			$audit['date'] = $matches[1];
			$audit['time'] = $matches[2];
		}
		if (empty($audit['approval_code']) && preg_match ('#AUTHOR. \# *: ([0-9A-Z]{6})#im', $data['ctr'], $matches)) {
			$audit['approval_code'] = $matches[1];
		}

		if (!array_key_exists('response_code', $audit)) {
			$audit['response_code'] = '0';
		}

		return $audit;
	}
}
