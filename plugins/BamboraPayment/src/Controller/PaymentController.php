<?php
namespace BamboraPayment\Controller;

use App\Controller\PaymentsTrait;
use Cake\Core\Configure;
use BamboraPayment\Http\API;

/**
 * Controller for handling payments from the Bambora hosted checkout system.
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class PaymentController extends AppController {

	use PaymentsTrait;

	/**
	 * @var \BamboraPayment\Http\API
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
	public function initialize() {
		parent::initialize();
		$this->loadModel('Registrations');
	}

	public function index() {
		$this->Authorization->authorize($this);
		if (Configure::read('payment.popup')) {
			$this->viewBuilder()->setLayout('bare');
		}

		// Bambora sends data back through the URL
		$data = $this->request->getUri()->getQuery();
		[$result, $audit, $registration_ids] = $this->getAPI(API::isTestData($data))->parsePayment($data);
		$this->_processPayment($result, $audit, $registration_ids);
	}

}
