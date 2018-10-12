<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * Questions Controller
 *
 * @property \App\Model\Table\QuestionsTable $Questions
 */
class QuestionsController extends AppController {

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
					'autocomplete',
				]))
				{
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'view',
					'edit',
					'add_answer',
					'activate',
					'deactivate',
					'delete',
				]))
				{
					// If a question id is specified, check if we're a manager of that question's affiliate
					$question = $this->request->getQuery('question');
					if ($question) {
						if (in_array($this->Questions->affiliate($question), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}

				if (in_array($this->request->getParam('action'), [
					'delete_answer',
				]))
				{
					// If an answer id is specified, check if we're a manager of that answer's affiliate
					$answer = $this->request->getQuery('answer');
					if ($answer) {
						$question = $this->Questions->Answers->field('question_id', ['id' => $answer]);
						if (in_array($this->Questions->affiliate($question), $this->UserCache->read('ManagedAffiliateIDs'))) {
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

	// TODO: Eliminate this if we can find a way around black-holing caused by Ajax field adds
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
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
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
	 * @return void|\Cake\Network\Response
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function view() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$id = $this->request->getQuery('question');
		try {
			$question = $this->Questions->get($id, [
				'contain' => ['Affiliates', 'Questionnaires', 'Answers']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($question->affiliate_id);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('question', 'affiliates'));
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

		$question = $this->Questions->newEntity();
		if ($this->request->is('post')) {
			$question = $this->Questions->patchEntity($question, $this->request->data);
			if ($this->Questions->save($question)) {
				$this->Flash->success(__('The question has been saved.'));
				return $this->redirect(['action' => 'edit', 'question' => $question->id]);
			} else {
				$this->Flash->warning(__('The question could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($question->affiliate_id);
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('question', 'affiliates'));
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

		$id = $this->request->getQuery('question');
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
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($question->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$question = $this->Questions->patchEntity($question, $this->request->data);
			if ($this->Questions->save($question)) {
				$this->Flash->success(__('The question has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The question could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('question', 'affiliates'));
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

		$id = $this->request->getQuery('question');
		try {
			$question = $this->Questions->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$question->active = true;
		if (!$this->Questions->save($question)) {
			$this->Flash->warning(__('Failed to activate question \'\'{0}\'\'.', addslashes($question->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('question'));
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

		$id = $this->request->getQuery('question');
		try {
			$question = $this->Questions->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		$question->active = false;
		if (!$this->Questions->save($question)) {
			$this->Flash->warning(__('Failed to deactivate question \'\'{0}\'\'.', addslashes($question->name)));
			return $this->redirect(['action' => 'index']);
		}

		$this->set(compact('question'));
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

		$id = $this->request->getQuery('question');
		$dependencies = $this->Questions->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this question, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$question = $this->Questions->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Questions->delete($question)) {
			$this->Flash->success(__('The question has been deleted.'));
		} else if ($question->errors('delete')) {
			$this->Flash->warning(current($question->errors('delete')));
		} else {
			$this->Flash->warning(__('The question could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function add_answer() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('question');
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
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid question.'));
			return $this->redirect(['action' => 'index']);
		}
		if (empty($question->answers)) {
			$sort = 1;
		} else {
			$sort = $question->answers[0]->sort + 1;
		}
		$answer = $this->Questions->Answers->newEntity();
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
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$id = $this->request->getQuery('answer');
		try {
			$answer = $this->Questions->Answers->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid answer.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid answer.'));
			return $this->redirect(['action' => 'index']);
		}

		// Find if there are responses that use this answer
		$count = TableRegistry::get('Responses')->find()
			->where([
				'answer_id' => $id,
			])
			->count();

		// Only answers with no responses can be removed
		if ($count == 0) {
			if (!$this->Questions->Answers->delete($answer)) {
				$this->Flash->warning(__('Failed to remove this answer.'));
				return $this->redirect(['action' => 'view', 'question' => $answer->question_id]);
			}
		} else {
			$this->Flash->info(__('This answer has responses saved, and cannot be removed for historical purposes. You can deactivate it instead, so it will no longer be shown for new registrations.'));
			return $this->redirect(['action' => 'view', 'question' => $answer->question_id]);
		}
	}

	public function autocomplete() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$conditions = [
			'Questions.question LIKE' => '%' . $this->request->getQuery('term') . '%',
			'Questions.active' => true,
		];
		$affiliate = $this->request->getQuery('affiliate');
		if ($affiliate && (Configure::read('Perm.is_admin') || in_array($affiliate, $this->UserCache->read('ManagedAffiliateIDs')))) {
			$conditions['Questions.affiliate_id'] = $affiliate;

			$this->set('questions', $this->Questions->find()
				->where($conditions)
				->order('Questions.question')
				->combine('id', 'question')
				->toArray());
		} else {
			$this->set('questions', []);
		}
	}

	public function TODOLATER_consolidate() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

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
