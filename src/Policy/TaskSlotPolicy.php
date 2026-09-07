<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Model\Entity\TaskSlot;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class TaskSlotPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.tasks')) {
			return false;
		}

		return parent::before($identity, $resource, $action);
	}

	public function canView(IdentityInterface $identity, TaskSlot $task_slot) {
		return $identity->isManagerOf($task_slot);
	}

	public function canEdit(IdentityInterface $identity, TaskSlot $task_slot) {
		return $identity->isManagerOf($task_slot);
	}

	public function canAssign(IdentityInterface $identity, ContextResource $resource) {
		$task = $resource->task;
		if (!$task->allow_signup && !$identity->isManagerOf($task)) {
			return new RedirectResult(__('Invalid task slot.'),
				['controller' => 'Tasks', 'action' => 'index']);
		}

		$task_slot = $resource->resource();
		if ($task_slot->person_id && !$identity->isManagerOf($task)) {
			return new RedirectResult(__('This task slot has already been assigned.'),
				['controller' => 'Tasks', 'action' => 'view', '?' => ['task' => $task->id]]);
		}

		return $identity->isManagerOf($task_slot) || $identity->isVolunteer();
	}

	public function canAuto_approve(IdentityInterface $identity, ContextResource $resource) {
		$task = $resource->task;
		$task_slot = $resource->resource();
		return $task->auto_approve || $identity->isManagerOf($task_slot);
	}

	public function canApprove(IdentityInterface $identity, TaskSlot $task_slot) {
		return $task_slot->person_id && $identity->isManagerOf($task_slot);
	}

}
