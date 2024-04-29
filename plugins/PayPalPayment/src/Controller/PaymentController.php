<?php
namespace PayPalPayment\Controller;

use App\Controller\PaymentsTrait;
use Cake\Core\Configure;
use PayPalPayment\Http\API;

/**
 * Controller for handling payments from the PayPal Express checkout system.
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class PaymentController extends AppController {

	use PaymentsTrait;

	/**
	 * @var \PayPalPayment\Http\API
	 */
	public $api = null;

	/**
	 * @param $test
	 * @return API
	 */
	public function getAPI($test) {
		if (!$this->api) {
			$this->api = new API($test);
		}

		return $this->api;
	}

	/**
	 * Initialization hook method.
	 *
	 * Use this method to add common initialization code like loading components.
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function initialize(): void {
		parent::initialize();
		$this->Registrations = $this->fetchTable('Registrations');
	}

	public function index() {
		$this->Authorization->authorize($this);
		if (Configure::read('payment.popup')) {
			$this->viewBuilder()->setLayout('bare');
		}

		// PayPal sends data back through the URL
		$data = $this->getRequest()->getQueryParams();
		[$result, $audit, $registration_ids, $debit_ids] = $this->getAPI(API::isTestData($data))->parsePayment($data);
		$this->_processPayment($result, $audit, $registration_ids, $debit_ids);
	}

}
