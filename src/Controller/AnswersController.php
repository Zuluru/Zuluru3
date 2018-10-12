<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;

/**
 * Answers Controller
 *
 * @property \App\Model\Table\AnswersTable $Answers
 */
class AnswersController extends AppController {

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (!Configure::read('feature.registration')) {
				throw new MethodNotAllowedException('Registration is not enabled on this system.');
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'activate',
					'deactivate',
				])) {
					// If an answer id is specified, check if we're a manager of that answer's affiliate
					$answer = $this->request->getQuery('answer');
					if ($answer) {
						if (in_array($this->Answers->affiliate($answer), $this->UserCache->read('ManagedAffiliateIDs'))) {
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
	 * Activate method
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function activate() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('answer');
		try {
			$answer = $this->Answers->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid answer.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid answer.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$answer->active = true;
		if (!$this->Answers->save($answer)) {
			$this->Flash->warning(__('Failed to activate answer \'\'{0}\'\'.', addslashes($answer->answer)));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->set(compact('answer'));
	}

	/**
	 * Deactivate method
	 *
	 * @return void|\Cake\Network\Response Redirects on error, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function deactivate() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('answer');
		try {
			$answer = $this->Answers->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid answer.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid answer.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$answer->active = false;
		if (!$this->Answers->save($answer)) {
			$this->Flash->warning(__('Failed to deactivate answer \'\'{0}\'\'.', addslashes($answer->answer)));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->set(compact('answer'));
	}

}
