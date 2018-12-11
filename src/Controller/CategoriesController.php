<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;

/**
 * Categories Controller
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 */
class CategoriesController extends AppController {

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if tasks are not enabled
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (!Configure::read('feature.tasks')) {
				throw new MethodNotAllowedException('Tasks are not enabled on this system.');
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
					// If a category id is specified, check if we're a manager of that category's affiliate
					$category = $this->request->getQuery('category');
					if ($category) {
						if (in_array($this->Categories->affiliate($category), $this->UserCache->read('ManagedAffiliateIDs'))) {
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
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if tasks are not enabled
	 */
	public function index() {
		if (!Configure::read('feature.tasks')) {
			throw new MethodNotAllowedException('Tasks are not enabled on this system.');
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
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
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if tasks are not enabled
	 */
	public function view() {
		if (!Configure::read('feature.tasks')) {
			throw new MethodNotAllowedException('Tasks are not enabled on this system.');
		}

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
		$this->Configuration->loadAffiliate($category->affiliate_id);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('category', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if tasks are not enabled
	 */
	public function add() {
		if (!Configure::read('feature.tasks')) {
			throw new MethodNotAllowedException('Tasks are not enabled on this system.');
		}

		$category = $this->Categories->newEntity();
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
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('category', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if tasks are not enabled
	 */
	public function edit() {
		if (!Configure::read('feature.tasks')) {
			throw new MethodNotAllowedException('Tasks are not enabled on this system.');
		}

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
		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('category', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if tasks are not enabled
	 */
	public function delete() {
		if (!Configure::read('feature.tasks')) {
			throw new MethodNotAllowedException('Tasks are not enabled on this system.');
		}

		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('category');
		$dependencies = $this->Categories->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this category, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$category = $this->Categories->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid category.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid category.'));
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
