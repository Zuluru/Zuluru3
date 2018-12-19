<?php
namespace App\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Holidays Controller
 *
 * @property \App\Model\Table\HolidaysTable $Holidays
 */
class HolidaysController extends AppController {

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->paginate['contain'] = ['Affiliates'];
		$this->paginate['conditions'] = ['Holidays.affiliate_id IN' => $affiliates];
		$this->paginate['order'] = ['date'];
		$holidays = $this->paginate($this->Holidays);

		$this->set(compact('holidays', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$holiday = $this->Holidays->newEntity();
		$this->Authorization->authorize($holiday);

		if ($this->request->is('post')) {
			$holiday = $this->Holidays->patchEntity($holiday, $this->request->data);
			if ($this->Holidays->save($holiday)) {
				$this->Flash->success(__('The holiday has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The holiday could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($holiday->affiliate_id);
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('holiday', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->getQuery('holiday');
		try {
			$holiday = $this->Holidays->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid holiday.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid holiday.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($holiday);
		$this->Configuration->loadAffiliate($holiday->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$holiday = $this->Holidays->patchEntity($holiday, $this->request->data);
			if ($this->Holidays->save($holiday)) {
				$this->Flash->success(__('The holiday has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The holiday could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('holiday', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('holiday');
		try {
			$holiday = $this->Holidays->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid holiday.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid holiday.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($holiday);

		if ($this->Holidays->delete($holiday)) {
			$this->Flash->success(__('The holiday has been deleted.'));
		} else if ($holiday->errors('delete')) {
			$this->Flash->warning(current($holiday->errors('delete')));
		} else {
			$this->Flash->warning(__('The holiday could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
