<?php
namespace App\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Groups Controller
 *
 * @property \App\Model\Table\GroupsTable $Groups
 */
class GroupsController extends AppController {

	/**
	 * Index method
	 *
	 * @return void
	 */
	public function index() {
		$this->set('groups', $this->Groups->find('all'));
		$this->set('_serialize', true);
	}

	/**
	 * Activate group method
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise.
	 */
	public function activate() {
		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->query('group');
		try {
			$group = $this->Groups->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid group.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid group.'));
			return $this->redirect(['action' => 'index']);
		}

		$group->active = true;
		if (!$this->Groups->save($group)) {
			$this->Flash->warning(__('Failed to activate group \'\'{0}\'\'.', addslashes($group->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('group'));
	}

	/**
	 * Deactivate group method
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise.
	 */
	public function deactivate() {
		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->query('group');
		try {
			$group = $this->Groups->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid group.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid group.'));
			return $this->redirect(['action' => 'index']);
		}

		$group->active = false;
		if (!$this->Groups->save($group)) {
			$this->Flash->warning(__('Failed to deactivate group \'\'{0}\'\'.', addslashes($group->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('group'));
	}

}
