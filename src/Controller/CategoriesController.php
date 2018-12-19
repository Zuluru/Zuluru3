<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\MethodNotAllowedException;

/**
 * Categories Controller
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 */
class CategoriesController extends AppController {

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$this->set('categories', $this->Categories->find()
			->contain(['Affiliates'])
			->where(['affiliate_id IN' => $affiliates])
			->order(['Affiliates.name', 'Categories.name']));
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->getQuery('category');
		try {
			$category = $this->Categories->get($id, [
				'contain' => ['Affiliates', 'Tasks' => ['People']]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid category.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid category.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($category);
		$this->Configuration->loadAffiliate($category->affiliate_id);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('category', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$category = $this->Categories->newEntity();
		$this->Authorization->authorize($this);

		if ($this->request->is('post')) {
			$category = $this->Categories->patchEntity($category, $this->request->data);
			if ($this->Categories->save($category)) {
				$this->Flash->success(__('The category has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The category could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($category->affiliate_id);
			}
		}
		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('category', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->getQuery('category');
		try {
			$category = $this->Categories->get($id);
		} catch (RecordNotFoundException  $ex) {
			$this->Flash->info(__('Invalid category.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid category.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Authorization->authorize($category);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$category = $this->Categories->patchEntity($category, $this->request->data);
			if ($this->Categories->save($category)) {
				$this->Flash->success(__('The category has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The category could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($category->affiliate);
			}
		}
		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('category', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('category');
		try {
			$category = $this->Categories->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid category.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid category.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($category);

		$dependencies = $this->Categories->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this category, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Categories->delete($category)) {
			$this->Flash->success(__('The category has been deleted.'));
		} else if ($category->errors('delete')) {
			$this->Flash->warning(current($category->errors('delete')));
		} else {
			$this->Flash->warning(__('The category could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
