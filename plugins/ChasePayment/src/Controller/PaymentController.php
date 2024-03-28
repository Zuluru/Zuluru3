<?php
namespace ChasePayment\Controller;

use App\Controller\PaymentsTrait;
use App\Model\Entity\Registration;
use Cake\Core\Configure;
use ChasePayment\Http\API;

/**
 * Controller for handling payments from the Chase hosted checkout system.
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class PaymentController extends AppController {

	use PaymentsTrait;

	/**
	 * @var \ChasePayment\Http\API
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
	protected function _noAuthenticationActions(): array {
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
	public function initialize(): void {
		parent::initialize();
		$this->loadModel('Registrations');
	}

	public function beforeFilter(\Cake\Event\EventInterface $event) {
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
		$data = $this->getRequest()->getData();
		[$result, $audit, $registration_ids, $debit_ids] = $this->getAPI(API::isTestData($data))->parsePayment($data);
		$this->_processPayment($result, $audit, $registration_ids, $debit_ids);
	}

	public function from_email() {
		$this->Authorization->authorize($this);
		if (!empty($this->getRequest()->getData())) {
			$values = $this->_parseEmail($this->getRequest()->getData('email_text'));
			if (!$values) {
				return;
			}

			[$result, $audit, $registration_ids, $debit_ids] = $this->getAPI(API::isTestData($values))->parsePayment($values, false);
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
				if ($registration->payment === 'Paid') {
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
		$data = $this->getRequest()->getData();
		[$result, $audit, $registration_ids, $debit_ids] = $this->getAPI(API::isTestData($data))->parsePayment($data, false);
		$this->_processPayment($result, $audit, $registration_ids, $debit_ids);
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
