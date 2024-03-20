<?php
namespace StripePayment\Controller;

use App\Controller\AppController;
use App\Controller\SettingsTrait;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

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
	public function initialize(): void {
		parent::initialize();
		$this->loadModel('Settings');
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function index() {
		$result = $this->_process(['category' => 'payment']);
		if ($result === true) {
			if ($this->validateSettings('live') && $this->validateSettings('test')) {
				return $this->redirect(['plugin' => false, 'controller' => 'Plugins', 'action' => 'index']);
			}
		} else if (is_object($result)) {
			return $result;
		}
	}

	protected function validateSettings($env) {
		$key = Configure::read("payment.stripe_{$env}_secret_key");
		if (!$key) {
			return true;
		}

		$stripe = new \Stripe\StripeClient($key);
		$settings_table = TableRegistry::getTableLocator()->get('Settings');
		$webhook_setting = $settings_table->find()
			->where(['category' => 'payment', 'name' => "stripe_{$env}_webhook_signing"])
			->first();
		if ($webhook_setting) {
			try {
				$webhooks = $stripe->webhookEndpoints->all();
			} catch (\Stripe\Exception\AuthenticationException $ex) {
				$this->Flash->error(__('Invalid {0} secret key.', $env));
				return false;
			}

			foreach ($webhooks->data as $webhook) {
				if ($webhook->url == Router::url(['controller' => 'Payment', 'action' => 'index'], true)) {
					// Assume we've got a valid webhook. Seems to be no way to get the secret except at time of creation?
					// If someone mucks with their endpoint in the dashboard, the solution will be to just delete it and
					// re-save the settings; this will fall through, and a new endpoint be created below.
					return true;
				}
			}
		}

		// No matching webhook found, create a new one
		try {
			$webhook = $stripe->webhookEndpoints->create([
				'url' => Router::url(['controller' => 'Payment', 'action' => 'index'], true),
				'description' => 'Zuluru payment notification',
				'enabled_events' => ['checkout.session.completed'],
			]);
		} catch (\Stripe\Exception\AuthenticationException $ex) {
			$this->Flash->error(__('Invalid {0} secret key.', $env));
			return false;
		}

		if (!$settings_table->save($settings_table->newEntity([
			'category' => 'payment',
			'name' => "stripe_{$env}_webhook_signing",
			'value' => $webhook->secret,
		]))) {
			$this->Flash->error(__('Failed to save the webhook signing key.', $env));
			return false;
		}

		return true;
	}
}
