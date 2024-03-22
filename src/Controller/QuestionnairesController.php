<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * Questionnaires Controller
 *
 * @property \App\Model\Table\QuestionnairesTable $Questionnaires
 */
class QuestionnairesController extends AppController {

	// TODO: Proper fix for black-holing when we add questions to a questionnaire
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);
		if (isset($this->Security)) {
			$this->Security->setConfig('unlockedActions', ['edit']);
		}
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
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
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
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
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('questionnaire');
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

		$this->Authorization->authorize($questionnaire);
		$this->Configuration->loadAffiliate($questionnaire->affiliate_id);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('questionnaire', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$questionnaire = $this->Questionnaires->newEmptyEntity();
		$this->Authorization->authorize($questionnaire);
		if ($this->getRequest()->is('post')) {
			$questionnaire = $this->Questionnaires->patchEntity($questionnaire, $this->getRequest()->getData());
			if ($this->Questionnaires->save($questionnaire)) {
				$this->Flash->success(__('The questionnaire has been saved.'));
				return $this->redirect(['action' => 'edit', '?' => ['questionnaire' => $questionnaire->id]]);
			} else {
				$this->Flash->warning(__('The questionnaire could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($questionnaire->affiliate_id);
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('questionnaire', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('questionnaire');
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

		$this->Authorization->authorize($questionnaire);
		$this->Configuration->loadAffiliate($questionnaire->affiliate_id);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$questionnaire = $this->Questionnaires->patchEntity($questionnaire, $this->getRequest()->getData(), [
				'associated' => ['Questions', 'Questions._joinData'],
			]);
			if ($this->Questionnaires->save($questionnaire)) {
				$this->Flash->success(__('The questionnaire has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The questionnaire could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('questionnaire', 'affiliates'));
	}

	/**
	 * Activate method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function activate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('questionnaire');
		try {
			$questionnaire = $this->Questionnaires->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->Authorization->authorize($questionnaire);

		$questionnaire->active = true;
		if (!$this->Questionnaires->save($questionnaire)) {
			$this->Flash->warning(__('Failed to activate questionnaire "{0}".', addslashes($questionnaire->name)));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->set(compact('questionnaire'));
	}

	/**
	 * Deactivate method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function deactivate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('questionnaire');
		try {
			$questionnaire = $this->Questionnaires->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->Authorization->authorize($questionnaire);

		$questionnaire->active = false;
		if (!$this->Questionnaires->save($questionnaire)) {
			$this->Flash->warning(__('Failed to deactivate questionnaire "{0}".', addslashes($questionnaire->name)));
			return $this->redirect(['controller' => 'Questionnaires']);
		}

		$this->set(compact('questionnaire'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('questionnaire');
		try {
			$questionnaire = $this->Questionnaires->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid questionnaire.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($questionnaire);

		$dependencies = $this->Questionnaires->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this questionnaire, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Questionnaires->delete($questionnaire)) {
			$this->Flash->success(__('The questionnaire has been deleted.'));
		} else if ($questionnaire->getError('delete')) {
			$this->Flash->warning(current($questionnaire->getError('delete')));
		} else {
			$this->Flash->warning(__('The questionnaire could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function add_question() {
		$this->getRequest()->allowMethod('ajax');

		$question_id = $this->getRequest()->getQuery('question');
		try {
			$question = $this->Questionnaires->Questions->get($question_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($question);

		$questionnaire_id = $this->getRequest()->getQuery('questionnaire');
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

		$this->Authorization->authorize($questionnaire);

		if (!empty($questionnaire->questions)) {
			$this->Flash->info(__('This question is already part of this questionnaire.'));
			return $this->redirect(['action' => 'view', '?' => ['questionnaire' => $questionnaire_id]]);
		}

		$this->set(compact('question', 'questionnaire'));
	}

	// TODO: Maybe the remove link should only remove the row, and leave unlinking for the save operation, just like add_question does
	public function remove_question() {
		$this->getRequest()->allowMethod('ajax');

		$question_id = $this->getRequest()->getQuery('question');
		try {
			$question = $this->Questionnaires->Questions->get($question_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($question);

		$questionnaire_id = $this->getRequest()->getQuery('questionnaire');
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

		$this->Authorization->authorize($questionnaire);

		if (empty($questionnaire->questions)) {
			$this->Flash->info(__('This question is not part of this questionnaire.'));
			return $this->redirect(['action' => 'view', '?' => ['questionnaire' => $questionnaire_id]]);
		}

		if (!empty($questionnaire->events)) {
			$event_ids = collection($questionnaire->events)->combine('id', 'id')->toArray();

			// Now find if there are responses to this question in one of these events
			$count = TableRegistry::getTableLocator()->get('Responses')->find()
				->where([
					'question_id' => $question_id,
					'event_id IN' => $event_ids,
				])
				->count();
		} else {
			$count = 0;
		}

		// Only questions with no responses through this questionnaire can be removed
		if ($count == 0) {
			$this->Questionnaires->Questions->unlink($questionnaire, [$question], false);
		} else {
			$this->Flash->info(__('This question has responses saved, and cannot be removed for historical purposes. You can deactivate it instead, so it will no longer be shown for new registrations.'));
			return $this->redirect(['action' => 'view', '?' => ['questionnaire' => $questionnaire_id]]);
		}
	}

	public function TODOLATER_consolidate() {
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
