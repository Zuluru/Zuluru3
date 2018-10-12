<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Query;
use App\Model\Traits\CanRegister;

/**
 * Preregistrations Controller
 *
 * @property \App\Model\Table\PreregistrationsTable $Preregistrations
 */
class PreregistrationsController extends AppController {

	use CanRegister;

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
					'index',
					'add',
				])) {
					// If an event id is specified, check if we're a manager of that event's affiliate
					$event = $this->request->getQuery('event');
					if ($event) {
						if (in_array($this->Preregistrations->Events->affiliate($event), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					} else {
						// If there's no event id, this is a top-level operation that all managers can perform
						return true;
					}
				}

				if (in_array($this->request->getParam('action'), [
					'delete',
				])) {
					$preregistration = $this->request->getQuery('preregistration');
					if ($preregistration) {
						if (in_array($this->Preregistrations->affiliate($preregistration), $this->UserCache->read('ManagedAffiliateIDs'))) {
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
			'contain' => [
				'People',
				'Events' => ['Affiliates'],
			],
			'conditions' => [
				'Events.affiliate_id IN' => $affiliates,
			],
		];

		if ($this->request->getQuery('event')) {
			$event_id = $this->request->getQuery('event');
			if (!$event_id) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			}

			try {
				$event = $this->Preregistrations->Events->get($event_id, [
					'contain' => ['Affiliates'],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			}
			$this->Configuration->loadAffiliate($event->affiliate_id);

			$this->paginate['conditions']['Preregistrations.event_id'] = $event_id;
		}
		$this->set('preregistrations', $this->paginate($this->Preregistrations));
		$this->set(compact('event', 'affiliates'));
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

		$event_id = $this->request->getQuery('event');
		$person_id = $this->request->getQuery('person');

		// If we have an event ID, verify it
		if (!empty($event_id)) {
			try {
				$event = $this->Preregistrations->Events->get($event_id, [
					'contain' => ['EventTypes', 'Prices', 'Affiliates'],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			}
			$this->Configuration->loadAffiliate($event->affiliate_id);
		}

		$affiliates = $this->_applicableAffiliateIDs(true);

		if (!empty($event_id) && !empty($person_id)) {
			// If we have an event ID and a person ID, save the preregistration
			$data = [
				'event_id' => $event_id,
				'person_id' => $person_id,
			];
			$found = $this->Preregistrations->find()
				->where($data)
				->count();
			if ($found) {
				$this->Flash->info(__('This person already has a preregistration for this event.'));
				return $this->redirect(['action' => 'add', 'event' => $event_id]);
			}
			list($notices, $allowed, $redirect) = $this->canRegister($person_id, $event, null, ['ignore_date' => true, 'strict' => false]);
			if (!$allowed) {
				$this->Flash->html('{0}', ['params' => ['replacements' => $notices, 'class' => 'warning']]);
				return $this->redirect(['action' => 'add', 'event' => $event_id]);
			}

			$preregistration = $this->Preregistrations->newEntity($data);
			if ($this->Preregistrations->save($preregistration)) {
				$this->UserCache->clear('Preregistrations', $person_id);
				$this->Flash->success(__('The preregistration has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The preregistration could not be saved. Please correct the errors below and try again.'));
			}
		} else {
			// If a form is posted from the initial "add" page with an event ID,
			// we redirect to the page that handles that. This lets us add a batch
			// of similar preregistrations easily with the "return" parameter.
			// Any other post will be from the search form, handled below.
			if ($this->request->is(['post']) && !empty($this->request->data['event'])) {
				return $this->redirect($this->request->data);
			} else if ($this->request->is(['post']) && array_key_exists('first_name', $this->request->data)) {
				// Handle a post to the search form
				$this->_handlePersonSearch(['event']);
			} else if (!$event_id) {
				$events = $this->Preregistrations->Events->find()
					->contain(['Affiliates'])
					->where([
						// Unlikely that we want to let someone post-register for something
						// that closed more than 3 months ago
						'Events.close >' => FrozenDate::now()->subMonths(3),
						'Events.affiliate_id IN' => $affiliates,
					])
					->order(['Affiliates.name', 'Events.open' => 'DESC', 'Events.id' => 'DESC'])
					->toArray();

				if (count($affiliates) > 1) {
					$events = collection($events)->combine('id', 'name', 'affiliate.name')->toArray();
				} else {
					$events = collection($events)->combine('id', 'name')->toArray();
				}

				// Any post that reached this point must not have selected an event
				if ($this->request->is(['post'])) {
					$this->Flash->warning(__('You must select an event!'));
				}
			}
		}

		$this->set(compact('event', 'events', 'affiliates'));
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

		$id = $this->request->getQuery('preregistration');
		try {
			$preregistration = $this->Preregistrations->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid preregistration.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid preregistration.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Preregistrations->delete($preregistration)) {
			$this->UserCache->clear('Preregistrations', $preregistration->person_id);
			$this->Flash->success(__('The preregistration has been deleted.'));
		} else if ($preregistration->errors('delete')) {
			$this->Flash->warning(current($preregistration->errors('delete')));
		} else {
			$this->Flash->warning(__('The preregistration could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
