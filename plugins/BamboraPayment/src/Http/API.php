<?php
namespace BamboraPayment\Http;

use Cake\Core\Configure;

class API extends \App\Http\API {

	/**
	 * @param string $qs
	 * @param bool $checkHash
	 * @return array
	 */
	public function parsePayment($qs) {
		parse_str($qs, $data);

		// Retrieve the parameters sent from the server
		$audit = [];
		foreach ([
			'iso_code' => 'trnApproved',
			'transaction_id' => 'trnId',
			'approval_code' => 'authCode',
			'response_code' => 'messageId',
			'message' => 'messageText',
			'charge_total' => 'trnAmount',
			'order_id' => 'trnOrderNumber',
			'card' => 'cardType',
			'issuer_confirmation' => 'ioConfCode',
			'issuer' => 'ioInstName',
			'issuer_invoice' => 'trnOrderNumber',
		] as $field => $key) {
			if (array_key_exists($key, $data)) {
				$audit[$field] = $data[$key];
			}
		}

		if (preg_match('#(\d+/\d+/\d+) (\d+:\d+:\d+ [AP]M)#im', $data['trnDate'], $matches)) {
			$audit['date'] = $matches[1];
			$audit['time'] = $matches[2];
		}
		$audit['transaction_name'] = $data['trnType'] . ': ' . $data['paymentMethod'];

		// Validate the hash
		$hash_pos = strpos($qs, '&hashValue=');
		if ($hash_pos === false) {
			return [false, $audit, []];
		} else {
			$qs = substr($qs, 0, $hash_pos);
		}
		if ($this->isTest()) {
			$hash_key = Configure::read('payment.bambora_test_hash_key');
		} else {
			$hash_key = Configure::read('payment.bambora_live_hash_key');
		}
		$calculated_hash = sha1($qs . $hash_key);

		// Validate the response code
		if ($audit['iso_code'] != 1 || $data['hashValue'] != $calculated_hash) {
			return [false, $audit, []];
		}

		$registration_ids = explode(',', $data['ref1']);

		return [true, $audit, $registration_ids];
	}

}
