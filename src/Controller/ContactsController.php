<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Query;
use App\Form\MessageForm;

/**
 * Contacts Controller
 *
 * @property \App\Model\Table\ContactsTable $Contacts
 * @property \App\Model\Table\MessagesTable $Messages
 */
class ContactsController extends AppController {

	public $paginate = [
		'order' => [
			'Affiliates.name',
			'Contacts.name',
		]
	];

	/**
	 * isAuthorized method
	 *
	 * @return bool true if access allowed
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if contacts are not enabled
	 */
	public function isAuthorized() {
		try {
			if (!Configure::read('feature.contacts')) {
				throw new MethodNotAllowedException('Contacts are not enabled on this system.');
			}

			if (Configure::read('Perm.is_manager')) {
				// Managers can perform these operations
				if (in_array($this->request->params['action'], [
					'index',
					'add',
				])) {
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->params['action'], [
					'edit',
					'delete',
				])) {
					// If a contact id is specified, check if we're a manager of that contact's affiliate
					$contact = $this->request->query('contact');
					if ($contact) {
						if (in_array($this->Contacts->affiliate($contact), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}

			// Anyone that's logged in can perform these operations
			if (in_array($this->request->params['action'], [
				'message',
			])) {
				return true;
			}
		} catch (RecordNotFoundException $ex) {
		} catch (InvalidPrimaryKeyException $ex) {
		}

		return false;
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if contacts are not enabled
	 */
	public function index() {
		if (!Configure::read('feature.contacts')) {
			throw new MethodNotAllowedException('Contacts are not enabled on this system.');
		}

		$affiliate = $this->request->query('affiliate');
		$affiliates = $this->_applicableAffiliateIDs();

		$query = $this->Contacts->find()
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			});

		$contacts = $this->paginate($query);

		$this->set(compact('affiliates', 'affiliate', 'contacts'));
		$this->set('_serialize', true);
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if contacts are not enabled
	 */
	public function add() {
		if (!Configure::read('feature.contacts')) {
			throw new MethodNotAllowedException('Contacts are not enabled on this system.');
		}

		$contact = $this->Contacts->newEntity();
		if ($this->request->is('post')) {
			$contact = $this->Contacts->patchEntity($contact, $this->request->data);
			if ($this->Contacts->save($contact)) {
				$this->Flash->success(__('The contact has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The contact could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($contact->affiliate_id);
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('contact', 'affiliates'));
		$this->set('_serialize', true);
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if contacts are not enabled
	 */
	public function edit() {
		if (!Configure::read('feature.contacts')) {
			throw new MethodNotAllowedException('Contacts are not enabled on this system.');
		}

		$id = $this->request->query('contact');
		try {
			$contact = $this->Contacts->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid contact.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid contact.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$contact = $this->Contacts->patchEntity($contact, $this->request->data);
			if ($this->Contacts->save($contact)) {
				$this->Flash->success(__('The contact has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The contact could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($contact->affiliate_id);
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set(compact('contact', 'affiliates'));
		$this->set('_serialize', true);
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if contacts are not enabled
	 */
	public function delete() {
		if (!Configure::read('feature.contacts')) {
			throw new MethodNotAllowedException('Contacts are not enabled on this system.');
		}

		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->query('contact');
		$dependencies = $this->Contacts->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this contact, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		try {
			$contact = $this->Contacts->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid contact.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid contact.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Contacts->delete($contact)) {
			$this->Flash->success(__('The contact has been deleted.'));
		} else if ($contact->errors('delete')) {
			$this->Flash->warning(current($contact->errors('delete')));
		} else {
			$this->Flash->warning(__('The contact could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function message() {
		if (!Configure::read('feature.contacts')) {
			throw new MethodNotAllowedException('Contacts are not enabled on this system.');
		}

		$message = new MessageForm();
		if ($this->request->is(['patch', 'post', 'put'])) {
			try {
				if ($message->execute($this->request->data)) {
					$this->Flash->success(__('Your message has been sent.'));
					return $this->redirect('/');
				} else if ($message->errors()) {
					$this->Flash->warning(__('The email could not be sent. Please correct the errors below and try again.'));
				} else {
					$this->Flash->warning(__('Error sending email.'));
				}
			} catch (RecordNotFoundException $ex) {
				$this->Flash->warning(__('The email could not be sent. Please correct the errors below and try again.'));
				$message->setError('contact_id', __('You must select a valid contact.'));
			}
		}

		$id = $this->request->query('contact');
		if (!$id) {
			$affiliates = $this->_applicableAffiliateIDs();
			$contacts = $this->Contacts->find()
				->contain(['Affiliates'])
				->where(['Contacts.affiliate_id IN' => $affiliates])
				->order(['Affiliates.name', 'Contacts.name']);
			if ($contacts->isEmpty()) {
				$this->Flash->info(__('No contacts have been set up yet on this site.'));
				return $this->redirect('/');
			} else if ($contacts->count() == 1) {
				$this->set('contact', $contacts->first());
			} else {
				if (count($affiliates) > 1) {
					$contacts = $contacts->combine('id', 'name', 'affiliate.name')->toArray();
				} else {
					$contacts = $contacts->combine('id', 'name')->toArray();
				}
				$this->set(compact('contacts'));
			}
		} else {
			try {
				$contact = $this->Contacts->get($id, [
					'contain' => ['Affiliates']
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid contact.'));
				return $this->redirect('/');
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid contact.'));
				return $this->redirect('/');
			}
			$this->set(compact('contact'));
		}

		$this->set(compact('message'));
		$this->set('_serialize', true);
	}
}
