<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\ORM\Entity;
use Cake\ORM\Query;

/**
 * Plugins Controller
 *
 * @property \App\Model\Table\PluginsTable $Plugins
 */
class PluginsController extends AppController {

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$this->set('plugins', $this->Plugins->find()->where(['advertise' => true])->order('Plugins.name'));
	}

	/**
	 * Activate method
	 *
	 * This function does *not* operate through Ajax, because if we do that, it tries to generate a link to the
	 * plugin's settings page, but there's no route for that registered and it fails. Instead, we have to do a
	 * normal page load, and redirect to the index afterwards. If a clean method can be found for loading plugins
	 * from within a controller action, then Ajax can come back to this.
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function activate() {
		$id = $this->getRequest()->getQuery('plugin_id');
		try {
			$plugin = $this->Plugins->get($id, [
				'contain' => []
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid plugin.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid plugin.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($plugin, 'activate');

		$plugin->enabled = true;
		if (!$this->Plugins->save($plugin)) {
			$this->Flash->warning(__('Failed to activate plugin "{0}".', addslashes($plugin->name)));
		}

		return $this->redirect(['controller' => 'Plugins', 'action' => 'index']);
	}

	/**
	 * Deactivate method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function deactivate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('plugin_id');
		try {
			$plugin = $this->Plugins->get($id, [
				'contain' => []
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid plugin.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid plugin.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($plugin, 'deactivate');

		$plugin->enabled = false;
		if (!$this->Plugins->save($plugin)) {
			$this->Flash->warning(__('Failed to deactivate plugin "{0}".', addslashes($plugin->name)));
			return $this->redirect(['controller' => 'Plugins', 'action' => 'index']);
		}

		$this->set(compact('plugin'));
	}

}
