<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use App\View\Helper\ZuluruTimeHelper;

/**
 * TaskSlots Controller
 *
 * @property \App\Model\Table\TaskSlotsTable $TaskSlots
 */
class TaskSlotsController extends AppController {

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions(): array {
		if (!Configure::read('feature.tasks')) {
			return [];
		}

		return ['ical'];
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('slot');
		try {
			$task_slot = $this->TaskSlots->get($id, [
				'contain' => [
					'Tasks' => ['Categories'],
					'People',
					'ApprovedBy',
				],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		$this->Authorization->authorize($task_slot);
		$this->Configuration->loadAffiliate($task_slot->task->category->affiliate_id);

		$this->set(compact('task_slot'));
	}

	// This function takes the parameters the old-fashioned way, to try to be more third-party friendly
	public function ical($id) {
		try {
			$task_slot = $this->TaskSlots->get($id, [
				'contain' => [
					'Tasks' => ['Categories', 'People'],
				],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			return;
		}
		if (!$task_slot->approved) {
			return;
		}

		$this->Configuration->loadAffiliate($task_slot->task->category->affiliate_id);

		$this->set('calendar_type', 'Task');
		$this->set('calendar_name', 'Task');
		$this->getResponse()->withDownload("$id.ics");
		$this->set(compact('task_slot'));
		$this->viewBuilder()->setLayoutPath('ics')->setClassName('Ical');
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$id = $this->getRequest()->getQuery('task');
		try {
			$task = $this->TaskSlots->Tasks->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		$this->Authorization->authorize($task, 'add_slots');
		$this->Configuration->loadAffiliate($task->affiliate_id);

		$task_slot = $this->TaskSlots->newEmptyEntity();
		if ($this->getRequest()->is('post')) {
			$task_slot = $this->TaskSlots->patchEntity($task_slot, array_merge($this->getRequest()->getData(), ['task_id' => $id]));
			$date = $task_slot->task_date;
			if (!$task_slot->getErrors()) {
				for ($days = 0; $days < $this->getRequest()->getData('days_to_repeat'); ++ $days) {
					for ($slots = 0; $slots < $this->getRequest()->getData('number_of_slots'); ++ $slots) {
						$slot = $this->TaskSlots->newEntity(array_merge($this->getRequest()->getData(), ['task_id' => $id, 'task_date' => $date]));
						$this->TaskSlots->save($slot);
					}
					$date = $date->addDays(1);
				}
				$this->Flash->success(__('The task slot(s) have been saved. You may create more similar task slots below.'));
			} else {
				$this->Flash->warning(__('The task slot(s) could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('affiliates', 'task', 'task_slot'));
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('slot');
		try {
			$task_slot = $this->TaskSlots->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		$this->Authorization->authorize($task_slot);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$task_slot = $this->TaskSlots->patchEntity($task_slot, $this->getRequest()->getData());
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
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('slot');
		try {
			$task_slot = $this->TaskSlots->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		$this->Authorization->authorize($task_slot, 'edit');

		$dependencies = $this->TaskSlots->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this task slot, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		if ($this->TaskSlots->delete($task_slot)) {
			$this->Flash->success(__('The task slot has been deleted.'));
		} else if ($task_slot->getError('delete')) {
			$this->Flash->warning(current($task_slot->getError('delete')));
		} else {
			$this->Flash->warning(__('The task slot could not be deleted. Please, try again.'));
		}

		return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
	}

	public function assign() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('slot');
		try {
			$task_slot = $this->TaskSlots->get($id, [
				'contain' => ['Tasks' => ['Categories']]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		$context = new ContextResource($task_slot, ['task' => $task_slot->task]);
		$this->Authorization->authorize($context);

		$person_id = $this->getRequest()->getData('person');
		if (!empty($person_id)) {
			try {
				$this->TaskSlots->People->get($person_id);
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid person.'));
				return $this->redirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]);
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
				return $this->redirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]);
			} catch (RecordNotFoundException $ex) {
				// This is actually the situation that we want: no conflict!
			}
		}

		// If the slot was previously assigned, clear that person's task cache
		if ($task_slot->person_id) {
			$this->UserCache->clear('Tasks', $task_slot->person_id);
		}

		if (!empty($person_id) && $this->Authorization->can($context, 'auto_approve')) {
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
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]);
		}

		if ($task_slot->approved && $person_id) {
			$this->UserCache->clear('Tasks', $person_id);
		}
		if (!$this->getRequest()->is('ajax')) {
			$this->Flash->success(__('The assignment has been saved.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]);
		}

		// Read some data required to correctly build the output
		$affiliates = $this->Authentication->applicableAffiliates(true);
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
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('slot');
		try {
			$task_slot = $this->TaskSlots->get($id, [
				'contain' => ['Tasks' => ['Categories']]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'index']);
		}

		$this->Authorization->authorize($task_slot);

		if (!$task_slot->person_id) {
			$this->Flash->info(__('This task slot has not been assigned.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]);
		}

		if ($task_slot->approved) {
			$this->Flash->info(__('This task slot has already been approved.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]);
		}

		$task_slot = $this->TaskSlots->patchEntity($task_slot, [
			'approved' => true,
			'approved_by_id' => $this->UserCache->currentId(),
		]);

		if (!$this->TaskSlots->save($task_slot)) {
			$this->Flash->info(__('Error approving the task slot.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]);
		}

		$this->UserCache->clear('Tasks', $task_slot->person_id);
		if (!$this->getRequest()->is('ajax')) {
			$this->Flash->success(__('The assignment has been approved.'));
			return $this->redirect(['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task_slot->task->id]]);
		}

		// Read some data required to correctly build the output
		$approved_by = $this->UserCache->read('Person');
		$this->set(compact('approved_by'));
	}

}
