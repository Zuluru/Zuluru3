<?php
namespace Javelin\Controller;

use App\Controller\AppController;
use App\Controller\SettingsTrait;
use Cake\Core\Configure;

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
		$result = $this->_process(['category' => 'javelin']);
		if ($result === true) {
			return $this->redirect(['plugin' => false, 'controller' => 'Plugins', 'action' => 'index']);
		} else if (is_object($result)) {
			return $result;
		}

		if (!Configure::check('javelin.api_key')) {
			$this->Flash->html(__('You have enabled the {0} plugin but not yet registered your site. To complete this process, you must {1}.'), [
				'params' => [
					'replacements' => [
						'Javelin',
						[
							'type' => 'link',
							'link' => __('register your site with them'),
							'target' => ['plugin' => 'Javelin', 'controller' => 'Register', 'action' => 'index'],
						],
					],
				],
			]);
		}
	}

}
