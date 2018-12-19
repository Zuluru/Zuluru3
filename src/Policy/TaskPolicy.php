<?php
namespace App\Policy;

use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Task;
use Authorization\IdentityInterface;
use Cake\Core\Configure;

class TaskPolicy extends AppPolicy {

	public function before($identity, $resource, $action) {
		if (!Configure::read('feature.tasks')) {
			return false;
		}

		parent::before($identity, $resource, $action);
	}

	public function canIndex(IdentityInterface $identity, $controller) {
		return $identity->isManager() || $identity->isOfficial() || $identity->isVolunteer();
	}

	public function canView(IdentityInterface $identity, Task $task) {
		if ($task->allow_signup || $identity->isManagerOf($task) || $identity->isOfficial() || $identity->isVolunteer()) {
			return true;
		}

		throw new ForbiddenRedirectException(__('Invalid task.'), ['action' => 'index']);
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canEdit(IdentityInterface $identity, Task $task) {
		return $identity->isManagerOf($task);
	}

	public function canAssign(IdentityInterface $identity, Task $task) {
		return $identity->isManagerOf($task) || $identity->isVolunteer();
	}

	public function canDelete(IdentityInterface $identity, Task $task) {
		return $identity->isManagerOf($task);
	}

	public function canAdd_slots(IdentityInterface $identity, Task $task) {
		return $identity->isManagerOf($task);
	}

}
