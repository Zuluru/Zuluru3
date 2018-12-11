<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Query;
use App\View\Helper\ZuluruTimeHelper;

/**
 * TaskSlots Controller
 *
 * @property \App\Model\Table\TaskSlotsTable $TaskSlots
 */
class TaskSlotsController extends AppController {

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if taskSlots are not enabled
	 */
	protected function _publicActions() {
		if (!Configure::read('feature.tasks')) {
			throw new MethodNotAllowedException('Tasks are not enabled on this system.');
		}

		return ['ical'];
	}

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
					'assign',
					'approve',
					'delete',
				])) {
					// If a task slot id is specified, check if we're a manager of that task slot's affiliate
					$task_slot = $this->request->getQuery('slot');
					if ($task_slot) {
						if (in_array($this->TaskSlots->affiliate($task_slot), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}

			if (Configure::read('Perm.is_volunteer')) {
				// Volunteers can can perform these operations for themselves or relatives
				if (in_array($this->request->getParam('action'), [
					'assign',
				])) {
					// If a person id is specified, check the id
					$person = $this->request->getQuery('person');
					$relatives = $this->UserCache->read('RelativeIDs');
					if ($person && ($person == $this->UserCache->currentId() || in_array($person, $relatives))) {
						return true;
					}
				}
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
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

		$id = $this->request->getQuery('slot');
		try {
			$task_slot = $this->TaskSlots->get($id, [
				'contain' => [
					'Tasks' => ['Categories'],
					'People',
					'ApprovedBy',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}
		$this->Configuration->loadAffiliate($task_slot->task->category->affiliate_id);

		$this->set(compact('task_slot'));
	}

	// This function takes the parameters the old-fashioned way, to try to be more third-party friendly
	public function ical($id) {
		if (!Configure::read('feature.tasks')) {
			throw new MethodNotAllowedException('Tasks are not enabled on this system.');
		}

		$this->viewBuilder()->layout('ical');
		try {
			$task_slot = $this->TaskSlots->get($id, [
				'contain' => [
					'Tasks' => ['Categories', 'People'],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			return;
		} catch (InvalidPrimaryKeyException $ex) {
			return;
		}
		if (!$task_slot->approved) {
			return;
		}

		$this->Configuration->loadAffiliate($task_slot->task->category->affiliate_id);

		$this->set('calendar_type', 'Task');
		$this->set('calendar_name', 'Task');
		$this->response->download("$id.ics");
		$this->set(compact('task_slot'));
		$this->RequestHandler->ext = 'ics';
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

		$id = $this->request->getQuery('task');
		try {
			$task = $this->TaskSlots->Tasks->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}
		$this->Configuration->loadAffiliate($task->affiliate_id);

		$task_slot = $this->TaskSlots->newEntity();
		if ($this->request->is('post')) {
			$task_slot = $this->TaskSlots->patchEntity($task_slot, array_merge($this->request->data, ['task_id' => $id]));
			$date = $task_slot->task_date;
			if (!$task_slot->errors()) {
				for ($days = 0; $days < $this->request->data['days_to_repeat']; ++ $days) {
					for ($slots = 0; $slots < $this->request->data['number_of_slots']; ++ $slots) {
						$slot = $this->TaskSlots->newEntity(array_merge($this->request->data, ['task_id' => $id, 'task_date' => $date]));
						$this->TaskSlots->save($slot);
					}
					$date = $date->addDay();
				}
				$this->Flash->success(__('The task slot(s) have been saved. You may create more similar task slots below.'));
			} else {
				$this->Flash->warning(__('The task slot(s) could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('affiliates', 'task', 'task_slot'));
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

		$id = $this->request->getQuery('slot');
		try {
			$task_slot = $this->TaskSlots->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$task_slot = $this->TaskSlots->patchEntity($task_slot, $this->request->data);
			if ($this->TaskSlots->save($task_slot)) {
				$this->Flash->success(__('The task slot has been saved.'));
				return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
			} else {
				$this->Flash->warning(__('The task slot could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->TaskSlot->affiliate($id));
			}
		}
		$this->set(compact('task_slot'));
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

		$id = $this->request->getQuery('slot');
		$dependencies = $this->TaskSlots->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this task slot, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		try {
			$task_slot = $this->TaskSlots->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		if ($this->TaskSlots->delete($task_slot)) {
			$this->Flash->success(__('The task slot has been deleted.'));
		} else if ($task_slot->errors('delete')) {
			$this->Flash->warning(current($task_slot->errors('delete')));
		} else {
			$this->Flash->warning(__('The task slot could not be deleted. Please, try again.'));
		}

		return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
	}

	public function assign() {
		if (!Configure::read('feature.tasks')) {
			throw new MethodNotAllowedException('Tasks are not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('slot');
		try {
			$task_slot = $this->TaskSlots->get($id, [
				'contain' => ['Tasks' => ['Categories']]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		if (!$task_slot->task->allow_signup && !(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
		}

		if ($task_slot->person_id && !(Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) {
			$this->Flash->info(__('This task slot has already been assigned.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
		}

		$person_id = $this->request->data['person'];
		if (!empty($person_id)) {
			try {
				$person = $this->TaskSlots->People->get($person_id);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
			}

			try {
				$conflict = $this->TaskSlots->find()
					->contain(['Tasks' => ['Categories']])
					->where([
						'TaskSlots.id !=' => $id,
						'TaskSlots.person_id' => $person_id,
						'TaskSlots.task_date' => $task_slot->task_date,
						'OR' => [
							[
								'TaskSlots.task_start >=' => $task_slot->task_start,
								'TaskSlots.task_start <' => $task_slot->task_end,
							],
							[
								'TaskSlots.task_start <' => $task_slot->task_start,
								'TaskSlots.task_end >' => $task_slot->task_start,
							],
						],
					])
					->firstOrFail();

				$this->Flash->warning(__('This person has a conflicting assignment: {0} ({1}) from {2} to {3} on {4}',
					$conflict->task->name, $conflict->task->category->name,
					ZuluruTimeHelper::time($conflict->task_start),
					ZuluruTimeHelper::time($conflict->task_end),
					ZuluruTimeHelper::date($conflict->task_date)));
				return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
			} catch (RecordNotFoundException $ex) {
				// This is actually the situation that we want: no conflict!
			}
		}

		// If the slot was previously assigned, clear that person's task cache
		if ($task_slot->person_id) {
			$this->UserCache->clear('Tasks', $task_slot->person_id);
		}

		if (!empty($person_id) && ($task_slot->task->auto_approve || Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager'))) {
			$task_slot = $this->TaskSlots->patchEntity($task_slot, [
				'person_id' => $person_id,
				'approved' => true,
				'approved_by_id' => $this->UserCache->currentId(),
			]);
		} else {
			$task_slot = $this->TaskSlots->patchEntity($task_slot, [
				'person_id' => $person_id,
				'approved' => false,
				'approved_by_id' => null,
			]);
		}

		if (!$this->TaskSlots->save($task_slot)) {
			$this->Flash->info(__('Error assigning the task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
		}

		if ($task_slot->approved && $person_id) {
			$this->UserCache->clear('Tasks', $person_id);
		}
		if (!$this->request->is('ajax')) {
			$this->Flash->success(__('The assignment has been saved.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
		}

		// Read some data required to correctly build the output
		$affiliates = $this->_applicableAffiliates(true);
		$people = $this->TaskSlots->Tasks->People->find()
			->matching('Groups', function (Query $q) {
				return $q->where(['Groups.id IN' => [GROUP_VOLUNTEER, GROUP_OFFICIAL, GROUP_MANAGER, GROUP_ADMIN]]);
			})
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => array_keys($affiliates)]);
			})
			->order(['People.first_name', 'People.last_name'])
			->combine('id', 'full_name')
			->toArray();

		if ($person_id) {
			$task_slot->person = $this->UserCache->read('Person', $person_id);
		}
		if ($task_slot->approved) {
			$task_slot->approved_by = $this->UserCache->read('Person', $task_slot->approved_by_id);
		}

		$this->set(compact('task_slot', 'people'));
	}

	public function approve() {
		if (!Configure::read('feature.tasks')) {
			throw new MethodNotAllowedException('Tasks are not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('slot');
		try {
			$task_slot = $this->TaskSlots->get($id, [
				'contain' => ['Tasks' => ['Categories']]
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		if (!$task_slot->person_id) {
			$this->Flash->info(__('This task slot has not been assigned.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
		}

		if ($task_slot->approved) {
			$this->Flash->info(__('This task slot has already been approved.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
		}

		$task_slot = $this->TaskSlots->patchEntity($task_slot, [
			'approved' => true,
			'approved_by_id' => $this->UserCache->currentId(),
		]);

		if (!$this->TaskSlots->save($task_slot)) {
			$this->Flash->info(__('Error approving the task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
		}

		$this->UserCache->clear('Tasks', $task_slot->person_id);
		if (!$this->request->is('ajax')) {
			$this->Flash->success(__('The assignment has been approved.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', 'task' => $task_slot->task->id]);
		}

		// Read some data required to correctly build the output
		$approved_by = $this->UserCache->read('Person');
		$this->set(compact('approved_by'));
	}

}
