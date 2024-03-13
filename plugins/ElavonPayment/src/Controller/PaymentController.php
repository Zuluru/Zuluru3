<?php
namespace ElavonPayment\Controller;

use App\Controller\PaymentsTrait;
use Cake\Core\Configure;
use ElavonPayment\Http\API;

/**
 * Controller for handling payments from the Elavon hosted checkout system.
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class PaymentController extends AppController {

	use PaymentsTrait;

	/**
	 * @var \ElavonPayment\Http\API
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

		// Elavon posts data back to us as if we're a form
		$data = $this->getRequest()->getData();

		[$result, $audit, $registration_ids, $debit_ids] = $this->getAPI(API::isTestData($data))->parsePayment($data);
		$this->_processPayment($result, $audit, $registration_ids, $debit_ids);
	}
}
