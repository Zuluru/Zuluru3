<?php
namespace PayPalPayment\Controller;

use App\Controller\AppController;
use App\Controller\SettingsTrait;

/**
 * Settings Controller
 *
 * @property \App\Model\Table\SettingsTable $Settings
 */
class SettingsController extends AppController {

	use SettingsTrait;

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
		$this->loadModel('Settings');
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function index() {
		$result = $this->_process(['category' => 'payment']);
		if ($result === true) {
			return $this->redirect(['plugin' => false, 'controller' => 'Plugins', 'action' => 'index']);
		} else if (is_object($result)) {
			return $result;
		}

		if (!function_exists('curl_init')) {
			$this->Flash->warning(__('PayPal integration requires the cUrl library, which your installation of PHP does not support. If you need PayPal support, talk to your system administrator or hosting company about enabling cUrl.'));
		}
	}

}
