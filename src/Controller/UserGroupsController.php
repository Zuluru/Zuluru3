<?php
namespace App\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Groups Controller
 *
 * @property \App\Model\Table\UserGroupsTable $UserGroups
 */
class UserGroupsController extends AppController {

	/**
	 * Index method
	 *
	 * @return void
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$this->set('groups', $this->UserGroups->find('all'));
	}

	/**
	 * Activate group method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function activate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('group');
		try {
			$group = $this->UserGroups->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid group.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($group);

		$group->active = true;
		if (!$this->UserGroups->save($group)) {
			$this->Flash->warning(__('Failed to activate group "{0}".', addslashes($group->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('group'));
	}

	/**
	 * Deactivate group method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function deactivate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('group');
		try {
			$group = $this->UserGroups->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid group.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($group);

		$group->active = false;
		if (!$this->UserGroups->save($group)) {
			$this->Flash->warning(__('Failed to deactivate group "{0}".', addslashes($group->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('group'));
	}

}
