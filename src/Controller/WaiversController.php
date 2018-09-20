<?php
namespace App\Controller;

use Cake\Core\Configure;
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

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 */
	public function isAuthorized() {
		try {
			if ($this->UserCache->read('Person.status') == 'locked') {
				return false;
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations
				if (in_array($this->request->params['action'], [
					'index',
					'add',
				]))
				{
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->params['action'], [
					'view',
					'edit',
					'delete',
				]))
				{
					// If a waiver id is specified, check if we're a manager of that waiver's affiliate
					$waiver = $this->request->query('waiver');
					if ($waiver) {
						if (in_array($this->Waivers->affiliate($waiver), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}

			// Anyone that's logged in can perform these operations
			if (in_array($this->request->params['action'], [
				'sign',
				'review',
			]))
			{
				return true;
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	// TODO: Proper fix for black-holing when we add/edit waivers
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		$this->Security->config('unlockedActions', ['add', 'edit']);
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$affiliates = $this->_applicableAffiliateIDs(true);

		$this->paginate = [
			'contain' => ['Affiliates'],
			'order' => ['Waivers.id'],
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
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->query('waiver');
		try {
			$waiver = $this->Waivers->get($id, [
				'contain' => ['Affiliates']
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($waiver->affiliate_id);

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('waiver', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$waiver = $this->Waivers->newEntity();
		if ($this->request->is('post')) {
			$waiver = $this->Waivers->patchEntity($waiver, $this->request->data);
			if ($this->Waivers->save($waiver)) {
				$this->Flash->success(__('The waiver has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The waiver could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($this->request->data['affiliate_id']);
			}
		}
		$this->set('affiliates', $this->_applicableAffiliates(true));
		$this->set(compact('waiver', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->query('waiver');
		try {
			$waiver = $this->Waivers->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect(['action' => 'index']);
		}

		$can_edit_text = ($this->Waivers->dependencies($id) === false);
		$this->set(compact('can_edit_text'));

		if ($this->request->is(['patch', 'post', 'put'])) {
			if (array_key_exists('text', $this->request->data) && !$can_edit_text) {
				$this->Flash->warning(__('This waiver has already been signed, so for legal reasons the text cannot be edited.'));
				return $this->redirect(['action' => 'index']);
			}

			$waiver = $this->Waivers->patchEntity($waiver, $this->request->data);
			if ($this->Waivers->save($waiver)) {
				$this->Flash->success(__('The waiver has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The waiver could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->Configuration->loadAffiliate($waiver->affiliate_id);

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('waiver', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->query('waiver');
		$dependencies = $this->Waivers->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this waiver, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$waiver = $this->Waivers->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Waivers->delete($waiver)) {
			$this->Flash->success(__('The waiver has been deleted.'));
		} else if ($waiver->errors('delete')) {
			$this->Flash->warning(current($waiver->errors('delete')));
		} else {
			$this->Flash->warning(__('The waiver could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function sign() {
		$waiver_id = $this->request->query('waiver');

		try {
			$waiver = $this->Waivers->get($waiver_id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect('/');
		}
		if (!$waiver->active) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect('/');
		}
		$this->Configuration->loadAffiliate($waiver->affiliate_id);

		// Make sure they're waivering for a valid date
		$date = $this->request->query('date');
		if (!$date) {
			$this->Flash->info(__('Invalid waiver date.'));
			return $this->redirect('/');
		}
		$date = new FrozenDate($date);
		if (!$waiver->canSign($date)) {
			$this->Flash->info(__('Invalid waiver date.'));
			return $this->redirect('/');
		}

		$person_id = $this->UserCache->currentId();
		try {
			$person = $this->Waivers->People->get($person_id, [
				'contain' => [
					'Groups',
					'Waivers' => [
						'queryBuilder' => function (Query $q) use ($waiver_id) {
							return $q->where(['Waivers.id' => $waiver_id]);
						},
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		// Check if they have already signed this waiver
		if ($this->Waivers->signed($person->waivers, $date)) {
			$this->Flash->info(__('You have already accepted this waiver.'));
			return $this->redirect('/');
		}

		list($valid_from, $valid_until) = $waiver->validRange($date);
		if ($valid_from === false) {
			$this->Flash->info(__('Invalid waiver date.'));
			return $this->redirect('/');
		}

		// Don't allow adults to sign a waiver on behalf of another adult
		if ($person_id != $this->UserCache->realId() && !$this->_isChild($person)) {
			$this->Flash->info(__('You are not allowed to accept this waiver on behalf of another person.'));
			return $this->forceRedirect('/');
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			if ($this->request->data['signed'] == 'yes') {
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
		$waiver_id = $this->request->query('waiver');
		if (!$waiver_id) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect('/');
		}
		$waiver = $this->Waivers->get($waiver_id);
		if (!$waiver) {
			$this->Flash->info(__('Invalid waiver.'));
			return $this->redirect('/');
		}

		$person_id = $this->UserCache->currentId();
		if (!$person_id) {
			$this->Flash->info(__('Invalid person.'));
			return $this->redirect('/');
		}

		$this->Configuration->loadAffiliate($waiver->affiliate_id);
		$conditions = ['Waivers.id' => $waiver_id];

		$date = $this->request->query('date');
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
