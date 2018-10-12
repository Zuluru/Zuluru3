<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * Questionnaires Controller
 *
 * @property \App\Model\Table\QuestionnairesTable $Questionnaires
 */
class QuestionnairesController extends AppController {

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
				// Managers can perform these operations
				if (in_array($this->request->getParam('action'), [
					'index',
					'deactivated',
					'add',
				])) {
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'view',
					'edit',
					'remove_question',
					'activate',
					'deactivate',
					'delete',
				])) {
					// If a questionnaire id is specified, check if we're a manager of that questionnaire's affiliate
					$questionnaire = $this->request->getQuery('questionnaire');
					if ($questionnaire) {
						if (in_array($this->Questionnaires->affiliate($questionnaire), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}

				if (in_array($this->request->getParam('action'), [
					'add_question',
				])) {
					// If a question id is specified, check if we're a manager of that question's affiliate
					$question = $this->request->getQuery('question');
					if ($question) {
						if (in_array($this->Questionnaires->Questions->affiliate($question), $this->UserCache->read('ManagedAffiliateIDs'))) {
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

	// TODO: Proper fix for black-holing when we add questions to a questionnaire
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		$this->Security->config('unlockedActions', ['edit']);
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function index() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->paginate = [
			'contain' => ['Affiliates'],
			'conditions' => [
				'Questionnaires.active' => true,
				'Questionnaires.affiliate_id IN' => $affiliates,
			],
		];
		$query = $this->Questionnaires->find()
			->order(['Affiliates.name']);
		$this->set('questionnaires', $this->paginate($query));
		$this->set('active', true);
		$this->set(compact('affiliates'));
	}

	public function deactivated() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->paginate = [
			'contain' => ['Affiliates'],
			'conditions' => [
				'Questionnaires.active' => false,
				'Questionnaires.affiliate_id IN' => $affiliates,
			],
		];
		$query = $this->Questionnaires->find()
			->order(['Affiliates.name']);
		$this->set('questionnaires', $this->paginate($query));
		$this->set('active', false);
		$this->set(compact('affiliates'));
		$this->render('index');
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function view() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$id = $this->request->getQuery('questionnaire');
		try {
			$questionnaire = $this->Questionnaires->get($id, [
				'contain' => [
					'Affiliates',
					'Questions' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Questions.active' => true]);
						},
						'Answers' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['Answers.active' => true]);
							},
						],
					],
					'Events',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($questionnaire->affiliate_id);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('questionnaire', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function add() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$questionnaire = $this->Questionnaires->newEntity();
		if ($this->request->is('post')) {
			$questionnaire = $this->Questionnaires->patchEntity($questionnaire, $this->request->data);
			if ($this->Questionnaires->save($questionnaire)) {
				$this->Flash->success(__('The questionnaire has been saved.'));
				return $this->redirect(['action' => 'edit', 'questionnaire' => $questionnaire->id]);
			} else {
				$this->Flash->warning(__('The questionnaire could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($questionnaire->affiliate_id);
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('questionnaire', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function edit() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$id = $this->request->getQuery('questionnaire');
		try {
			$questionnaire = $this->Questionnaires->get($id, [
				'contain' => ['Questions']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($questionnaire->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$questionnaire = $this->Questionnaires->patchEntity($questionnaire, $this->request->data, [
				'associated' => ['Questions', 'Questions._joinData'],
			]);
			if ($this->Questionnaires->save($questionnaire)) {
				$this->Flash->success(__('The questionnaire has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The questionnaire could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('questionnaire', 'affiliates'));
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

		$id = $this->request->getQuery('questionnaire');
		try {
			$questionnaire = $this->Questionnaires->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$questionnaire->active = true;
		if (!$this->Questionnaires->save($questionnaire)) {
			$this->Flash->warning(__('Failed to activate questionnaire \'\'{0}\'\'.', addslashes($questionnaire->name)));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->set(compact('questionnaire'));
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

		$id = $this->request->getQuery('questionnaire');
		try {
			$questionnaire = $this->Questionnaires->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$questionnaire->active = false;
		if (!$this->Questionnaires->save($questionnaire)) {
			$this->Flash->warning(__('Failed to deactivate questionnaire \'\'{0}\'\'.', addslashes($questionnaire->name)));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->set(compact('questionnaire'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function delete() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('questionnaire');
		$dependencies = $this->Questionnaires->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this questionnaire, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$questionnaire = $this->Questionnaires->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Questionnaires->delete($questionnaire)) {
			$this->Flash->success(__('The questionnaire has been deleted.'));
		} else if ($questionnaire->errors('delete')) {
			$this->Flash->warning(current($questionnaire->errors('delete')));
		} else {
			$this->Flash->warning(__('The questionnaire could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function add_question() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$question_id = $this->request->getQuery('question');
		try {
			$question = $this->Questionnaires->Questions->get($question_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$questionnaire_id = $this->request->getQuery('questionnaire');
		try {
			$questionnaire = $this->Questionnaires->get($questionnaire_id, [
				'contain' => [
					'Questions' => [
						'queryBuilder' => function (Query $q) use ($question_id) {
							return $q->where(['Questions.id' => $question_id]);
						},
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		}
		if (!empty($questionnaire->questions)) {
			$this->Flash->info(__('This question is already part of this questionnaire.'));
			return $this->redirect(['action' => 'view', 'questionnaire' => $questionnaire_id]);
		}

		$this->set(compact('question', 'questionnaire'));
	}

	// TODO: Maybe the remove link should only remove the row, and leave unlinking for the save operation, just like add_question does
	public function remove_question() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$question_id = $this->request->getQuery('question');
		try {
			$question = $this->Questionnaires->Questions->get($question_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$questionnaire_id = $this->request->getQuery('questionnaire');
		try {
			$questionnaire = $this->Questionnaires->get($questionnaire_id, [
				'contain' => [
					'Questions' => [
						'queryBuilder' => function (Query $q) use ($question_id) {
							return $q->where(['Questions.id' => $question_id]);
						},
					],
					'Events',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		}
		if (empty($questionnaire->questions)) {
			$this->Flash->info(__('This question is not part of this questionnaire.'));
			return $this->redirect(['action' => 'view', 'questionnaire' => $questionnaire_id]);
		}

		$event_ids = collection($questionnaire->events)->combine('id', 'id')->toArray();

		// Now find if there are responses to this question in one of these events
		$count = TableRegistry::get('Responses')->find()
			->where([
				'question_id' => $question_id,
				'event_id IN' => $event_ids,
			])
			->count();

		// Only questions with no responses through this questionnaire can be removed
		if ($count == 0) {
			$this->Questionnaires->Questions->unlink($questionnaire, [$question], false);
		} else {
			$this->Flash->info(__('This question has responses saved, and cannot be removed for historical purposes. You can deactivate it instead, so it will no longer be shown for new registrations.'));
			return $this->redirect(['action' => 'view', 'questionnaire' => $questionnaire_id]);
		}
	}

	public function TODOLATER_consolidate() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->Questionnaire->contain(['Question' => ['order' => 'QuestionnairesQuestion.sort']]);
		$questionnaires = $this->Questionnaire->find('all', [
				'order' => 'Questionnaire.id',
		]);
		$this->QuestionnairesQuestions = ClassRegistry::init('QuestionnairesQuestions');

		$matches = [];
		foreach ($questionnaires as $key_one => $one) {
			foreach ($questionnaires as $key_two => $two) {
				if ($key_one < $key_two) {
					$match = $this->_compare_questionnaires($one, $two);
					if ($match === true) {
						unset($questionnaires[$key_two]);
						$matches[$one['Questionnaire']['id']][$two['Questionnaire']['id']] = $this->_merge_questionnaires ($one, $two);
					} else if ($match !== false) {
						$matches[$one['Questionnaire']['id']][$two['Questionnaire']['id']] = $match;
					}
				}
			}
		}

		$this->set(compact('matches'));
	}

	protected function TODOLATER__compare_questionnaires($one, $two) {
		$q1 = Hash::extract($one, '/Question/id');
		$q2 = Hash::extract($two, '/Question/id');
		return ($q1 == $q2);
	}

	protected function TODOLATER__merge_questionnaires($one, $two) {
		$result =
			$this->Questionnaire->Event->updateAll(
				['questionnaire_id' => $one['Questionnaire']['id']],
				['questionnaire_id' => $two['Questionnaire']['id']]
			) &&
			$this->QuestionnairesQuestions->deleteAll(
				['questionnaire_id' => $two['Questionnaire']['id']], false
			) &&
			$this->Questionnaire->deleteAll(
				['Questionnaire.id' => $two['Questionnaire']['id']], false
			);

		return ($result ? true : 'Failed to merge');
	}

}
