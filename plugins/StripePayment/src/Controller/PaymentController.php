<?php
namespace StripePayment\Controller;

use App\Controller\PaymentsTrait;
use App\Controller\RegistrationsController;
use Cake\Core\Configure;
use StripePayment\Http\API;

/**
 *  Controller for handling payments from the Stripe Checkout system.
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
		// Stripe sends data back through an event
		[$result, $audit, $registration_ids] = API::parsePayment($this->request->input());
		$this->_processPayment($result, $audit, $registration_ids);

		if (!$result) {
			return $this->response->withStatus(400);
		}
	}

	public function success() {
		$this->Authorization->authorize($this);
		$this->Flash->success(__('Your payment has been recorded. It may take a short time to update in this system. If your registration remains unpaid for more than a few minutes, please contact an administrator. Do not re-pay unless you can confirm that no payment has been applied to your card or bank account.'));
		return $this->redirect(['plugin' => false, 'controller' => 'People', 'action' => 'splash']);
	}

}
