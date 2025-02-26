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

	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);
		if (isset($this->FormProtection)) {
			// TODO: All the fields for sorting in the index page are hidden and hence by default locked
			$this->FormProtection->setConfig('unlockedActions', ['index']);
		}
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$categories = $this->Categories->find()
			->contain(['Affiliates'])
			->where(['affiliate_id IN' => $affiliates])
			->order(['Affiliates.name', 'Categories.type', 'Categories.sort', 'Categories.name'])
			->toArray();

		if ($this->getRequest()->is('post')) {
			$categories = $this->Categories->patchEntities($categories, $this->getRequest()->getData());
			if ($this->Categories->saveMany($categories)) {
				$this->Flash->success(__('Sort order has been updated.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('Sort order could not be updated.'));
			}
		}

		$this->set(compact('affiliates', 'categories'));
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('category');
		try {
			$category = $this->Categories->get($id, [
				'contain' => ['Affiliates', 'Leagues', 'Tasks' => ['People']]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
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
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$category = $this->Categories->newEmptyEntity();
		$this->Authorization->authorize($this);

		if ($this->getRequest()->is('post')) {
			$category = $this->Categories->patchEntity($category, $this->getRequest()->getData());
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
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('category');
		try {
			$category = $this->Categories->find('translations')
				->where(['Categories.id' => $id])
				->firstOrFail();
		} catch (RecordNotFoundException|InvalidPrimaryKeyException  $ex) {
			$this->Flash->info(__('Invalid category.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Authorization->authorize($category);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$category = $this->Categories->patchEntity($category, $this->getRequest()->getData());
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
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('category');
		try {
			$category = $this->Categories->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
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
		} else if ($category->getError('delete')) {
			$this->Flash->warning(current($category->getError('delete')));
		} else {
			$this->Flash->warning(__('The category could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
