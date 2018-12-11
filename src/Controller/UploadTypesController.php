<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;

/**
 * UploadTypes Controller
 *
 * @property \App\Model\Table\UploadTypesTable $UploadTypes
 */
class UploadTypesController extends AppController {

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if document management is not enabled
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (!Configure::read('feature.documents')) {
				throw new MethodNotAllowedException('Document management is disabled on this site.');
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations
				if (in_array($this->request->getParam('action'), [
					'index',
					'add',
				]))
				{
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'view',
					'edit',
					'delete',
				]))
				{
					// If an upload type id is specified, check if we're a manager of that upload type's affiliate
					$type = $this->request->getQuery('type');
					if ($type) {
						if (in_array($this->UploadTypes->affiliate($type), $this->UserCache->read('ManagedAffiliateIDs'))) {
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
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if document management is not enabled
	 */
	public function index() {
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is disabled on this site.');
		}

		$this->set('uploadTypes', $this->UploadTypes->find()
			->contain(['Affiliates'])
			->where(['UploadTypes.affiliate_id IN' => $this->_applicableAffiliateIDs(true)])
			->order('UploadTypes.name')
			->toArray());
		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if document management is not enabled
	 */
	public function view() {
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is disabled on this site.');
		}

		$id = $this->request->getQuery('type');
		try {
			$upload_type = $this->UploadTypes->get($id, [
				'contain' => [
					'Uploads' => ['People'],
					'Affiliates',
				]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid upload type.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid upload type.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($upload_type->affiliate_id);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('upload_type', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if document management is not enabled
	 */
	public function add() {
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is disabled on this site.');
		}

		$upload_type = $this->UploadTypes->newEntity();
		if ($this->request->is('post')) {
			$upload_type = $this->UploadTypes->patchEntity($upload_type, $this->request->data);
			if ($this->UploadTypes->save($upload_type)) {
				$this->Flash->success(__('The upload type has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The upload type could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->request->data['affiliate_id']);
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('upload_type', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if document management is not enabled
	 */
	public function edit() {
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is disabled on this site.');
		}

		$id = $this->request->getQuery('type');
		try {
			$upload_type = $this->UploadTypes->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid upload type.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid upload type.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$upload_type = $this->UploadTypes->patchEntity($upload_type, $this->request->data);
			if ($this->UploadTypes->save($upload_type)) {
				$this->Flash->success(__('The upload type has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The upload type could not be saved. Please correct the errors below and try again.'));
			}
		}

		$this->Configuration->loadAffiliate($upload_type->affiliate_id);
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('upload_type', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if document management is not enabled
	 */
	public function delete() {
		if (!Configure::read('feature.documents')) {
			throw new MethodNotAllowedException('Document management is disabled on this site.');
		}

		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('type');
		$dependencies = $this->UploadTypes->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this upload type, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$upload_type = $this->UploadTypes->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid upload type.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid upload type.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->UploadTypes->delete($upload_type)) {
			$this->Flash->success(__('The upload type has been deleted.'));
		} else if ($upload_type->errors('delete')) {
			$this->Flash->warning(current($upload_type->errors('delete')));
		} else {
			$this->Flash->warning(__('The upload type could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
