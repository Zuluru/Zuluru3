<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use App\Model\Entity\WaiversPerson;

/**
 * Waivers Controller
 *
 * @property \App\Model\Table\WaiversTable $Waivers
 */
class WaiversController extends AppController {

	/**
	 * _freeActions method
	 *
	 * @return array list of actions that people can perform even if the system wants them to do something else
	 */
	protected function _freeActions() {
		return ['sign'];
	}

	// TODO: Proper fix for black-holing when we add/edit waivers
	public function beforeFilter(\Cake\Event\EventInterface $event) {
		parent::beforeFilter($event);
		if (isset($this->FormProtection)) {
			$this->FormProtection->setConfig('unlockedActions', ['add', 'edit']);
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
			'order' => ['Waivers.id' => 'ASC'],
			'conditions' => ['Waivers.affiliate_id IN' => $affiliates],
		];
		$query = $this->Waivers->find()
			->order(['Affiliates.name']);
		$this->set('waivers', $this->paginate($query));
		$this->set(compact('affiliates'));
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('waiver');
		try {
			$waiver = $this->Waivers->get($id, [
				'contain' => ['Affiliates']
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($waiver);
		$this->Configuration->loadAffiliate($waiver->affiliate_id);

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('waiver', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$waiver = $this->Waivers->newEmptyEntity();
		$this->Authorization->authorize($waiver);
		if ($this->getRequest()->is('post')) {
			$waiver = $this->Waivers->patchEntity($waiver, $this->getRequest()->getData());
			if ($this->Waivers->save($waiver)) {
				$this->Flash->success(__('The waiver has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The waiver could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->getRequest()->getData('affiliate_id'));
			}
		}
		$this->set('affiliates', $this->Authentication->applicableAffiliates(true));
		$this->set(compact('waiver'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('waiver');
		try {
			$waiver = $this->Waivers->find('translations')
				->where(['Waivers.id' => $id])
				->firstOrFail();
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($waiver);

		$can_edit_text = ($this->Waivers->dependencies($id) === false);
		$this->set(compact('can_edit_text'));

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if (array_key_exists('text', $this->getRequest()->getData()) && !$can_edit_text) {
				$this->Flash->warning(__('This waiver has already been signed, so for legal reasons the text cannot be edited.'));
				return $this->redirect(['action' => 'index']);
			}

			$waiver = $this->Waivers->patchEntity($waiver, $this->getRequest()->getData());
			if ($this->Waivers->save($waiver)) {
				$this->Flash->success(__('The waiver has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The waiver could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->Configuration->loadAffiliate($waiver->affiliate_id);

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('waiver', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('waiver');
		try {
			$waiver = $this->Waivers->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($waiver);

		$dependencies = $this->Waivers->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this waiver, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Waivers->delete($waiver)) {
			$this->Flash->success(__('The waiver has been deleted.'));
		} else if ($waiver->getError('delete')) {
			$this->Flash->warning(current($waiver->getError('delete')));
		} else {
			$this->Flash->warning(__('The waiver could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function sign() {
		$id = $this->getRequest()->getQuery('waiver');

		try {
			$waiver = $this->Waivers->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect('/');
		}

		$date = $this->getRequest()->getQuery('date') ? new FrozenDate($this->getRequest()->getQuery('date')) : null;
		list($valid_from, $valid_until) = $waiver->validRange($date);

		$person_id = $this->UserCache->currentId();
		try {
			$person = $this->Waivers->People->get($person_id, [
				'contain' => [
					'Groups',
					'WaiversPeople' => [
						'queryBuilder' => function (Query $q) use ($id) {
							return $q->where(['WaiversPeople.waiver_id' => $id]);
						},
					],
				],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize(new ContextResource($waiver, compact('person', 'date', 'valid_from', 'valid_until')));
		$this->Configuration->loadAffiliate($waiver->affiliate_id);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			if ($this->getRequest()->getData('signed') === 'yes') {
				$person->_joinData = new WaiversPerson(compact('valid_from', 'valid_until'));
				if ($this->Waivers->People->link($waiver, [$person])) {
					$this->UserCache->clear('Waivers', $person_id);
					$this->UserCache->clear('WaiversCurrent', $person_id);

					$this->Flash->success(__('Waiver signed.'));
					return $this->redirect('/');
				} else {
					$this->Flash->warning(__('Failed to save the waiver.'));
				}
			} else {
				$this->Flash->warning(__('Sorry, you may only proceed by agreeing to the waiver.'));
			}
		}

		$this->set(compact('person', 'waiver', 'date', 'valid_from', 'valid_until'));
	}

	public function review() {
		$id = $this->getRequest()->getQuery('waiver');
		if (!$id) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect('/');
		}
		try {
			$waiver = $this->Waivers->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect('/');
		}

		$person_id = $this->UserCache->currentId();
		if (!$person_id) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($waiver);
		$this->Configuration->loadAffiliate($waiver->affiliate_id);
		$conditions = ['Waivers.id' => $id];

		$date = $this->getRequest()->getQuery('date');
		if ($date) {
			$date = new FrozenDate($date);
			list($valid_from, $valid_until) = $waiver->validRange($date);
			if ($valid_from === false) {
				$this->Flash->info(__('Invalid waiver date.'));
				return $this->redirect('/');
			}
			$conditions['valid_from <='] = $date;
			$conditions['valid_until >='] = $date;
		} else {
			list($valid_from, $valid_until) = $waiver->validRange();
		}

		$person = $this->Waivers->People->find()
			->contain([
				'Waivers' => [
					'queryBuilder' => function (Query $q) use ($conditions) {
						return $q
							->where($conditions)
							->order(['WaiversPeople.created' => 'DESC']);
					}
				]
			])
			->where(['People.id' => $person_id])
			->first();

		$this->set(compact('person', 'waiver', 'date', 'valid_from', 'valid_until'));
	}

}
