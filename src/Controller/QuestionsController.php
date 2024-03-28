<?php
namespace App\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * Questions Controller
 *
 * @property \App\Model\Table\QuestionsTable $Questions
 */
class QuestionsController extends AppController {

	// TODO: Eliminate this if we can find a way around black-holing caused by Ajax field adds
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
			'conditions' => [
				'Questions.active' => true,
				'Questions.affiliate_id IN' => $affiliates,
			],
			'contain' => ['Affiliates'],
			'order' => ['Questions.question'],
		];
		$query = $this->Questions->find()
			->order(['Affiliates.name']);
		$this->set('questions', $this->paginate($query));
		$this->set('active', true);
		$this->set(compact('affiliates'));
	}

	public function deactivated() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->paginate = [
			'conditions' => [
				'Questions.active' => false,
				'Questions.affiliate_id IN' => $affiliates,
			],
			'contain' => ['Affiliates'],
			'order' => ['Questions.question'],
		];
		$query = $this->Questions->find()
			->order(['Affiliates.name']);
		$this->set('questions', $this->paginate($query));
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
		$id = $this->getRequest()->getQuery('question');
		try {
			$question = $this->Questions->get($id, [
				'contain' => ['Affiliates', 'Questionnaires', 'Answers']
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($question);
		$this->Configuration->loadAffiliate($question->affiliate_id);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('question', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$question = $this->Questions->newEmptyEntity();
		$this->Authorization->authorize($this);
		if ($this->getRequest()->is('post')) {
			$question = $this->Questions->patchEntity($question, $this->getRequest()->getData());
			if ($this->Questions->save($question)) {
				$this->Flash->success(__('The question has been saved.'));
				return $this->redirect(['action' => 'edit', '?' => ['question' => $question->id]]);
			} else {
				$this->Flash->warning(__('The question could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($question->affiliate_id);
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('question', 'affiliates'));
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('question');
		try {
			$question = $this->Questions->get($id, [
				'contain' => [
					'Answers' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Answers.sort']);
						},
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($question);
		$this->Configuration->loadAffiliate($question->affiliate_id);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$question = $this->Questions->patchEntity($question, $this->getRequest()->getData());
			if ($this->Questions->save($question)) {
				$this->Flash->success(__('The question has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The question could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('question', 'affiliates'));
	}

	/**
	 * Activate method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function activate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('question');
		try {
			$question = $this->Questions->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($question);

		$question->active = true;
		if (!$this->Questions->save($question)) {
			$this->Flash->warning(__('Failed to activate question "{0}".', addslashes($question->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('question'));
	}

	/**
	 * Deactivate method
	 *
	 * @return void|\Cake\Http\Response Redirects on error, renders view otherwise.
	 */
	public function deactivate() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('question');
		try {
			$question = $this->Questions->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($question);

		$question->active = false;
		if (!$this->Questions->save($question)) {
			$this->Flash->warning(__('Failed to deactivate question "{0}".', addslashes($question->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('question'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('question');
		try {
			$question = $this->Questions->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($question);

		$dependencies = $this->Questions->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this question, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Questions->delete($question)) {
			$this->Flash->success(__('The question has been deleted.'));
		} else if ($question->getError('delete')) {
			$this->Flash->warning(current($question->getError('delete')));
		} else {
			$this->Flash->warning(__('The question could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function add_answer() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('question');
		try {
			$question = $this->Questions->get($id, [
				'contain' => [
					'Answers' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Answers.sort' => 'DESC']);
						}
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($question);

		if (empty($question->answers)) {
			$sort = 1;
		} else {
			$sort = $question->answers[0]->sort + 1;
		}
		$answer = $this->Questions->Answers->newEmptyEntity();
		$answer = $this->Questions->Answers->patchEntity($answer, [
			'question_id' => $id,
			'sort' => $sort,
			'active' => true,
		], ['validate' => false]);
		if ($this->Questions->Answers->save($answer)) {
			$this->set(compact('answer'));
		} else {
			$this->Flash->info(__('Failed to add question.'));
			return $this->redirect(['action' => 'index']);
		}
	}

	public function delete_answer() {
		$this->getRequest()->allowMethod('ajax');

		$id = $this->getRequest()->getQuery('answer');
		try {
			$answer = $this->Questions->Answers->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid answer.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($answer, 'delete');

		// Find if there are responses that use this answer
		$count = TableRegistry::getTableLocator()->get('Responses')->find()
			->where([
				'answer_id' => $id,
			])
			->count();

		// Only answers with no responses can be removed
		if ($count == 0) {
			if (!$this->Questions->Answers->delete($answer)) {
				$this->Flash->warning(__('Failed to remove this answer.'));
				return $this->redirect(['action' => 'view', '?' => ['question' => $answer->question_id]]);
			}
		} else {
			$this->Flash->info(__('This answer has responses saved, and cannot be removed for historical purposes. You can deactivate it instead, so it will no longer be shown for new registrations.'));
			return $this->redirect(['action' => 'view', '?' => ['question' => $answer->question_id]]);
		}
	}

	public function autocomplete() {
		$this->getRequest()->allowMethod('ajax');

		try {
			$affiliate = TableRegistry::getTableLocator()->get('Affiliates')->get($this->getRequest()->getQuery('affiliate'));
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid affiliate.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($affiliate);

		$this->set('questions', $this->Questions->find()
			->where([
				'OR' => [
					'Questions.question LIKE' => '%' . $this->getRequest()->getQuery('term') . '%',
					'Questions_question_translation.content LIKE' => '%' . $this->getRequest()->getQuery('term') . '%',
				],
				'Questions.active' => true,
				'Questions.affiliate_id' => $affiliate->id,
			])
			->order('Questions.question')
			->combine('id', 'question')
			->toArray());
	}

	public function TODOLATER_consolidate() {
		$questions = $this->Questions->find()
			->contain([
				'Answers' => [
					'queryBuilder' => function (Query $q) {
						return $q->order(['Answers.sort']);
					}
				],
			])
			->order(['Questions.id'])
			->toArray();
		$this->Response = ClassRegistry::init('Response');
		$this->QuestionnairesQuestions = ClassRegistry::init('QuestionnairesQuestions');

		$matches = [];
		foreach ($questions as $key_one => $one) {
			foreach ($questions as $key_two => $two) {
				if ($key_one < $key_two) {
					$match = $this->_compare_questions($one, $two);
					if ($match === true) {
						unset($questions[$key_two]);
						$matches[$one['Question']['id']][$two['Question']['id']] = $this->_merge_questions($one, $two);
					} else if ($match !== false) {
						$matches[$one['Question']['id']][$two['Question']['id']] = $match;
					}
				}
			}
		}

		$this->set(compact('matches'));
	}

	protected function TODOLATER__compare_questions($one, $two) {
		if ($one['Question']['question'] != $two['Question']['question']) return false;
		if ($one['Question']['type'] != $two['Question']['type']) return 'different type';
		if (count($one['Answer']) != count($two['Answer'])) return 'different answer count';
		foreach ($one['Answer'] as $key => $answer_one) {
			if (!array_key_exists($key, $two['Answer'])) return 'missing answer';
			$answer_two = $two['Answer'][$key];
			if ($answer_one['answer'] != $answer_two['answer'])
			{
				return "answer {$answer_one['answer']} != {$answer_two['answer']}";
			}
		}

		return true;
	}

	protected function TODOLATER__merge_questions($one, $two) {
		$result =
			$this->Response->updateAll(
				['question_id' => $one['Question']['id']],
				['question_id' => $two['Question']['id']]
			) &&
			$this->QuestionnairesQuestions->updateAll(
				['question_id' => $one['Question']['id']],
				['question_id' => $two['Question']['id']]
			);

		foreach ($one['Answer'] as $key => $answer_one) {
			$answer_two = $two['Answer'][$key];
			$result &= $this->Response->updateAll(
				['answer_id' => $answer_one['id']],
				['answer_id' => $answer_two['id']]
			);
		}

		$result &= $this->Question->Answer->deleteAll(['question_id' => $two['Question']['id']], false);
		$result &= $this->Question->deleteAll(['Question.id' => $two['Question']['id']], false);

		return ($result ? true : 'Failed to merge');
	}

}
