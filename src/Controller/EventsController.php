<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\Network\Exception\MethodNotAllowedException;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use App\Model\Entity\EventsConnection;
use App\Model\Traits\CanRegister;

/**
 * Events Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 */
class EventsController extends AppController {

	use CanRegister;

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	protected function _publicActions() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		if (Configure::read('Perm.is_manager')) {
			// If an event id is specified, check if we're a manager of that event's affiliate
			$event = $this->request->getQuery('event');
			if ($event) {
				if (!in_array($this->Events->affiliate($event), $this->UserCache->read('ManagedAffiliateIDs'))) {
					Configure::write('Perm.is_manager', false);
				}
			}
		}

		return ['index', 'view', 'wizard'];
	}

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
					'add',
					'add_price',
					'event_type_fields',
				])) {
					return true;
				}

				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->getParam('action'), [
					'edit',
					'connections',
					'delete',
				])) {
					// If an event id is specified, check if we're a manager of that event's affiliate
					$event = $this->request->getQuery('event');
					if ($event) {
						if (in_array($this->Events->affiliate($event), $this->UserCache->read('ManagedAffiliateIDs'))) {
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
		$this->Security->config('unlockedActions', ['add', 'edit']);
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

		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
			$year = $this->request->getQuery('year');
			if ($year) {
				$conditions = ['OR' => [
					[
						'Events.open >' => $year,
						'Events.open <' => (string) ($year + 1),
					],
					[
						'Events.close >' => $year,
						'Events.close <' => (string) ($year + 1),
					],
				]];
			} else {
				// Admins and managers see things that have recently closed, or open far in the future
				// TODO: Use the 'open' finder
				$conditions = [
					'Events.open <' => FrozenDate::now()->addDays(180),
					'Events.close >' => FrozenDate::now()->subDays(30),
				];
			}
		} else {
			$conditions = [
				'Events.open <' => FrozenDate::now()->addDays(30),
				'Events.close >' => FrozenDate::now(),
			];
		}

		$affiliates = $this->_applicableAffiliateIDs();

		// Find any preregistrations
		$prereg = $this->UserCache->read('Preregistrations');
		if (!empty($prereg)) {
			$conditions = ['OR' => [
				$conditions,
				'Events.id IN' => collection($prereg)->extract('event_id')->toArray()
			]];
		}
		$conditions['Events.affiliate_id IN'] = $affiliates;

		$events = $this->Events->find()
			->where($conditions)
			->order(['Affiliates.name', 'Events.event_type_id', 'Events.open', 'Events.close', 'Events.id'])
			->contain([
				'EventTypes',
				'Affiliates',
				'Prices' => [
					'queryBuilder' => function (Query $q) {
						return $q->order(['Prices.open', 'Prices.close', 'Prices.id']);
					}
				],
				'Divisions' => ['Leagues', 'Days']
			])
			->toArray();

		$years = $this->Events->find()
			->hydrate(false)
			->select(['year' => 'DISTINCT YEAR(Events.open)'])
			->where([
				'YEAR(Events.open) !=' => 0,
				'Events.affiliate_id IN' => $affiliates,
			])
			->order(['Events.open'])
			->toArray();

		$this->set(compact('affiliates', 'events', 'years', 'year'));
	}

	public function wizard($step = null) {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->Events->Registrations->expireReservations();

		if (!Configure::read('Perm.is_logged_in')) {
			return $this->redirect(['action' => 'index']);
		}
		$id = $this->UserCache->currentId();

		// Check whether this user is considered new or inactive for the purposes of registration
		$is_new = ($this->UserCache->read('Person.status') == 'new');
		$is_inactive = ($this->UserCache->read('Person.status') == 'inactive');
		// If the user is not yet approved, we may let them register but not pay
		if ($is_new && Configure::read('registration.allow_tentative')) {
			$person = $this->UserCache->read('Person');
			$person->affiliates = $this->UserCache->read('Affiliates');
			$duplicates = $this->Events->Registrations->People->find('duplicates', compact('person'));
			if ($duplicates->count() == 0) {
				$is_new = false;
			}
		}
		if ($is_new) {
			$this->Flash->info(__('You are not allowed to register for events until your profile has been approved by an administrator.'));
			return $this->redirect('/');
		}
		if ($is_inactive) {
			$this->Flash->info(__('You are not allowed to register for events until your profile has been reactivated.'));
			return $this->redirect('/');
		}

		// Find any preregistrations
		$prereg = collection($this->UserCache->read('Preregistrations'))->extract('event_id')->toArray();

		// Find all the events that are potentially available
		// TODO: Eliminate the events that don't match the step, if any
		$affiliates = $this->_applicableAffiliateIDs();

		$conditions = [
			'Events.open <' => FrozenDate::now()->addDays(30),
			'Events.close >' => FrozenDate::now(),
		];
		if (!empty($prereg)) {
			$conditions = [
				'OR' => [
					$conditions,
					'Events.id IN' => $prereg,
				],
			];
		}
		$conditions['Events.affiliate_id IN'] = $affiliates;

		$events = $this->Events->find()
			->contain([
				'EventTypes',
				'Affiliates',
				'Prices' => [
					'queryBuilder' => function (Query $q) {
						return $q->order(['Prices.open', 'Prices.close', 'Prices.id']);
					}
				],
				'Divisions' => ['Leagues', 'Days'],
			])
			->where($conditions)
			->order(['Events.event_type_id', 'Events.open', 'Events.close', 'Events.id'])
			->toArray();

		$types = $this->Events->EventTypes->find()
			->hydrate(false)
			->order(['EventTypes.id'])
			->toArray();

		// Prune out the events that are not possible
		foreach ($events as $key => $event) {
			list($notices, $allowed, $redirect) = $this->canRegister($id, $event, null, ['strict' => false, 'waiting' => true]);
			if (!$allowed) {
				unset($events[$key]);
			}
		}

		$this->set(compact('events', 'types', 'affiliates', 'step'));
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

		$this->Events->Registrations->expireReservations();

		$id = $this->request->getQuery('event');
		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager')) {
			// Admins and managers see things that have recently closed, or open far in the future
			$close = FrozenDate::now()->subDays(30);
			$open = FrozenDate::now()->addDays(180);
		} else {
			$close = FrozenDate::now();
			$open = FrozenDate::now()->addDays(30);
		}

		try {
			$event = $this->Events->get($id, [
				'contain' => [
					'EventTypes',
					'Prices' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Prices.open', 'Prices.close', 'Prices.id']);
						},
					],
					'Divisions' => [
						'GameSlots' => [
							'Fields' => ['Facilities'],
						],
						'Days',
						'Events' => [
							'queryBuilder' => function (Query $q) use ($id) {
								return $q->where(['Events.id !=' => $id]);
							},
							'EventTypes',
						],
					],
					'Alternate' => [
						'EventTypes',
						'conditions' => [
							'Alternate.open <' => $open,
							'Alternate.close >' => $close,
						],
					],
					'Preregistrations' => ['People'],
					'Affiliates',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'wizard']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'wizard']);
		}

		$this->Configuration->loadAffiliate($event->affiliate_id);

		// Extract some more details, if it's a division registration
		if (!empty($event->division->game_slots)) {
			// Find the list of facilities and time slots
			// TODOLATER: Probably some nice collection countBy that could simplify this?
			$facilities = $times = [];
			foreach ($event->division->game_slots as $slot) {
				$facilities[$slot->field->facility->id] = $slot->field->facility->name;
				if ($slot->game_end) {
					$times[$slot->game_start->toTimeString() . ':' . $slot->game_end->toTimeString()] = $slot;
				} else {
					$times[$slot->game_start->toTimeString()] = $slot;
				}
			}
			ksort ($times);
		}

		if (Configure::read('Perm.is_logged_in')) {
			list($notices, $allowed, $redirect) = $this->canRegister($this->UserCache->currentId(), $event, null, ['all_rules' => true]);
			$this->set(compact('notices', 'allowed'));
		}

		$affiliates = $this->_applicableAffiliateIDs(true);
		$this->set(compact('id', 'event', 'facilities', 'times', 'affiliates'));
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

		$event = $this->Events->newEntity();
		if ($this->request->is('post')) {
			// Validation requires this information
			if (!empty($this->request->data['event_type_id'])) {
				$type = $this->Events->EventTypes->field('type', ['id' => $this->request->data['event_type_id']]);
			} else {
				$type = 'default';
			}

			$event = $this->Events->patchEntity($event, $this->request->data, ['validate' => $type]);
			if ($this->Events->save($event, ['prices' => $event->prices])) {
				$this->Flash->success(__('The event has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The event could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($event->affiliate_id);
			}
		} else if ($this->request->getQuery('event')) {
			// To clone an event, read the old one and remove the id
			try {
				$event = $this->Events->cloneWithoutIds($this->request->getQuery('event'), [
					'contain' => [
						'EventTypes',
						'Prices' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['Prices.open', 'Prices.close', 'Prices.id']);
							},
						],
					],
				]);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set('eventTypes', $this->Events->EventTypes->find('list'));
		$this->set('questionnaires', $this->Events->Questionnaires->find('list', [
			'conditions' => [
				'Questionnaires.active' => true,
				'Questionnaires.affiliate_id IN' => array_keys($affiliates),
			],
		]));

		if ($event->has('event_type_id')) {
			$type = $this->Events->EventTypes->field('type', ['id' => $event->event_type_id]);
			$event_obj = $this->moduleRegistry->load("EventType:{$type}");
		}

		$this->set(compact('event', 'affiliates', 'event_obj'));
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

		$id = $this->request->getQuery('event');
		try {
			$event = $this->Events->get($id, [
				'contain' => [
					'EventTypes',
					'Prices' => [
						'queryBuilder' => function ($q) {
							return $q->order(['Prices.open', 'Prices.close', 'Prices.id']);
						},
					],
					'Divisions',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		}
		$this->Configuration->loadAffiliate($event->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			// Validation requires this information
			if (!empty($this->request->data['event_type_id'])) {
				$type = $this->Events->EventTypes->field('type', ['id' => $this->request->data['event_type_id']]);
			} else {
				$type = 'default';
			}

			$event = $this->Events->patchEntity($event, $this->request->data, ['validate' => $type]);
			if ($this->Events->save($event, ['prices' => $event->prices])) {
				$this->Flash->success(__('The event has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The event could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->_applicableAffiliates(true);
		$this->set('eventTypes', $this->Events->EventTypes->find('list'));
		$this->set('questionnaires', $this->Events->Questionnaires->find('list', ['conditions' => [
			'Questionnaires.active' => true,
			'Questionnaires.affiliate_id IN' => array_keys($affiliates),
		]]));
		$type = $this->Events->EventTypes->field('type', ['id' => $event->event_type_id]);
		$event_obj = $this->moduleRegistry->load("EventType:{$type}");
		$this->set(compact('event', 'affiliates', 'event_obj'));
	}

	public function event_type_fields() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');

		$type = $this->Events->EventTypes->field('type', ['id' => $this->request->data['event_type_id']]);
		$this->set('event_obj', $this->moduleRegistry->load("EventType:{$type}"));
		$this->set('affiliates', $this->_applicableAffiliates(true));
	}

	/**
	 * Add price function
	 *
	 * @return void Renders view, just an empty price point block with a random index.
	 * @throws \Cake\Network\Exception\MethodNotAllowedException if registration is not enabled
	 */
	public function add_price() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$this->viewBuilder()->className('Ajax.Ajax');
		$this->request->allowMethod('ajax');
		$event = $this->Events->newEntity();
		$this->set(compact('event'));
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

		$id = $this->request->getQuery('event');
		$dependencies = $this->Events->dependencies($id, ['Prices', 'Predecessor', 'Successor', 'Alternate', 'PredecessorTo', 'SuccessorTo', 'AlternateTo']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this event, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action'=>'index']);
		}

		try {
			$event = $this->Events->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		}

		if ($this->Events->delete($event)) {
			$this->Flash->success(__('The event has been deleted.'));
		} else if ($event->errors('delete')) {
			$this->Flash->warning(current($event->errors('delete')));
		} else {
			$this->Flash->warning(__('The event could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}

	public function connections() {
		if (!Configure::read('feature.registration')) {
			throw new MethodNotAllowedException('Registration is not enabled on this system.');
		}

		$id = $this->request->getQuery('event');
		try {
			$event = $this->Events->get($id, [
				'contain' => [
					'Predecessor',
					'Successor',
					'Alternate',
					'PredecessorTo',
					'SuccessorTo',
					'AlternateTo',
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Configuration->loadAffiliate($event->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			// Alternates always go both ways
			$this->request->data['alternate_to'] = $this->request->data['alternate'];
			$event = $this->Events->patchEntity($event, $this->request->data);

			// We need to add join data for all of the connections we're about to make,
			// to tell the system what type of connections they are.
			foreach (Configure::read('event_connection') as $type => $name) {
				foreach ($event->$name as $connection) {
					$connection->_joinData = new EventsConnection(['connection' => $type]);
				}

				$to_name = "{$name}_to";
				foreach ($event->$to_name as $connection) {
					$connection->_joinData = new EventsConnection(['connection' => $type]);
				}
			}

			if ($this->Events->save($event)) {
				$this->Flash->success(__('The connections have been saved.'));
				return $this->redirect(['action' => 'view', 'event' => $id]);
			} else {
				$this->Flash->warning(__('The connections could not be saved. Please correct the errors below and try again.'));
			}
		}

		$event_types = $this->Events->EventTypes->find('list')->toArray();
		$events = $this->Events->find()
			->contain(['EventTypes'])
			->where([
				'Events.id !=' => $id,
				"Events.open > DATE_ADD('{$event->open}', INTERVAL -18 MONTH)",
				"Events.open < DATE_ADD('{$event->open}', INTERVAL 18 MONTH)",
				"Events.close > DATE_ADD('{$event->close}', INTERVAL -18 MONTH)",
				"Events.close < DATE_ADD('{$event->close}', INTERVAL 18 MONTH)",
				'Events.affiliate_id IN' => $event->affiliate_id,
			])
			->order(['Events.event_type_id', 'Events.open', 'Events.close', 'Events.id'])
			->indexBy('id')
			->toArray();

		// Limit lists of events by open and close dates
		$predecessor = $successor = $alternate = array_fill_keys(array_values($event_types), []);
		foreach ($events as $other_event) {
			$type = $event_types[$other_event->event_type_id];
			if ($other_event->open < $event->open) {
				$predecessor[$type][$other_event->id] = $other_event->name;
			}
			if ($other_event->close > $event->close) {
				$successor[$type][$other_event->id] = $other_event->name;
			}
			if ($other_event->close > $event->open && $other_event->open < $event->close) {
				$alternate[$type][$other_event->id] = $other_event->name;
			}
		}
		$successorTo = $predecessor;
		$predecessorTo = $successor;

		$this->set(compact('event', 'events', 'event_types', 'predecessor', 'predecessorTo', 'successor', 'successorTo', 'alternate'));
	}

}
