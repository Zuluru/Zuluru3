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

	public function index() {
		$this->Authorization->authorize($this);
		if (Configure::read('payment.popup')) {
			$this->viewBuilder()->setLayout('bare');
		}
		// PayPal sends data back through the URL
		[$result, $audit, $registration_ids] = API::parsePayment($this->request->getQueryParams());
		$this->_processPayment($result, $audit, $registration_ids);
	}

}
