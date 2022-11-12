<?php
namespace ChasePayment\Http;

use Cake\Core\Configure;

class API extends \App\Http\API {

	/**
	 * @param array $data
	 * @param bool $checkHash
	 * @return array
	 */
	public function parsePayment(Array $data, $checkHash = true) {
		// Retrieve the parameters sent from the server
		$audit = [];
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
		if (preg_match ('#CARD NUMBER : ([\*\#]+\d+)#im', $data['exact_ctr'], $matches))
		{
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

}
