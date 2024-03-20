<?php
namespace App\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Regions Controller
 *
 * @property \App\Model\Table\RegionsTable $Regions
 */
class RegionsController extends AppController {

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
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
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('region');
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

		$this->Authorization->authorize($region);
		$this->Configuration->loadAffiliate($region->affiliate_id);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('region', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$region = $this->Regions->newEmptyEntity();
		$this->Authorization->authorize($region);
		if ($this->getRequest()->is('post')) {
			$region = $this->Regions->patchEntity($region, $this->getRequest()->getData());
			if ($this->Regions->save($region)) {
				$this->Flash->success(__('The region has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The region could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->getRequest()->getData('affiliate_id'));
			}
		}
		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('region', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('region');
		try {
			$region = $this->Regions->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($region);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$region = $this->Regions->patchEntity($region, $this->getRequest()->getData());
			if ($this->Regions->save($region)) {
				$this->Flash->success(__('The region has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The region could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->Configuration->loadAffiliate($region->affiliate_id);
		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('region', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('region');
		try {
			$region = $this->Regions->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid region.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($region);

		$dependencies = $this->Regions->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this region, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Regions->delete($region)) {
			$this->Flash->success(__('The region has been deleted.'));
		} else if ($region->getError('delete')) {
			$this->Flash->warning(current($region->getError('delete')));
		} else {
			$this->Flash->warning(__('The region could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
