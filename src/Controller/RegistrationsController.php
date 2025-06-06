<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use App\Core\UserCache;
use App\Model\Entity\Event;
use App\Model\Entity\Payment;
use App\Model\Entity\Registration;
use Authorization\Exception\ForbiddenException;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\Number;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\Validation\Validator;
use App\Model\Entity\Response;
use App\Module\EventType as EventTypeBase;

/**
 * Registrations Controller
 *
 * @property \App\Model\Table\RegistrationsTable $Registrations
 */
class RegistrationsController extends AppController {

	/**
	 * Full list method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function full_list() {
		$this->paginate['order'] = ['Registrations.payment' => 'DESC', 'Registrations.created' => 'DESC'];
		$id = $this->getRequest()->getQuery('event');
		try {
			$event = $this->Registrations->Events->get($id, [
				'contain' => [
					'EventTypes',
					'Questionnaires' => [
						'Questions' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['Questions.active' => true, 'Questions.anonymous' => false]);
							},
							'Answers',
						],
					],
					'Prices',
					'Divisions' => ['Leagues'],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['controller' => 'Events', 'action' => 'index']);
		}
		$event->prices = collection($event->prices)->indexBy('id')->toArray();

		$this->Authorization->authorize($event);
		$this->Configuration->loadAffiliate($event->affiliate_id);

		$event_obj = $this->moduleRegistry->load("EventType:{$event->event_type->type}");
		$event->mergeAutoQuestions($event_obj, null, true);

		$query = $this->Registrations->find()
			->contain(['People', 'Payments'])
			->where(['Registrations.event_id' => $id]);

		if ($this->getRequest()->is('csv')) {
			$query->contain([
				'People' => [
					Configure::read('Security.authModel'),
					'UserGroups',
					'Related' => [Configure::read('Security.authModel')],
				],
				'Payments' => ['RegistrationAudits'],
				'Responses',
			]);
			if (!empty($event->division_id) && !empty($event->division->league->sport)) {
				$sport = $event->division->league->sport;
			} else {
				$sports = Configure::read('options.sport');
				if (count($sports) == 1) {
					$sport = reset($sports);
				}
			}
			if (isset($sport)) {
				$query->contain(['People' => [
					'Skills' => [
						'queryBuilder' => function (Query $q) use ($sport) {
							return $q->where(['Skills.sport' => $sport]);
						},
					],
				]]);
			}
			$this->set('registrations', $query);
			$this->setResponse($this->getResponse()->withDownload("Registrations - {$event->name}.csv"));
		} else {
			$this->set('registrations', $this->paginate($query));
		}

		$this->set(compact('event'));
	}

	public function summary() {
		$id = $this->getRequest()->getQuery('event');
		try {
			$event = $this->Registrations->Events->get($id, [
				'contain' => [
					'EventTypes',
					'Questionnaires' => [
						'Questions' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['Questions.active' => true, 'Questions.anonymous' => false]);
							},
							'Answers',
						],
					],
					'Divisions' => ['Leagues'],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['controller' => 'Events', 'action' => 'index']);
		}

		$this->Authorization->authorize($event);
		$this->Configuration->loadAffiliate($event->affiliate_id);

		$event_obj = $this->moduleRegistry->load("EventType:{$event->event_type->type}");
		$event->mergeAutoQuestions($event_obj, null, true);

		// If the event is all men or all women, there's no point in including this
		if ($event->open_cap != 0 && $event->women_cap != 0) {
			$gender_split = $this->Registrations->find()
				->contain('People')
				->select(['count' => 'COUNT(Registrations.id)', Configure::read('gender.column') => 'People.' . Configure::read('gender.column')])
				->where([
					'Registrations.event_id' => $id,
					'Registrations.payment !=' => 'Cancelled',
				])
				->group(['People.' . Configure::read('gender.column')])
				->order(['People.' . Configure::read('gender.column') => Configure::read('gender.order')])
				->toArray();
			$this->set(compact('gender_split'));

			// We need to include a gender breakdown of the payment statuses if both
			// genders are allowed to register.
			$payment = $this->Registrations->find()
				->contain('People')
				->select(['count' => 'COUNT(Registrations.payment)', Configure::read('gender.column') => 'People.' . Configure::read('gender.column'), 'payment' => 'Registrations.payment'])
				->where([
					'Registrations.event_id' => $id,
				])
				->group(['Registrations.payment', 'People.' . Configure::read('gender.column')])
				->order(['Registrations.payment', 'People.' . Configure::read('gender.column') => Configure::read('gender.order')])
				->toArray();
		} else {
			$payment = $this->Registrations->find()
				->select(['count' => 'COUNT(Registrations.payment)', 'payment' => 'Registrations.payment'])
				->where([
					'Registrations.event_id' => $id,
				])
				->group(['Registrations.payment'])
				->order(['Registrations.payment'])
				->toArray();
		}

		$responses = $this->Registrations->Responses->find()
			->select(['count' => 'COUNT(answer_id)', 'question_id' => 'question_id', 'answer_id' => 'answer_id'])
			->where([
				'event_id' => $id,
				'answer_id IS NOT' => null,
			])
			->group(['question_id', 'answer_id'])
			->order(['question_id'])
			->toArray();

		$this->set(compact('event', 'payment', 'responses'));
	}

	public function statistics() {
		$this->Authorization->authorize($this);
		$year = $this->getRequest()->getQuery('year');
		if ($year === null) {
			$year = FrozenTime::now()->year;
		}

		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$this->set(compact('affiliates'));

		$query = $this->Registrations->find();

		$events = $this->Registrations->Events->find()
			->select(['registration_count' => $query->func()->count('Registrations.id')])
			->select($this->Registrations->Events)
			->select($this->Registrations->Events->EventTypes)
			->select($this->Registrations->Events->Affiliates)
			->select($this->Registrations->Events->Divisions)
			->select($this->Registrations->Events->Divisions->Leagues)
			->matching('Affiliates', function (Query $q) use ($affiliates) {
				return $q->where(['Affiliates.id IN' => $affiliates]);
			})
			->leftJoin(['Registrations' => 'registrations'], ['Registrations.event_id = Events.id'])
			->contain(['EventTypes', 'Divisions' => ['Leagues', 'Days']])
			->where([
				'Registrations.payment !=' => 'Cancelled',
				'OR' => [
					// TODO: Use a query object here
					'YEAR(Events.open)' => $year,
					'YEAR(Events.close)' => $year,
				],
			])
			->group(['Events.id'])
			->order(['Affiliates.name', 'Events.event_type_id', 'Events.open' => 'DESC', 'Events.close' => 'DESC', 'Events.id'])
			->toArray();

		$years = $this->Registrations->Events->find()
			->enableHydration(false)
			->select(['year' => 'DISTINCT YEAR(Events.open)'])
			->where([
				'YEAR(Events.open) !=' => 0,
				'Events.affiliate_id IN' => $affiliates,
			])
			->order(['year'])
			->toArray();

		$this->set(compact('events', 'years'));
	}

	public function report() {
		$this->Authorization->authorize($this);
		if ($this->getRequest()->is('post')) {
			$start_date = $this->getRequest()->getData('start_date');
			$end_date = $this->getRequest()->getData('end_date');
		} else {
			$start_date = $this->getRequest()->getQuery('start_date');
			$end_date = $this->getRequest()->getQuery('end_date');
			if (!$start_date || !$end_date) {
				// Just return, which will present the user with a date selection
				return;
			}
		}

		if ($start_date > $end_date) {
			$this->Flash->info(__('Start date must be before end date!'));
			return;
		}

		$affiliate = $this->getRequest()->getQuery('affiliate');
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);

		$query = $this->Registrations->find()
			->contain([
				'Events' => ['EventTypes', 'Affiliates'],
				'Prices',
				'Payments' => ['RegistrationAudits'],
				'People',
			])
			->where([
				function (QueryExpression $exp) use ($start_date, $end_date) {
					return $exp->between('Registrations.created', $start_date, "{$end_date} 23:59:59", 'date');
				},
				'Events.affiliate_id IN' => $affiliates,
			]);

		if ($this->getRequest()->is('csv')) {
			$query
				->contain([
					'People' => [
						Configure::read('Security.authModel'),
						'Related' => [Configure::read('Security.authModel')],
					],
				])
				->order(['Events.affiliate_id', 'Registrations.payment' => 'DESC', 'Registrations.created']);
			$this->set('registrations', $query);
			$this->setResponse($this->getResponse()->withDownload("Registrations $start_date to $end_date.csv"));
		} else {
			$this->paginate = [
				'order' => ['Events.affiliate_id', 'Registrations.payment' => 'DESC', 'Registrations.created' => 'DESC'],
			];
			$this->set('registrations', $this->paginate($query));
		}

		$this->set(compact('affiliates', 'affiliate', 'start_date', 'end_date'));
	}

	public function TODOLATER_accounting() {
		$this->Authorization->authorize($this);
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('registration');
		try {
			$registration = $this->Registrations->get($id, [
				'contain' => [
					'People',
					'Events' => [
						'EventTypes',
						'Questionnaires' => [
							'Questions' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['Questions.active' => true, 'Questions.anonymous' => false]);
								},
								'Answers',
							],
						],
						'Divisions' => ['Leagues'],
					],
					'Responses',
					'Payments' => [
						'queryBuilder' => function (Query $q) {
							return $q->order(['Payments.id']);
						},
						'RegistrationAudits',
					],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid registration.'));
			return $this->redirect(['controller' => 'Events', 'action' => 'index']);
		}

		try {
			$this->Authorization->authorize($registration);
		} catch (ForbiddenException $ex) {
			// Try the invoice; if authorized, redirect there. Otherwise, throw the original exception.
			try {
				$this->Authorization->authorize($registration, 'invoice');
				return $this->redirect(['action' => 'invoice', '?' => ['registration' => $id]]);
			} catch (ForbiddenException $ex2) {
				throw $ex;
			}
		}
		$this->Configuration->loadAffiliate($registration->event->affiliate_id);

		$event_obj = $this->moduleRegistry->load("EventType:{$registration->event->event_type->type}");
		$registration->event->mergeAutoQuestions($event_obj, $registration->person->id, true);

		$this->set(compact('registration'));
	}

	/**
	 * Register method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function register() {
		$this->Registrations->expireReservations();

		$id = $this->getRequest()->getQuery('event');
		try {
			$event = $this->Registrations->Events->get($id, [
				'contain' => [
					'EventTypes',
					'Prices' => [
						'queryBuilder' => function (Query $q) {
							return $q->where(['Prices.open', 'Prices.close', 'Prices.id']);
						},
					],
					'Questionnaires' => [
						'Questions' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['active' => true]);
							},
							'Answers' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['active' => true]);
								},
							],
						],
					],
					'Divisions' => ['Leagues'],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['controller' => 'Events', 'action' => 'wizard']);
		}

		// TODO: Eliminate the 'option' option once all old links are gone
		$price_id = $this->getRequest()->getQuery('variant') ?: $this->getRequest()->getQuery('option');
		if (empty($price_id) && $this->getRequest()->is(['patch', 'post', 'put'])) {
			$price_id = $this->getRequest()->getData('price_id');
		}
		if (!empty($price_id)) {
			$price = collection($event->prices)->firstMatch(['id' => $price_id]);
			if (empty($price)) {
				$this->Flash->info(__('Invalid price point.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'wizard']);
			}
		} else if (count($event->prices) == 1) {
			$price = $event->prices[0];
		} else {
			$price = null;
		}

		$context = new ContextResource($event, ['price' => $price, 'waiting' => $this->getRequest()->getQuery('waiting'), 'all_rules' => true]);
		$this->Authorization->authorize($context);
		$this->Configuration->loadAffiliate($event->affiliate_id);

		$event_obj = $this->moduleRegistry->load("EventType:{$event->event_type->type}");
		$event->mergeAutoQuestions($event_obj, $this->UserCache->currentId());

		$data = $this->getRequest()->getData();
		$registration = $this->Registrations->newEmptyEntity();
		$force_save = false;
		if (isset($price)) {
			if (empty($event->questionnaire->questions) && !in_array($price->online_payment_option, [ONLINE_MINIMUM_DEPOSIT, ONLINE_SPECIFIC_DEPOSIT, ONLINE_NO_MINIMUM])) {
				// The event has no questionnaire, and no price options; save trivial registration data and proceed
				$force_save = true;
				if (!$price->allow_deposit) {
					$data['payment_amount'] = $price->total;
				} else {
					$data['payment_amount'] = $price->minimum_deposit;
				}
				$data['event_id'] = $id;
			}

			// We have a price selected, set it in the entity so the view reflects it
			$registration->price = $price;
			$registration->price_id = $price->id;
		}

		// Data was posted, save it and proceed
		if ($this->getRequest()->is(['patch', 'post', 'put']) || $force_save) {
			$responseValidator = $this->Registrations->Responses->validationDefault(new Validator());
			if (!empty($event->questionnaire->questions)) {
				$responseValidator = $event->questionnaire->addResponseValidation($responseValidator, $event_obj, $data['responses'], $event);
			}

			$registration = $this->Registrations->patchEntity($registration, $data, ['associated' => [
				'Responses' => ['validate' => $responseValidator],
			]]);
			$this->_reindexResponses($registration, $event);

			if (!$this->Registrations->save($registration, compact('event', 'event_obj'))) {
				$this->Flash->warning(__('The registration could not be saved. Please correct the errors below and try again.'));
			} else if ($registration->payment == 'Waiting') {
				$this->Flash->success(__('You have been added to the waiting list for this event.'));
				return $this->redirect(['controller' => 'Events', 'action' => 'wizard']);
			} else if ($price->total == 0) {
				if (empty($registration->responses)) {
					$this->Flash->success(__('Your registration for this event has been confirmed.'));
				} else {
					$this->Flash->success(__('Your preferences have been saved and your registration confirmed.'));
				}
				return $this->redirect(['controller' => 'Events', 'action' => 'wizard']);
			} else {
				if (empty($registration->responses)) {
					$this->Flash->success(__('Your registration for this event has been saved. Please complete your payment to confirm your registration.'));
				} else {
					$this->Flash->success(__('Your preferences for this registration have been saved. Please complete your payment to confirm your registration.'));
				}
				return $this->redirect(['action' => 'checkout']);
			}
		}

		$this->set(compact('id', 'event', 'price_id', 'event_obj', 'registration'));
		$this->set('waiting', $context->waiting);
	}

	public function register_payment_fields() {
		$this->Authorization->authorize($this);
		$this->getRequest()->allowMethod('ajax');

		$price_id = $this->getRequest()->getData('price_id');
		if (!empty($price_id)) {
			$contain = ['Events' => ['EventTypes']];
			$registration = $this->getRequest()->getQuery('registration_id');
			if ($registration) {
				$contain['Events']['Registrations'] = [
					'queryBuilder' => function (Query $q) use ($registration) {
						return $q->where(['Registrations.id' => $registration]);
					},
				];
			}

			try {
				$price = $this->Registrations->Prices->get($price_id, compact('contain'));
				$for_edit = $this->getRequest()->getQuery('for_edit');

				// This authorization call is just to set the message, if any, in the price
				$this->Authorization->can(new ContextResource($price->event, [
					'person_id' => $for_edit ? $price->event->registrations[0]->person_id : $this->UserCache->currentId(),
					'price' => $price,
					'for_edit' => $for_edit ? $price->event->registrations[0] : false,
					'waiting' => $this->getRequest()->getQuery('waiting'),
					'ignore_date' => $for_edit,
				]), 'register');

				$this->set(compact('price', 'for_edit'));
			} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			}
		}
	}

	public function redeem() {
		$id = $this->getRequest()->getQuery('registration');
		try {
			$registration = $this->Registrations->get($id, [
				'contain' => [
					'People' => [
						'Credits' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['amount != amount_used']);
							},
						],
					],
					'Events' => [
						'EventTypes',
						'Prices',
						'Divisions' => ['Leagues'],
					],
					'Prices',
					'Responses',
					'Payments',
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid registration.'));
			return $this->redirect(['controller' => 'Events', 'action' => 'wizard']);
		}

		$registration->person->credits = collection($registration->person->credits)->match(['affiliate_id' => $registration->event->affiliate_id])->toArray();

		$this->Authorization->authorize(new ContextResource($registration, [
			'person' => $registration->person,
			'price' => $registration->price,
			'event' => $registration->event,
			'prices' => $registration->event->prices,
		]));
		$this->Configuration->loadAffiliate($registration->event->affiliate_id);

		$credit = $this->getRequest()->getQuery('credit');
		if ($credit) {
			$credit = collection($registration->person->credits)->firstMatch(['id' => $credit]);
			if (!$credit) {
				$this->Flash->info(__('Invalid credit.'));
				return $this->redirect(['action' => 'checkout']);
			}
		}

		$payment = $this->Registrations->Payments->newEmptyEntity();

		if ($credit) {
			$payment = $this->Registrations->Payments->patchEntity($payment, [
				'payment_type' => ($credit->balance >= $registration->balance ? ($registration->total_payment == 0 ? 'Full' : 'Remaining Balance') : 'Installment'),
				'payment_amount' => min($credit->balance, $registration->balance),
				'payment_method' => 'Credit Redeemed',
				'notes' => "Applied credit #{$credit->id}",
			]);
			$registration->payments[] = $payment;
			$registration->setDirty('payments', true);

			if (!empty($credit->notes)) {
				$credit->notes .= "\n";
			}
			$credit->notes .= __('{0} applied to registration #{1}: {2}',
				$credit->amount_used == 0 && $payment->payment_amount == $credit->amount ? __('Credit') : Number::currency($payment->payment_amount),
				$registration->id, $registration->event->name);
			$credit->amount_used += $payment->payment_amount;

			// We don't actually want to update the "modified" column in the people table here, but we do need to save the credit
			if ($this->Registrations->People->hasBehavior('Timestamp')) {
				$this->Registrations->People->removeBehavior('Timestamp');
			}

			if ($this->Registrations->getConnection()->transactional(function () use ($registration, $credit) {
				return $this->Registrations->save($registration, ['registration' => $registration, 'event' => $registration->event]) &&
					$this->Registrations->People->Credits->save($credit);
			})) {
				$this->Flash->success(__('The credit has been applied to the chosen registration.'));
				$this->UserCache->clear('Credits', $registration->person_id);
			} else {
				$this->Flash->info(__('There was an error redeeming the credit.'));
			}
			return $this->redirect(['action' => 'checkout']);
		}

		$this->set(compact('registration'));
	}

	public function checkout() {
		$this->Registrations->expireReservations();
		$this->Authorization->authorize($this);

		$registrations = $this->Registrations->find()
			->contain([
				'Events' => ['EventTypes', 'Prices'],
				'Prices',
				'Payments',
				'Responses',
			])
			->where([
				'Registrations.person_id' => $this->UserCache->currentId(),
				'Registrations.payment IN' => Configure::read('registration_unpaid'),
			])
			->toArray();

		// If there are no unpaid registrations, then we probably got here by
		// unregistering from our last thing. In that case, we don't want to
		// disturb the flash message, just go back to the event list.
		if (empty($registrations)) {
			return $this->redirect(['controller' => 'Events', 'action' => 'wizard']);
		}

		$person = $this->Registrations->People->get($this->UserCache->currentId(), [
			'contain' => [
				Configure::read('Security.authModel'),
				'Credits' => [
					'queryBuilder' => function (Query $q) {
						return $q->where(['Credits.amount_used != Credits.amount']);
					},
				],
				// TODOLATER: Include relatives, and allow us to pay for them too; see also All/splash.php
				'Related' => [Configure::read('Security.authModel')],
			]
		]);

		$other = [];
		$affiliate = $this->getRequest()->getQuery('affiliate');
		foreach ($registrations as $key => $registration) {
			// Check that we're still allowed to pay for this
			if (!$registration->price->allow_late_payment && $registration->price->close->isPast()) {
				$other_prices = collection($registration->event->prices)->filter(function ($price) {
					return $price->close->isFuture();
				})->toArray();
				$prereg = collection($this->UserCache->read('Preregistrations'))->firstMatch(['event_id' => $registration->event_id]);
				if (!empty($other_prices) || empty($prereg)) {
					$other[] = ['registration' => $registration, 'reason' => __('Payment deadline has passed'), 'change_price' => !empty($other_prices)];
					unset($registrations[$key]);
					continue;
				}
			}

			// Find the registration cap and how many are already registered.
			$cap = $registration->event->cap($person->roster_designation);
			if ($cap != CAP_UNLIMITED) {
				$paid = $registration->event->count($person->roster_designation, ['Registrations.id !=' => $registration->id]);
				if ($cap <= $paid) {
					$other[] = ['registration' => $registration, 'reason' => __('You are on the waiting list')];
					unset($registrations[$key]);
					continue;
				}
			}

			// Don't allow the user to pay for things from multiple affiliates at the same time
			if (!$affiliate) {
				$affiliate = $registration->event->affiliate_id;
			} else if ($affiliate != $registration->event->affiliate_id) {
				$other[] = ['registration' => $registration, 'reason' => __('In a different affiliate')];
				unset($registrations[$key]);
				continue;
			}

			// Don't allow further payment on "deposit only" items
			if ($registration->price->deposit_only && in_array($registration->payment, Configure::read('registration_some_paid'))) {
				$other[] = ['registration' => $registration, 'reason' => __('Deposit paid; balance must be paid off-line')];
				unset($registrations[$key]);
				continue;
			}

			// Don't allow any payment on $0 "deposit only" items
			if ($registration->price->online_payment_option == ONLINE_NO_PAYMENT) {
				$other[] = ['registration' => $registration, 'reason' => __('Registration for this is open, but online payments are not allowed')];
				unset($registrations[$key]);
				continue;
			}

			// Set the description for the invoice
			$event_obj = $this->moduleRegistry->load("EventType:{$registration->event->event_type->type}");
			$registration->event->payment_desc = $event_obj->longDescription($registration);
		}

		$this->Configuration->loadAffiliate($affiliate);
		$person->credits = collection($person->credits)->match(['affiliate_id' => $affiliate])->toArray();

		$plugin_elements = new \ArrayObject();
		$event = new \Cake\Event\Event('Plugin.checkout', $this, [$plugin_elements]);
		$this->getEventManager()->dispatch($event);

		// Forms will use $registrations[0], but that may have been unset above.
		$registrations = array_values($registrations);
		$this->set(compact('registrations', 'other', 'person', 'plugin_elements'));
		$this->set(['is_test' => $this->isTest()]);
	}

	public function unregister() {
		$this->getRequest()->allowMethod(['get', 'post', 'delete']);

		try {
			$registration = $this->Registrations->get($this->getRequest()->getQuery('registration'), [
				'contain' => [
					'Events' => ['EventTypes'],
					'Prices',
					'Responses',
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid registration.'));
			return $this->redirect(['action' => 'checkout']);
		}

		$this->Authorization->authorize($registration);
		$this->Configuration->loadAffiliate($registration->event->affiliate_id);

		if ($this->Registrations->delete($registration)) {
			$this->Flash->success(__('Successfully unregistered from this event.'));
		} else {
			$this->Flash->warning(__('Failed to unregister from this event!'));
		}

		if ($this->Authentication->getIdentity()->isMe($registration)) {
			return $this->redirect(['action' => 'checkout']);
		} else {
			return $this->redirect('/');
		}
	}

	public function add_payment() {
		$id = $this->getRequest()->getQuery('registration');
		try {
			$registration = $this->Registrations->get($id, [
				'contain' => [
					'People' => [
						'Credits' => [
							'queryBuilder' => function (Query $q) {
								return $q->where(['amount != amount_used']);
							},
						],
					],
					'Events' => [
						'EventTypes',
						'Divisions' => ['Leagues'],
					],
					'Responses',
					'Payments',
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid registration.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($registration);
		$this->Configuration->loadAffiliate($registration->event->affiliate_id);
		$payment = $this->Registrations->Payments->newEmptyEntity();

		$this->set(compact('registration', 'payment'));

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$data = $this->getRequest()->getData();

			// Handle credit redemption
			if (array_key_exists('credit_id', $data)) {
				$credit = collection($registration->person->credits)->firstMatch(['id' => $data['credit_id']]);
				if (!$credit) {
					$this->Flash->info(__('Invalid credit.'));
					return;
				}

				$data['payment_amount'] = min($data['payment_amount'], $registration->balance, $credit->balance);
				$data['notes'] = __('Applied {0} from credit #{1}', Number::currency($data['payment_amount']), $credit->id);

				$credit->amount_used += $data['payment_amount'];
				if (!empty($credit->notes)) {
					$credit->notes .= "\n";
				}
				$credit->notes .= __('{0} applied to registration #{1}: {2}',
					$data['payment_amount'] == $credit->amount ? __('Credit') : Number::currency($data['payment_amount']),
					$registration->id, $registration->event->name);

				// We don't actually want to update the "modified" column in the people table here, but we do need to save the credit
				if ($this->Registrations->People->hasBehavior('Timestamp')) {
					$this->Registrations->People->removeBehavior('Timestamp');
				}
				$registration->setDirty('person', true);
				$registration->person->setDirty('credits', true);
			}

			// The registration is also passed as an option, so that the payment marshaller has easy access to it
			$payment = $this->Registrations->Payments->patchEntity($payment, $data, ['validate' => 'payment', 'registration' => $registration]);
			$registration->payments[] = $payment;
			$registration->setDirty('payments', true);

			// The registration is also passed as an option, so that the payment rules have easy access to it
			if ($this->Registrations->save($registration, ['registration' => $registration, 'event' => $registration->event])) {
				$this->Flash->success(__('The payment has been saved.'));
				return $this->redirect(['action' => 'view', '?' => ['registration' => $registration->id]]);
			} else {
				$this->Flash->warning(__('The payment could not be saved. Please correct the errors below and try again.'));
			}
		}
	}

	public function refund_payment() {
		$id = $this->getRequest()->getQuery('payment');
		try {
			$registration_id = $this->Registrations->Payments->field('registration_id', ['Payments.id' => $id]);
			/** @var Registration $registration */
			$registration = $this->Registrations->get($registration_id, [
				'contain' => [
					'People',
					'Events' => [
						'EventTypes',
						'Divisions' => ['Leagues'],
					],
					'Responses',
					'Payments' => [
						'queryBuilder' => function (Query  $q) {
							return $q->order(['Payments.created']);
						},
						'RegistrationAudits',
					],
				]
			]);

			$payment = collection($registration->payments)->firstMatch(compact('id'));
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid payment.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($payment);
		$this->Configuration->loadAffiliate($registration->event->affiliate_id);
		$refund = $this->Registrations->Payments->newEmptyEntity();

		if ($payment->registration_audit_id) {
			$api = $this->Registrations->Payments->RegistrationAudits->getAPI($payment->registration_audit);
			$this->set(compact('api'));
		}

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$data = $this->getRequest()->getData();
			// The registration is also passed as an option, so that the payment marshaller has easy access to it
			/** @var Payment $refund */
			$refund = $this->Registrations->Payments->patchEntity($refund, $data, ['validate' => 'refund', 'registration' => $registration]);
			if ($this->Registrations->refundPayment($this->getRequest(), $registration->event, $registration, $payment, $refund, $data['mark_refunded'], $data['online_refund'] ?? false)) {
				$this->Flash->success(__('The refund has been saved.'));
				return $this->redirect(['action' => 'view', '?' => ['registration' => $registration->id]]);
			}
		}

		$this->set(compact('registration', 'payment', 'refund'));
	}

	/**
	 * Invoice method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function invoice() {
		$id = $this->getRequest()->getQuery('registration');
		try {
			$registration = $this->Registrations->get($id, [
				'contain' => [
					'People',
					'Events',
					'Payments',
					'Prices',
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid registration.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($registration);

		if ($registration->balance) {
			$this->Flash->info(__('This registration is not fully paid for yet.'));
			return $this->redirect('/');
		}

		$this->Configuration->loadAffiliate($registration->event->affiliate_id);

		$this->set(compact('registration'));
	}

	public function credit_payment() {
		$id = $this->getRequest()->getQuery('payment');
		try {
			$registration_id = $this->Registrations->Payments->field('registration_id', ['Payments.id' => $id]);
			/** @var Registration $registration */
			$registration = $this->Registrations->get($registration_id, [
				'contain' => [
					'People' => ['Credits'],
					'Events' => [
						'EventTypes',
						'Divisions' => ['Leagues'],
					],
					'Responses',
					'Payments',
				]
			]);

			$payment = collection($registration->payments)->firstMatch(compact('id'));
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid payment.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($payment);
		$this->Configuration->loadAffiliate($registration->event->affiliate_id);
		$refund = $this->Registrations->Payments->newEmptyEntity();

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$data = $this->getRequest()->getData();
			// The registration is also passed as an option, so that the payment marshaller has easy access to it
			/** @var Payment $refund */
			$refund = $this->Registrations->Payments->patchEntity($refund, $data, ['validate' => 'credit', 'registration' => $registration]);
			if ($this->Registrations->refundPayment($this->getRequest(), $registration->event, $registration, $payment, $refund, $data['mark_refunded'], false, $data['credit_notes'])) {
				$this->Flash->success(__('The credit has been saved.'));
				$this->UserCache->clear('Credits', $registration->person_id);
				return $this->redirect(['action' => 'view', '?' => ['registration' => $registration->id]]);
			}
		}

		$this->set(compact('registration', 'payment', 'refund'));
	}

	/**
	 * Edit method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->getRequest()->getQuery('registration');
		try {
			$registration = $this->Registrations->get($id, [
				'contain' => [
					'People',
					'Events' => [
						'EventTypes',
						'Prices',
						'Questionnaires' => [
							'Questions' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['Questions.active' => true, 'Questions.anonymous' => false]);
								},
								'Answers' => [
									'queryBuilder' => function (Query $q) {
										return $q->where(['Answers.active' => true]);
									},
								],
							],
						],
						'Divisions' => ['Leagues'],
					],
					'Prices',
					'Responses',
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid registration.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($registration);
		$this->Configuration->loadAffiliate($registration->event['affiliate_id']);

		$event_obj = $this->moduleRegistry->load("EventType:{$registration->event->event_type->type}");
		$registration->event->mergeAutoQuestions($event_obj, $registration->person->id);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$this->Authorization->can(new ContextResource($registration->event, ['for_edit' => $registration, 'all_rules' => true]), 'register');
			$responseValidator = $this->Registrations->Responses->validationDefault(new Validator());
			if (!empty($registration->event->questionnaire->questions)) {
				$responseValidator = $registration->event->questionnaire->addResponseValidation($responseValidator, $event_obj, $this->getRequest()->getData('responses'), $registration->event, $registration);
			}

			// We use the "replace" saving strategy for responses, so that unnecessary responses get discarded,
			// but we need to keep a couple of things that the system generates.
			$preserve = EventTypeBase::extractAnswers($registration->responses, [
				'team_id' => TEAM_ID_CREATED,
				'franchise_id' => FRANCHISE_ID_CREATED,
			]);

			$registration = $this->Registrations->patchEntity($registration, $this->getRequest()->getData(), ['associated' => [
				'Responses' => ['validate' => $responseValidator],
			]]);
			$this->_reindexResponses($registration, $registration->event);
			if (!$registration->errors) {
				// TODO: Seems that the marshaller won't update $registration->price, even though
				// $registration->price_id gets set. Because it's a BelongsTo relationship, perhaps?
				// But we need it set correctly in RegistrationsTable::beforeSave. We'll do it manually. :-(
				$registration->price = collection($registration->event->prices)->firstMatch(['id' => $registration->price_id]);
				$registration->setDirty('price', false);

				if (!empty($preserve['team_id'])) {
					$registration->responses[] = new Response([
						'question_id' => TEAM_ID_CREATED,
						'answer_text' => $preserve['team_id'],
					]);
				}
				if (!empty($preserve['franchise_id'])) {
					$registration->responses[] = new Response([
						'question_id' => FRANCHISE_ID_CREATED,
						'answer_text' => $preserve['franchise_id'],
					]);
				}
			}

			if (!$this->Registrations->save($registration, ['event' => $registration->event, 'event_obj' => $event_obj])) {
				$this->Flash->warning(__('The registration could not be saved. Please correct the errors below and try again.'));
			} else if ($this->Authentication->getIdentity()->isMe($registration)) {
				$this->Flash->success(__('Your preferences for this registration have been saved.'));
				return $this->redirect(['action' => 'checkout']);
			} else {
				$this->Flash->success(__('The registration has been saved.'));
				return $this->redirect(['controller' => 'People', 'action' => 'registrations', '?' => ['person' => $registration->person->id]]);
			}
		} else {
			$this->Authorization->can(new ContextResource($registration->event, ['price' => $registration->price, 'for_edit' => $registration, 'all_rules' => true]), 'register');
		}

		$this->_reindexResponses($registration, $registration->event);

		$this->set(compact('registration'));
	}

	public function unpaid() {
		$this->Authorization->authorize($this);
		$affiliates = $this->Authentication->applicableAffiliateIDs(true);
		$registrations = $this->Registrations->find()
			->contain([
				'Events' => ['EventTypes', 'Affiliates'],
				'Prices',
				'People',
				'Payments',
			])
			->where([
				'Registrations.payment IN' => Configure::read('registration_delinquent'),
				'Events.affiliate_id IN' => $affiliates,
			])
			->order(['Events.affiliate_id', 'Registrations.payment', 'Registrations.modified']);
		if ($registrations->count() == 0) {
			$this->Flash->info(__('There are no unpaid registrations.'));
			return $this->redirect('/');
		}

		$this->set(compact('registrations', 'affiliates'));
	}

	public function waiting() {
		$id = $this->getRequest()->getQuery('event');
		try {
			$event = $this->Registrations->Events->get($id, [
				'contain' => [
					'Registrations' => [
						'queryBuilder' => function (Query $q) {
							return $q
								->where(['Registrations.payment' => 'Waiting'])
								->order(['Registrations.created']);
						},
						'People',
						'Prices',
						'Payments',
					],
					'Divisions' => ['Leagues'],
				]
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid event.'));
			return $this->redirect(['controller' => 'Events', 'action' => 'index']);
		}

		$this->Authorization->authorize($event);
		$this->Configuration->loadAffiliate($event->affiliate_id);

		if (empty($event->registrations)) {
			$this->Flash->info(__('There is nobody on the waiting list for this event.'));
			return $this->redirect('/');
		}

		$this->set(compact('event'));
	}

	public static function isTest() {
		$test_config = Configure::read('payment.test_payments');
		switch ($test_config) {
			case TEST_PAYMENTS_EVERYBODY:
				return true;

			case TEST_PAYMENTS_ADMINS:
				// TODO: Better way to do this
				$groups = UserCache::getInstance()->read('UserGroups');
				return collection($groups)->some(function ($group) {
					return in_array($group->id, [GROUP_ADMIN, GROUP_MANAGER]);
				});

			default:
				return false;
		}
	}

	private function _reindexResponses(Registration $registration, Event $event) {
		// The entity will contain a sequentially-numbered array of responses, but we need specific numbers
		// in order for any errors that occur to be correctly reported in the form. :-(
		// TODO: Report to Cake?
		$responses = [];
		foreach ($event->questionnaire->questions as $key => $question) {
			if ($question->type == 'checkbox' && $question->answers && count($question->answers) > 1) {
				foreach ($question->answers as $akey => $answer) {
					$response = collection($registration->responses)->firstMatch(['question_id' => $question->id, 'answer_id' => $answer->id]);
					if ($response) {
						$responses[$key * 100 + $akey] = $response;
					}
				}
			} else {
				$response = collection($registration->responses)->firstMatch(['question_id' => $question->id]);
				if ($response) {
					$responses[$key * 100] = $response;
				}
			}
		}
		$registration->responses = $responses;
	}

}
