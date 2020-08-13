<?php
namespace PayPal\Controller;

use App\Controller\PaymentsTrait;
use Cake\Core\Configure;
use PayPal\Http\API;

/**
 * Controller for handling payments from the PayPal Express checkout system.
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class PaymentController extends AppController {

	use PaymentsTrait;

	var $_api;

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
		$this->_api = new API();
	}

	public function index() {
		$this->Authorization->authorize($this);
		if (Configure::read('payment.popup')) {
			$this->viewBuilder()->setLayout('bare');
		}
		// PayPal sends data back through the URL
		[$result, $audit, $registration_ids] = $this->_parsePost($this->request->getQueryParams());
		$this->_processPayment($result, $audit, $registration_ids);
	}

	private function _parsePost(array $data) {
		$details = $this->_api->GetExpressCheckoutDetails(['TOKEN' => $data['token']]);
		if (!is_array($details)) {
			return [false, ['message' => $details], []];
		}

		$response = $this->_api->DoExpressCheckoutPayment([
			'PAYMENTACTION' => 'Sale',
			'PAYERID' => $details['PAYERID'],
			'TOKEN' => $details['TOKEN'],
			'PAYMENTREQUEST_0_AMT' => $details['PAYMENTREQUEST_0_AMT'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => $details['PAYMENTREQUEST_0_CURRENCYCODE'],
		]);
		if (!is_array($response)) {
			return [false, ['message' => $response], []];
		}

		// Retrieve the parameters sent from the server
		$audit = [
			'order_id' => $details['PAYMENTREQUEST_0_INVNUM'],
			'charge_total' => $details['PAYMENTREQUEST_0_AMT'],
			'cardholder' => "{$details['FIRSTNAME']} {$details['LASTNAME']}",
			'response_code' => $response['PAYMENTINFO_0_ERRORCODE'],
			'transaction_id' => $response['PAYMENTINFO_0_TRANSACTIONID'],
			'transaction_name' => "{$response['PAYMENTINFO_0_TRANSACTIONTYPE']}:{$response['PAYMENTINFO_0_PAYMENTTYPE']}",
		];

		if (array_key_exists('NOTE', $response)) {
			$audit['message'] = $response['NOTE'];
		}

		preg_match ('#(\d{4}-\d{2}-\d{2})T(\d+:\d+:\d+)Z#', $response['TIMESTAMP'], $matches);
		$audit['date'] = $matches[1];
		$audit['time'] = $matches[2];

		// Validate the response code
		if ($response['PAYMENTINFO_0_ERRORCODE'] == 0)
		{
			[$user_id, $registration_ids] = explode(':', $details['PAYMENTREQUEST_0_CUSTOM']);
			$registration_ids = explode(',', $registration_ids);
			return [true, $audit, $registration_ids];
		} else {
			return [false, $audit, []];
		}
	}

}
