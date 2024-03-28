<?php
namespace App\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * Answers Controller
 *
 * @property \App\Model\Table\AnswersTable $Answers
 */
class AnswersController extends AppController {

	/**
	 * Activate method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function activate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('answer');
		try {
			$answer = $this->Answers->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid answer.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->Authorization->authorize($answer);

		$answer->active = true;
		if (!$this->Answers->save($answer)) {
			$this->Flash->warning(__('Failed to activate answer "{0}".', addslashes($answer->answer)));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->set(compact('answer'));
	}

	/**
	 * Deactivate method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function deactivate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('answer');
		try {
			$answer = $this->Answers->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid answer.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->Authorization->authorize($answer);

		$answer->active = false;
		if (!$this->Answers->save($answer)) {
			$this->Flash->warning(__('Failed to deactivate answer "{0}".', addslashes($answer->answer)));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->set(compact('answer'));
	}

}
