<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;

/**
 * Preregistrations Controller
 *
 * @property \App\Model\Table\PreregistrationsTable $Preregistrations
 */
class PreregistrationsController extends AppController {

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$this->paginate = [
			'contain' => [
				'People',
				'Events' => ['Affiliates'],
			],
			'conditions' => [
				'Events.affiliate_id IN' => $affiliates,
			],
		];

		if ($this->getRequest()->getQuery('event')) {
			$event_id = $this->getRequest()->getQuery('event');
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
		} else {
			$event = null;
		}
		$this->set('preregistrations', $this->paginate($this->Preregistrations));
		$this->set(compact('event', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$event_id = $this->getRequest()->getQuery('event');
		$person_id = $this->getRequest()->getQuery('person');

		// If we have an event ID, verify it
		if (!empty($event_id)) {
			try {
				$event = $this->Preregistrations->Events->get($event_id, [
					'contain' => ['EventTypes', 'Prices', 'Affiliates', 'Divisions'],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			}
			$this->Configuration->loadAffiliate($event->affiliate_id);

			$this->Authorization->authorize($event, 'add_preregistration');
		} else {
			$event = null;
		}

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

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
			$context = new ContextResource($event, ['person_id' => $person_id, 'ignore_date' => true, 'strict' => false]);
			if (!$this->Authorization->can($context, 'register')) {
				$this->Flash->html('{0}', ['params' => ['replacements' => $context->notices, 'class' => 'warning']]);
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
			if ($this->getRequest()->is(['post']) && !empty($this->getRequest()->getData('event'))) {
				return $this->redirect($this->getRequest()->getData());
			} else if ($this->getRequest()->is('ajax')) {
				// Handle a post to the search form or a pagination link
				$this->_handlePersonSearch(['event']);
			} else if (!$event_id) {
				$this->Authorization->authorize($this);

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
				$this->set(compact('events'));

				// Any post that reached this point must not have selected an event
				if ($this->getRequest()->is(['post'])) {
					$this->Flash->warning(__('You must select an event!'));
				}
			}
		}

		$this->set(compact('event', 'affiliates'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('preregistration');
		try {
			$preregistration = $this->Preregistrations->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid preregistration.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid preregistration.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($preregistration);

		if ($this->Preregistrations->delete($preregistration)) {
			$this->UserCache->clear('Preregistrations', $preregistration->person_id);
			$this->Flash->success(__('The preregistration has been deleted.'));
		} else if ($preregistration->getError('delete')) {
			$this->Flash->warning(current($preregistration->getError('delete')));
		} else {
			$this->Flash->warning(__('The preregistration could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
