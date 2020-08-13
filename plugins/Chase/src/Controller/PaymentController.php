<?php
namespace Chase\Controller;

use App\Controller\PaymentsTrait;
use App\Model\Entity\Registration;
use Cake\Core\Configure;

/**
 * Controller for handling payments from the Chase hosted checkout system.
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class PaymentController extends AppController {

	use PaymentsTrait;

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions() {
		return ['index'];
	}

	/**
	 * Initialization hook method.
	 *
	 * Use this method to add common initialization code like loading components.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function initialize() {
		parent::initialize();
		$this->loadModel('Registrations');
	}

	// TODO: Proper fix for black-holing of payment details posted to us from processors
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		if (isset($this->Security)) {
			$this->Security->setConfig('unlockedActions', ['index']);
		}
	}

	public function index() {
		if (Configure::read('payment.popup')) {
			$this->viewBuilder()->setLayout('bare');
		}
		// Chase posts data back to us as if we're a form
		[$result, $audit, $registration_ids] = $this->_parsePost($this->request->getData());
		$this->_processPayment($result, $audit, $registration_ids);
	}

	public function from_email() {
		$this->Authorization->authorize($this);
		if (!empty($this->request->getData())) {
			$values = $this->_parseEmail($this->request->getData('email_text'));
			if (!$values) {
				return;
			}

			[$result, $audit, $registration_ids] = $this->_parsePost($values, false);
			if (!$result) {
				$this->Flash->warning(__('Unable to extract payment information from the text provided.'));
				return;
			}

			// Check that the registrations aren't already marked as paid
			$registrations = $this->Registrations->find()
				->contain(['Payments' => ['RegistrationAudits']])
				->where(['Registrations.id IN' => $registration_ids]);
			if ($registrations->count() != count($registration_ids)) {
				$this->Flash->warning(__('A registration in this email could not be loaded.'));
				return;
			}
			if ($registrations->some(function (Registration $registration) {
				if ($registration->payment == 'Paid') {
					return !empty($registration->payments);
				}
			})) {
				$this->Flash->warning(__('A registration in this email has already been marked as paid. All registrations must be unpaid before this can proceed.'));
				return;
			}

			$this->set(['fields' => $values]);
		}
	}

	public function from_email_confirmation() {
		$this->Authorization->authorize($this);
		$this->viewBuilder()->setTemplate('index');
		[$result, $audit, $registration_ids] = $this->_parsePost($this->request->getData(), false);
		$this->_processPayment($result, $audit, $registration_ids);
	}

	private function _parsePost(Array $data, $checkHash = true) {
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
			if (Configure::read('payment.test_payments')) {
				$login = Configure::read('payment.chase_test_store');
				$key = Configure::read('payment.chase_test_response');
			} else {
				$login = Configure::read('payment.chase_live_store');
				$key = Configure::read('payment.chase_live_response');
			}
			$calculated_hash = md5("$key$login{$audit['transaction_id']}{$audit['charge_total']}");

			// Validate the response code
			if ($audit['iso_code'] != 1 || $data['x_MD5_Hash'] != $calculated_hash) {
				return [false, $audit, []];
			}
		} else if (!array_key_exists('response_code', $audit)) {
			$audit['response_code'] = '0';
		}

		$registration_ids = explode(',', $data['x_description']);
		return [true, $audit, $registration_ids];
	}

	private function _parseEmail($text) {
		$values = [];

		// Look for the receipt text
		$matched = preg_match('/=+ TRANSACTION RECORD =+(.*?)={3,}(.*)/ms', $text, $matches);
		if ($matched) {
			$values['exact_ctr'] = $matches[1];
			$text = $matches[2];
		} else {
			$values['exact_ctr'] = '';
		}

		$matched = preg_match_all('/([\\w\\d_]+) *: (.*)/', $text, $matches, PREG_SET_ORDER);
		if (!$matched) {
			$this->Flash('warning', __('Unable to extract payment information from the text provided.'));
			return false;
		}

		if ($matched < 5) {
			// This one is for badly formatted emails
			// Matches name space colon value space, repeat. value may start with a space
			// https://stackoverflow.com/questions/50110007/regex-to-match-names-and-optional-values/50111862#50111862
			$matched = preg_match_all('/(\\w+) ?: ?(.*?)(?= ?\\w+ ?:|$)/', $text, $matches, PREG_SET_ORDER);
			if (!$matched) {
				$this->Flash('warning', __('Unable to extract payment information from the text provided.'));
				return false;
			}
		}

		foreach ($matches as $match) {
			$values[$match[1]] = trim($match[2]);
		}

		return $values;
	}

}
