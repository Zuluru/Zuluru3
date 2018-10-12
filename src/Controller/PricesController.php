<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;

/**
 * Prices Controller
 *
 * @property \App\Model\Table\PricesTable $Prices
 */
class PricesController extends AppController {

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (!Configure::read('feature.registration')) {
				throw new MethodNotAllowedException('Registration is not enabled on this system.');
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'delete',
				])) {
					// If a price id is specified, check if we're a manager of that price's affiliate
					$price = $this->request->getQuery('price');
					if ($price) {
						if (in_array($this->Prices->affiliate($price), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function delete() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('price');
		$dependencies = $this->Prices->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this price point, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['controller' => 'Events', 'action' => 'view', 'event' => $this->Prices->event($id)]);
		}

		try {
			$price = $this->Prices->get($id, [
				'contain' => ['Events' => ['Prices']],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid price.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid price.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Prices->delete($price)) {
			$this->Flash->success(__('The price point has been deleted.'));
		} else if ($price->errors('delete')) {
			$this->Flash->warning(current($price->errors('delete')));
		} else {
			$this->Flash->warning(__('The price point could not be deleted. Please, try again.'));
		}

		return $this->redirect(['controller' => 'Events', 'action' => 'view', 'event' => $price->event_id]);
	}

}
