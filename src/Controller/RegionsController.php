<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Regions Controller
 *
 * @property \App\Model\Table\RegionsTable $Regions
 */
class RegionsController extends AppController {

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations
				if (in_array($this->request->getParam('action'), [
					'index',
					'add',
				])) {
					// If an affiliate id is specified, check if we're a manager of that affiliate
					$affiliate = $this->request->getQuery('affiliate');
					if (!$affiliate) {
						// If there's no affiliate id, this is a top-level operation that all managers can perform
						return true;
					} else if (in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs'))) {
						return true;
					} else {
						Configure::write('Perm.is_manager', false);
					}
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'view',
					'edit',
					'delete',
				])) {
					// If a region id is specified, check if we're a manager of that region's affiliate
					$region = $this->request->getQuery('region');
					if ($region) {
						if (in_array($this->Regions->affiliate($region), $this->UserCache->read('ManagedAffiliateIDs'))) {
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
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->set('regions', $this->Regions->find()
			->where(['affiliate_id IN' => $affiliates])
			->contain(['Affiliates'])
			->order(['Affiliates.name', 'Regions.name'])
			->toArray());
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->getQuery('region');
		try {
			$region = $this->Regions->get($id, [
				'contain' => ['Affiliates', 'Facilities']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($region->affiliate_id);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('region', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$region = $this->Regions->newEntity();
		if ($this->request->is('post')) {
			$region = $this->Regions->patchEntity($region, $this->request->data);
			if ($this->Regions->save($region)) {
				$this->Flash->success(__('The region has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The region could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->request->data['affiliate_id']);
			}
		}
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('region', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->getQuery('region');
		try {
			$region = $this->Regions->get($id, [
				'contain' => []
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$region = $this->Regions->patchEntity($region, $this->request->data);
			if ($this->Regions->save($region)) {
				$this->Flash->success(__('The region has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The region could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->Configuration->loadAffiliate($region->affiliate_id);
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('region', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('region');
		$dependencies = $this->Regions->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this region, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$region = $this->Regions->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Regions->delete($region)) {
			$this->Flash->success(__('The region has been deleted.'));
		} else if ($region->errors('delete')) {
			$this->Flash->warning(current($region->errors('delete')));
		} else {
			$this->Flash->warning(__('The region could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
