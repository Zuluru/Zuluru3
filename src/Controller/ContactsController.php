<?php
namespace App\Controller;

use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use App\Form\MessageForm;

/**
 * Contacts Controller
 *
 * @property \App\Model\Table\ContactsTable $Contacts
 */
class ContactsController extends AppController {

	public $paginate = [
		'order' => [
			'Affiliates.name',
			'Contacts.name',
		]
	];

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);

		$affiliate = $this->getRequest()->getQuery('affiliate');
		$affiliates = $this->Authentication->applicableAffiliateIDs();

		$query = $this->Contacts->find()
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			});

		$contacts = $this->paginate($query);

		$this->set(compact('affiliates', 'affiliate', 'contacts'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$contact = $this->Contacts->newEmptyEntity();
		$this->Authorization->authorize($contact);

		if ($this->getRequest()->is('post')) {
			$contact = $this->Contacts->patchEntity($contact, $this->getRequest()->getData());
			if ($this->Contacts->save($contact)) {
				$this->Flash->success(__('The contact has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The contact could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($contact->affiliate_id);
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('contact', 'affiliates'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('contact');
		try {
			$contact = $this->Contacts->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid contact.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Authorization->authorize($contact);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$contact = $this->Contacts->patchEntity($contact, $this->getRequest()->getData());
			if ($this->Contacts->save($contact)) {
				$this->Flash->success(__('The contact has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The contact could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($contact->affiliate_id);
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set(compact('contact', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('contact');
		try {
			$contact = $this->Contacts->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid contact.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($contact);

		$dependencies = $this->Contacts->dependencies($id);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this contact, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Contacts->delete($contact)) {
			$this->Flash->success(__('The contact has been deleted.'));
		} else if ($contact->getError('delete')) {
			$this->Flash->warning(current($contact->getError('delete')));
		} else {
			$this->Flash->warning(__('The contact could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	public function message() {
		$this->Authorization->authorize($this);

		$message = new MessageForm();
		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			try {
				if ($message->execute($this->getRequest()->getData())) {
					$this->Flash->success(__('Your message has been sent.'));
					return $this->redirect('/');
				} else if ($message->getErrors()) {
					$this->Flash->warning(__('The email could not be sent. Please correct the errors below and try again.'));
				} else {
					$this->Flash->warning(__('Error sending email.'));
				}
			} catch (RecordNotFoundException $ex) {
				$this->Flash->warning(__('The email could not be sent. Please correct the errors below and try again.'));
				$message->setError('contact_id', __('You must select a valid contact.'));
			}
		}

		$id = $this->getRequest()->getQuery('contact');
		if (!$id) {
			$affiliates = $this->Authentication->applicableAffiliateIDs();
			$contacts = $this->Contacts->find()
				->contain(['Affiliates'])
				->where(['Contacts.affiliate_id IN' => $affiliates])
				->order(['Affiliates.name', 'Contacts.name']);
			if ($contacts->all()->isEmpty()) {
				$this->Flash->info(__('No contacts have been set up yet on this site.'));
				return $this->redirect('/');
			} else if ($contacts->count() == 1) {
				$this->set('contact', $contacts->first());
			} else {
				if (count($affiliates) > 1) {
					$contacts = $contacts->all()->combine('id', 'name', 'affiliate.name')->toArray();
				} else {
					$contacts = $contacts->all()->combine('id', 'name')->toArray();
				}
				$this->set(compact('contacts'));
			}
		} else {
			try {
				$contact = $this->Contacts->get($id, [
					'contain' => ['Affiliates']
				]);
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid contact.'));
				return $this->redirect('/');
			}
			$this->set(compact('contact'));
		}

		$this->set(compact('message'));
	}
}
