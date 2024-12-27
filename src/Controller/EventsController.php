<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use App\Exception\PaymentException;
use App\Model\Entity\Event;
use App\Model\Entity\EventsConnection;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

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
	protected function _noAuthenticationActions(): array {
		if (!Configure::read('feature.registration')) {
			return [];
		}

		return ['index', 'view', 'wizard'];
	}

	// TODO: Eliminate this if we can find a way around black-holing caused by Ajax field adds
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
	public function index(?string $slug = null) {
		$conditions = [
			'Events.open <' => FrozenTime::now()->addDays(30),
			'Events.close >' => FrozenTime::now(),
		];

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
						return $q
							->where(['Prices.close >=' => FrozenTime::now()])
							->order(['Prices.open', 'Prices.close', 'Prices.id']);
					}
				],
				'Divisions' => ['Leagues' => ['Categories'], 'Days']
			]);

		if ($slug) {
			$category = $this->Events->Divisions->Leagues->Categories->findBySlug($slug)->first();
			if (!$category) {
				$this->Flash->info(__('Invalid category.'));
				return $this->redirect(['action' => 'index']);
			}
			$this->set(compact('category'));

			$leagues = $this->Events->Divisions->Leagues->find()
				->contain(['Categories'])
				->where(['OR' => [
					'Leagues.is_open' => true,
					'Leagues.open >' => FrozenDate::now(),
				]])
				->matching('Categories', function (Query $q) use ($slug) {
					return $q->where(['Categories.slug' => $slug]);
				})
				->extract('id')
				->toArray();
			if (empty($leagues)) {
				$this->Flash->info(__('No active or upcoming leagues in this category.'));
				return $this->redirect(['action' => 'index']);
			}

			$events = $events
				->matching('Divisions.Leagues', function (Query $q) use ($leagues) {
					return $q->where(['Leagues.id IN' => $leagues]);
				});
		}

		$events = $events->toArray();
		if (empty($events)) {
			$this->Flash->info(__('There are no events currently available for registration. Please check back periodically for updates.'));
			return $this->redirect('/');
		}

		$this->populateLocations($events);

		$this->set(compact('affiliates', 'events'));
	}

	/**
	 * Index method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function admin() {
		$this->Authorization->authorize($this);
		$year = $this->getRequest()->getQuery('year');
		if ($year) {
			$conditions = ['OR' => [
				[
					'Events.open >' => $year . '-01-01',
					'Events.open <' => (string) ($year + 1) . '-01-01',
				],
				[
					'Events.close >' => $year . '-01-01',
					'Events.close <' => (string) ($year + 1) . '-01-01',
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

		$affiliates = $this->Authentication->applicableAffiliateIDs();
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
				'Divisions' => ['Leagues' => ['Categories'], 'Days']
			]);

		$years = $this->Events->find()
			->enableHydration(false)
			->select(['year' => 'DISTINCT YEAR(Events.open)'])
			->where([
				'YEAR(Events.open) !=' => 0,
				'Events.affiliate_id IN' => $affiliates,
			])
			->order(['year'])
			->all()
			->extract('year')
			->toArray();

		$events = $events->toArray();
		if (empty($events)) {
			$this->Flash->info(__('No matching events.'));
			return $this->redirect('/');
		}

		$this->set(compact('affiliates', 'events', 'year', 'years'));
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
		$affiliates = $this->Authentication->applicableAffiliateIDs();

		$conditions = [
			'Events.open <' => FrozenTime::now()->addDays(30),
			'Events.close >' => FrozenTime::now(),
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

		if ($step) {
			// TODO: Improve this structure
			$step_map = [
				'membership' => 1,
				'league_team' => 2,
				'league_individual' => 3,
				'event_team' => 4,
				'event_individual' => 5,
				'clinic' => 6,
				'social_event' => 7,
				'league_youth' => 8,
			];
			$conditions['Events.event_type_id'] = $step_map[$step];
		}

		$events = $this->Events->find()
			->contain([
				'EventTypes',
				'Affiliates',
				'Prices' => [
					'queryBuilder' => function (Query $q) {
						return $q
							->where(['Prices.close >=' => FrozenTime::now()])
							->order(['Prices.open', 'Prices.close', 'Prices.id']);
					}
				],
				'Divisions' => ['Leagues' => ['Categories'], 'Days'],
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

		if (empty($events)) {
			if ($step) {
				$this->Flash->info(__('There are no events of this type currently available for registration. Please check back periodically for updates.'));
			} else {
				$this->Flash->info(__('There are no events currently available for registration. Please check back periodically for updates.'));
			}
			return $this->redirect('/');
		}

		$this->populateLocations($events);

		$this->set(compact('events', 'types', 'affiliates', 'step'));
	}

	/**
	 * @param Event[] $events
	 */
	private function populateLocations(array $events) {
		foreach ($events as $event) {
			if ($event->division) {
				$locations = Cache::remember("division_{$event->division_id}_locations", function() use ($event) {
					$availability_table = TableRegistry::getTableLocator()->get('DivisionsGameslots');
					$facilities = $availability_table->find()
						->contain(['GameSlots' => ['Fields' => ['Facilities']]])
						->where(['DivisionsGameslots.division_id' => $event->division_id])
						->distinct('GameSlots.field_id');
					return collection($facilities)->combine('game_slot.field.facility_id', 'game_slot.field.facility.name')->toArray();
				}, 'long_term');

				// The selectors on this page are only for *single location* events
				if (count($locations) === 1) {
					$event->location = array_shift($locations);
				}
			}
		}
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$this->Events->Registrations->expireReservations();

		$id = $this->getRequest()->getQuery('event');
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
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
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
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$this->Authorization->authorize($this);
		$event = $this->Events->newEmptyEntity();

		if ($this->getRequest()->is('post')) {
			// Validation requires this information
			if (!empty($this->getRequest()->getData('event_type_id'))) {
				$type = $this->Events->EventTypes->field('type', ['EventTypes.id' => $this->getRequest()->getData('event_type_id')]);
			} else {
				$type = 'default';
			}

			$event = $this->Events->patchEntity($event, $this->getRequest()->getData(), ['validate' => $type]);
			if ($this->Events->save($event, ['prices' => $event->prices])) {
				$this->Flash->success(__('The event has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->warning(__('The event could not be saved. Please correct the errors below and try again.'));
				$this->Configuration->loadAffiliate($event->affiliate_id);
			}
		} else if ($this->getRequest()->getQuery('event')) {
			// To clone an event, read the old one and remove the id
			try {
				$event = $this->Events->get($this->getRequest()->getQuery('event'), [
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
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
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
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('event');
		try {
			$event = $this->Events->find('translations')
				->contain([
					'EventTypes',
					'Prices' => [
						'queryBuilder' => function ($q) {
							return $q->find('translations')->order(['Prices.open', 'Prices.close', 'Prices.id']);
						},
					],
					'Divisions',
				])
				->where(['Events.id' => $id])
				->firstOrFail();
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($event);
		$this->Configuration->loadAffiliate($event->affiliate_id);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			// Validation requires this information
			if (!empty($this->getRequest()->getData('event_type_id'))) {
				$type = $this->Events->EventTypes->field('type', ['EventTypes.id' => $this->getRequest()->getData('event_type_id')]);
			} else {
				$type = 'default';
			}

			$event = $this->Events->patchEntity($event, $this->getRequest()->getData(), ['validate' => $type]);
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

		$this->getRequest()->allowMethod('ajax');

		$type = $this->Events->EventTypes->field('type', ['EventTypes.id' => $this->getRequest()->getData('event_type_id')]);
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

		$this->getRequest()->allowMethod('ajax');
		$event = $this->Events->newEmptyEntity();
		$this->set(compact('event'));
	}

	/**
	 * Delete method
	 *
	 * @return void|\Cake\Http\Response Redirects to index.
	 */
	public function delete() {
		$this->getRequest()->allowMethod(['post', 'delete']);

		$id = $this->getRequest()->getQuery('event');
		try {
			$event = $this->Events->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
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
		} else if ($event->getError('delete')) {
			$this->Flash->warning(current($event->getError('delete')));
		} else {
			$this->Flash->warning(__('The event could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'index']);
	}

	public function connections() {
		$id = $this->getRequest()->getQuery('event');
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
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($event, 'edit');
		$this->Configuration->loadAffiliate($event->affiliate_id);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			// Alternates always go both ways
			$data = $this->getRequest()->getData();
			$data['alternate_to'] = $data['alternate'];
			$event = $this->Events->patchEntity($event, $data);

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
				return $this->redirect(['action' => 'view', '?' => ['event' => $id]]);
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
			->all()
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
	 * @return void|\Cake\Http\Response
	 */
	public function refund() {
		$this->paginate['order'] = ['Registrations.payment' => 'DESC', 'Registrations.created' => 'DESC'];
		$this->paginate['limit'] = 100;
		$id = $this->getRequest()->getQuery('event');
		$price_id = $this->getRequest()->getQuery('price');

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
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['action' => 'index']);
		}

		$this->Authorization->authorize($event, 'refund');
		$this->Configuration->loadAffiliate($event->affiliate_id);
		$event->prices = collection($event->prices)->indexBy('id')->toArray();

		$refund = $this->Events->Registrations->Payments->newEmptyEntity();

		if ($this->getRequest()->is('post')) {
			$data = $this->getRequest()->getData();
			$refund = $this->Events->Registrations->Payments->patchEntity($refund, $data);

			if (!in_array($data['amount_type'], ['total', 'prorated', 'input'])) {
				$refund->setError('amount_type', __('Select a refund amount option.'));
			} else {
				if (!empty($data['registrations'])) {
					$registration_ids = $data['registrations'];
					unset($data['registrations']);

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
						try {
							// @todo: A service class would be cleaner
							$this->Events->Registrations->refund($this->getRequest(), $event, $registration, $data);
						} catch (PaymentException $ex) {
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
