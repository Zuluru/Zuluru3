<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Query;

/**
 * Tasks Controller
 *
 * @property \App\Model\Table\TasksTable $Tasks
 */
class TasksController extends AppController {

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
					'add',
				])) {
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'view',
					'edit',
					'delete',
				])) {
					// If a task id is specified, check if we're a manager of that task's affiliate
					$task = $this->request->getQuery('task');
					if ($task) {
						if (in_array($this->Tasks->affiliate($task), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}

			if (Configure::read('Perm.is_manager') || Configure::read('Perm.is_official') || Configure::read('Perm.is_volunteer')) {
				// Volunteers can can perform these operations
				if (in_array($this->request->getParam('action'), [
					'index',
					'view',
				])) {
					return true;
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

		if ((Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) && $this->request->is('csv')) {
			$tasks = $this->Tasks->Categories->find()
				->contain([
					'Tasks' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Tasks.name']);
						},
						'People' => [Configure::read('Security.authModel')],
						'TaskSlots' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['TaskSlots.task_date', 'TaskSlots.task_start']);
							},
							'People',
							'ApprovedBy',
						],
					],
				])
				->where(['Categories.affiliate_id IN' => $affiliates])
				->order(['Categories.name'])
				->toArray();
			$this->response->download('Tasks.csv');
		} else {
			$conditions = ['Categories.affiliate_id IN' => $affiliates];
			if (!Configure::read('Perm.is_admin') && !Configure::read('Perm.is_manager')) {
				$conditions['Tasks.allow_signup'] = true;
			}
			$tasks = $this->Tasks->find()
				->contain([
					'Categories',
					'People',
				])
				->where($conditions)
				->order(['Categories.name', 'Tasks.name'])
				->toArray();
		}

		$this->set(compact('tasks'));
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

		$id = $this->request->getQuery('task');
		try {
			$task = $this->Tasks->get($id, [
				'contain' => [
					'Categories',
					'People',
					'TaskSlots' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['TaskSlots.task_date', 'TaskSlots.task_start', 'TaskSlots.task_end', 'TaskSlots.id']);
						},
						'People',
						'ApprovedBy',
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		}

		if (!$task->allow_signup && !(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		}

		$affiliates = $this->_applicableAffiliates(true);
		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
			$people = $this->Tasks->People->find()
				->matching('Groups', function (Query $q) {
					return $q->where(['Groups.id IN' => [GROUP_VOLUNTEER, GROUP_OFFICIAL, GROUP_MANAGER, GROUP_ADMIN]]);
				})
				->matching('Affiliates', function (Query $q) use ($affiliates) {
					return $q->where(['Affiliates.id IN' => array_keys($affiliates)]);
				})
				->order(['People.first_name', 'People.last_name'])
				->combine('id', 'full_name')
				->toArray();
		}
		$this->set(compact('task', 'affiliates', 'people'));
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

		$task = $this->Tasks->newEntity();
		if ($this->request->is('post')) {
			$task = $this->Tasks->patchEntity($task, $this->request->data);
			if ($this->Tasks->save($task)) {
				$this->Flash->success(__('The task has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The task could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->Tasks->Categories->affiliate($task->category_id));
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$categories = $this->Tasks->Categories->find('list', ['order' => 'Categories.name']);
		$people = $this->Tasks->People->find()
			->matching('Groups', function (Query $q) {
				return $q->where(['Groups.id IN' => [GROUP_VOLUNTEER, GROUP_OFFICIAL, GROUP_MANAGER, GROUP_ADMIN]]);
			})
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => array_keys($affiliates)]);
			})
			->order(['People.first_name', 'People.last_name'])
			->combine('id', 'full_name')
			->toArray();
		$this->set(compact('task', 'affiliates', 'categories', 'people'));
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

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('task');
		try {
			$task = $this->Tasks->get($id, [
				'contain' => ['Categories']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($task->category->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$task = $this->Tasks->patchEntity($task, $this->request->data);
			if ($this->Tasks->save($task)) {
				$this->Flash->success(__('The task has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The task could not be saved. Please correct the errors below and try again.'));
			}
		}
		$affiliates = $this->_applicableAffiliates(true);
		$categories = $this->Tasks->Categories->find('list', ['order' => 'Categories.name']);
		$people = $this->Tasks->People->find()
			->matching('Groups', function (Query $q) {
				return $q->where(['Groups.id IN' => [GROUP_VOLUNTEER, GROUP_OFFICIAL, GROUP_MANAGER, GROUP_ADMIN]]);
			})
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => array_keys($affiliates)]);
			})
			->order(['People.first_name', 'People.last_name'])
			->combine('id', 'full_name')
			->toArray();
		$this->set(compact('task', 'affiliates', 'categories', 'people'));
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

		$id = $this->request->getQuery('task');
		$dependencies = $this->Tasks->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this task, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$task = $this->Tasks->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Tasks->delete($task)) {
			$this->Flash->success(__('The task has been deleted.'));
		} else if ($task->errors('delete')) {
			$this->Flash->warning(current($task->errors('delete')));
		} else {
			$this->Flash->warning(__('The task could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
