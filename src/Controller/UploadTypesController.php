<?php
namespace App\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * UploadTypes Controller
 *
 * @property \App\Model\Table\UploadTypesTable $UploadTypes
 */
class UploadTypesController extends AppController {

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$this->set('uploadTypes', $this->UploadTypes->find()
			->contain(['Affiliates'])
			->where(['UploadTypes.affiliate_id IN' => $this->Authentication->applicableAffiliateIDs(true)])
			->order('UploadTypes.name')
			->toArray());
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('type');
		try {
			$upload_type = $this->UploadTypes->get($id, [
				'contain' => [
					'Uploads' => ['People'],
					'Affiliates',
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid upload type.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($upload_type);
		$this->Configuration->loadAffiliate($upload_type->affiliate_id);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('upload_type', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$upload_type = $this->UploadTypes->newEmptyEntity();
		$this->Authorization->authorize($upload_type);
		if ($this->getRequest()->is('post')) {
			$upload_type = $this->UploadTypes->patchEntity($upload_type, $this->getRequest()->getData());
			if ($this->UploadTypes->save($upload_type)) {
				$this->Flash->success(__('The upload type has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The upload type could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->getRequest()->getData('affiliate_id'));
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('upload_type', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('type');
		try {
			$upload_type = $this->UploadTypes->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid upload type.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($upload_type);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$upload_type = $this->UploadTypes->patchEntity($upload_type, $this->getRequest()->getData());
			if ($this->UploadTypes->save($upload_type)) {
				$this->Flash->success(__('The upload type has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The upload type could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->Configuration->loadAffiliate($upload_type->affiliate_id);
		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('upload_type', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('type');
		try {
			$upload_type = $this->UploadTypes->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid upload type.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($upload_type);

		$dependencies = $this->UploadTypes->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this upload type, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->UploadTypes->delete($upload_type)) {
			$this->Flash->success(__('The upload type has been deleted.'));
		} else if ($upload_type->getError('delete')) {
			$this->Flash->warning(current($upload_type->getError('delete')));
		} else {
			$this->Flash->warning(__('The upload type could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
