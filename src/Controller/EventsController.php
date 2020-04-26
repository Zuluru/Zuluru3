<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use App\Model\Entity\EventsConnection;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;

/**
 * Events Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 */
class EventsController extends AppController {

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 * @throws \Cake\Http\Exception\MethodNotAllowedException if registration is not enabled
	 */
	protected function _noAuthenticationActions() {
		if (!Configure::read('feature.registration')) {
			return [];
		}

		return ['index', 'view', 'wizard'];
	}

	// TODO: Eliminate this if we can find a way around black-holing caused by Ajax field adds
	public function beforeFilter(\Cake\Event\Event $event) {
		parent::beforeFilter($event);
		if (isset($this->Security)) {
			$this->Security->config('unlockedActions', ['add', 'edit']);
		}
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function index() {
		if ($this->Authorization->can(\App\Controller\EventsController::class, 'add')) {
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
			$year = null;
			$conditions = [
				'Events.open <' => FrozenDate::now()->addDays(30),
				'Events.close >' => FrozenDate::now(),
			];
		}

		$affiliates = $this->Authentication->applicableAffiliateIDs();

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
			->enableHydration(false)
			->select(['year' => 'DISTINCT YEAR(Events.open)'])
			->where([
				'YEAR(Events.open) !=' => 0,
				'Events.affiliate_id IN' => $affiliates,
			])
			->order(['year'])
			->toArray();

		$this->set(compact('affiliates', 'events', 'years', 'year'));
	}

	public function wizard($step = null) {
		$this->Authorization->authorize($this);
		$this->Events->Registrations->expireReservations();
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
		$affiliates = $this->Authentication->applicableAffiliateIDs();

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
			->enableHydration(false)
			->order(['EventTypes.id'])
			->toArray();

		// Prune out the events that are not possible
		foreach ($events as $key => $event) {
			if (!$this->Authorization->can(new ContextResource($event, ['person_id' => $id, 'strict' => false, 'waiting' => true]), 'register')) {
				unset($events[$key]);
			}
		}

		$this->set(compact('events', 'types', 'affiliates', 'step'));
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$this->Events->Registrations->expireReservations();

		$id = $this->request->getQuery('event');
		if ($this->Authorization->can($this, 'add')) {
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
		$facilities = $times = [];
		if (!empty($event->division->game_slots)) {
			// Find the list of facilities and time slots
			// TODOLATER: Probably some nice collection countBy that could simplify this?
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

		$identity = $this->Authentication->getIdentity();
		if ($identity && $identity->isLoggedIn()) {
			$context = new ContextResource($event, ['all_rules' => true]);
			$allowed = $this->Authorization->can($context, 'register');
			$this->set(['notices' => $context->notices ?: [], 'allowed' => $allowed]);
		}

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('id', 'event', 'facilities', 'times', 'affiliates'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$this->Authorization->authorize($this);
		$event = $this->Events->newEntity();

		if ($this->request->is('post')) {
			// Validation requires this information
			if (!empty($this->request->getData('event_type_id'))) {
				$type = $this->Events->EventTypes->field('type', ['EventTypes.id' => $this->request->getData('event_type_id')]);
			} else {
				$type = 'default';
			}

			$event = $this->Events->patchEntity($event, $this->request->getData(), ['validate' => $type]);
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
				$event = $this->Events->get($this->request->getQuery('event'), [
					'contain' => [
						'EventTypes',
						'Prices' => [
							'queryBuilder' => function (Query $q) {
								return $q->order(['Prices.open', 'Prices.close', 'Prices.id']);
							},
						],
					],
				]);

				$this->Authorization->authorize($event, 'edit');
				$event = $this->Events->cloneWithoutIds($event);
			} catch (RecordNotFoundException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			} catch (InvalidPrimaryKeyException $ex) {
				$this->Flash->info(__('Invalid event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'index']);
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set('eventTypes', $this->Events->EventTypes->find('list'));
		$this->set('questionnaires', $this->Events->Questionnaires->find('list', [
			'conditions' => [
				'Questionnaires.active' => true,
				'Questionnaires.affiliate_id IN' => array_keys($affiliates),
			],
		]));

		if ($event->has('event_type_id')) {
			$type = $this->Events->EventTypes->field('type', ['EventTypes.id' => $event->event_type_id]);
			$event_obj = $this->moduleRegistry->load("EventType:{$type}");
		} else {
			$event_obj = null;
		}

		$this->set(compact('event', 'affiliates', 'event_obj'));
		$this->render('edit');
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
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

		$this->Authorization->authorize($event);
		$this->Configuration->loadAffiliate($event->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			// Validation requires this information
			if (!empty($this->request->getData('event_type_id'))) {
				$type = $this->Events->EventTypes->field('type', ['EventTypes.id' => $this->request->getData('event_type_id')]);
			} else {
				$type = 'default';
			}

			$event = $this->Events->patchEntity($event, $this->request->getData(), ['validate' => $type]);
			if ($this->Events->save($event, ['prices' => $event->prices])) {
				$this->Flash->success(__('The event has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The event could not be saved. Please correct the errors below and try again.'));
			}
		}

		$affiliates = $this->Authentication->applicableAffiliates(true);
		$this->set('eventTypes', $this->Events->EventTypes->find('list'));
		$this->set('questionnaires', $this->Events->Questionnaires->find('list', ['conditions' => [
			'Questionnaires.active' => true,
			'Questionnaires.affiliate_id IN' => array_keys($affiliates),
		]]));
		$type = $this->Events->EventTypes->field('type', ['EventTypes.id' => $event->event_type_id]);
		$event_obj = $this->moduleRegistry->load("EventType:{$type}");
		$this->set(compact('event', 'affiliates', 'event_obj'));
	}

	public function event_type_fields() {
		// TODO: Change this to authorize on the event_type, in case we make them affiliate-specific
		$this->Authorization->authorize($this);

		$this->request->allowMethod('ajax');

		$type = $this->Events->EventTypes->field('type', ['EventTypes.id' => $this->request->getData('event_type_id')]);
		$this->set('event_obj', $this->moduleRegistry->load("EventType:{$type}"));
		$this->set('affiliates', $this->Authentication->applicableAffiliates(true));
	}

	/**
	 * Add price function
	 *
	 * @return void Renders view, just an empty price point block with a random index.
	 */
	public function add_price() {
		$this->Authorization->authorize($this);

		$this->request->allowMethod('ajax');
		$event = $this->Events->newEntity();
		$this->set(compact('event'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->getQuery('event');
		try {
			$event = $this->Events->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($event, 'edit');

		$dependencies = $this->Events->dependencies($id, ['Prices', 'Predecessor', 'Successor', 'Alternate', 'PredecessorTo', 'SuccessorTo', 'AlternateTo']);
		if ($dependencies !== false) {
			$this->Flash->warning(__('The following records reference this event, so it cannot be deleted.') . '<br>' . $dependencies, ['params' => ['escape' => false]]);
			return $this->redirect(['action'=>'index']);
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

		$this->Authorization->authorize($event, 'edit');
		$this->Configuration->loadAffiliate($event->affiliate_id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			// Alternates always go both ways
			$this->request->data['alternate_to'] = $this->request->getData('alternate');
			$event = $this->Events->patchEntity($event, $this->request->getData());

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

	/**
	 * Refund method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function refund() {
		$this->paginate['order'] = ['Registrations.payment' => 'DESC', 'Registrations.created' => 'DESC'];
		$this->paginate['limit'] = 100;
		$id = $this->request->getQuery('event');
		$price_id = $this->request->getQuery('price');

		try {
			$event = $this->Events->get($id, [
				'contain' => [
					'EventTypes',
					'Prices' => [
						'queryBuilder' => function (Query $q) use ($price_id) {
							if ($price_id) {
								$q->where(['Prices.id' => $price_id]);
							}
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

		$this->Authorization->authorize($event, 'refund');
		$this->Configuration->loadAffiliate($event->affiliate_id);
		$event->prices = collection($event->prices)->indexBy('id')->toArray();

		$refund = $this->Events->Registrations->Payments->newEntity();

		if ($this->request->is('post')) {
			$data = $this->request->getData();
			$refund = $this->Events->Registrations->Payments->patchEntity($refund, $data);

			if (!in_array($data['amount_type'], ['total', 'prorated', 'input'])) {
				$refund->setError('amount_type', __('Select a refund amount option.'));
			} else {
				if (!empty($data['registrations'])) {
					$registration_ids = $data['registrations'];
					unset($data['registrations']);

					if (Configure::read('registration.online_payments')) {
						$payment_obj = $this->moduleRegistry->load('Payment:' . Configure::read('payment.payment_implementation'));
					} else {
						$payment_obj = null;
					}

					$registrations = $this->Events->Registrations->find()
						->contain([
							'People',
							'Responses',
							'Payments' => [
								'queryBuilder' => function (Query $q) {
									return $q->order(['Payments.created']);
								},
							],
						])
						->where(['Registrations.id IN' => array_keys($registration_ids)]);

					$failed = [];
					foreach ($registrations as $registration) {
						if (!$this->Events->Registrations->refund($event, $registration, $data, $payment_obj)) {
							$failed[$registration->id] = $registration->person->full_name;
						}
					}

					if (!empty($failed)) {
						$this->Flash->refunds_failed(null, ['params' => compact('failed')]);
					} else if ($data['payment_type'] == 'Credit') {
						$this->Flash->success(__('The credits have been saved.'));
					} else {
						$this->Flash->success(__('The refunds have been saved.'));
					}
				} else {
					$this->Flash->info(__('You didn\'t select any registrations to refund.'));
				}
			}
		}

		$query = $this->Events->Registrations->find()
			->contain(['People', 'Payments'])
			->where(['Registrations.event_id' => $id, 'Registrations.payment IN' => Configure::read('registration_some_paid')])
			->order(['Registrations.price_id', 'Registrations.created', 'People.last_name', 'People.first_name']);
		if ($price_id) {
			$query->andWhere(['Registrations.price_id' => $price_id]);
		}

		$this->set(compact('id', 'price_id', 'event', 'refund'));
		$this->set('registrations', $this->paginate($query));
	}

}
