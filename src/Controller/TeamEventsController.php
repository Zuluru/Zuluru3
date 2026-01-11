<?php
namespace App\Controller;

use App\Authorization\ContextResource;
use App\Policy\MissingIdentityResult;
use App\Service\Games\AttendanceService;
use Authorization\Exception\ForbiddenException;
use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use App\PasswordHasher\HasherTrait;
use App\Model\Table\GamesTable;
use App\Model\Table\TeamsTable;

/**
 * TeamEvents Controller
 *
 * @property \App\Model\Table\TeamEventsTable $TeamEvents
 */
class TeamEventsController extends AppController {

	use HasherTrait;

	/**
	 * _noAuthenticationActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _noAuthenticationActions(): array {
		// Attendance updates may come from emailed links; people might not be logged in
		return ['attendance_change'];
	}

	/**
	 * View method
	 *
	 * @return void|\Cake\Http\Response
	 */
	public function view() {
		$id = $this->getRequest()->getQuery('event');
		try {
			$team_event = $this->TeamEvents->get($id, [
				'contain' => [
					'Teams' => [
						'People',
						'Divisions' => ['Leagues'],
					],
				],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($team_event);
		$this->Configuration->loadAffiliate($team_event->team->division->league->affiliate_id);

		$include_gender = $this->Authorization->can(new ContextResource($team_event->team, ['division' => $team_event->team->division]), 'display_gender');
		\App\lib\context_usort($team_event->team->people, [TeamsTable::class, 'compareRoster'], ['include_gender' => $include_gender]);

		$attendance = $this->TeamEvents->readAttendance($team_event->team, $id)->attendances;
		$this->set(compact('team_event', 'attendance'));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Http\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$id = $this->getRequest()->getQuery('team');
		try {
			$team = $this->TeamEvents->Teams->get($id, [
				'contain' => ['Divisions' => ['Leagues']],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($team, 'add_event');
		if (!empty($team->division)) {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		}

		$team_event = $this->TeamEvents->newEmptyEntity();

		if ($this->getRequest()->is('post')) {
			$team_event = $this->TeamEvents->patchEntity($team_event, array_merge($this->getRequest()->getData(), ['team_id' => $id, 'dates' => []]));

			// TODO: This entire block could benefit from some refactoring to improve code re-use and error detection
			if (!empty($this->getRequest()->getData('repeat'))) {
				if (!$team_event->getErrors()) {
					if ($this->getRequest()->getData('repeat_type') == 'custom') {
						if (!empty($this->getRequest()->getData('dates'))) {
							if (!$this->TeamEvents->getConnection()->transactional(function () use ($id, $team_event) {
								$team_event['dates'] = $dates = [];
								for ($i = 0; $i < $this->getRequest()->getData('repeat_count'); ++ $i) {
									// Note: We intentionally use a different variable than $team_event,
									// so that the team_event that is sent to the view has original data
									// in it, not the modified date.
									$team_event['dates'][$i] = $this->TeamEvents->newEntity(array_merge($this->getRequest()->getData(), ['date' => $this->getRequest()->getData("dates.$i.date")]));
									$this->TeamEvents->save($team_event['dates'][$i]);
									if (in_array($team_event['dates'][$i]->date, $dates)) {
										$team_event['dates'][$i]->setError('date', __('You cannot select the same date more than once.'));
									} else {
										$dates[] = $team_event['dates'][$i]->date;
									}
								}
								if ($team_event->getErrors()) {
									$this->Flash->warning(__('The team event could not be saved. Please correct the errors below and try again.'));
									return false;
								}
								return true;
							})) {
								$this->set(compact('team_event'));
								$this->render('add_dates');
								return;
							}
						} else {
							$this->Flash->success(__('Your team events have been created. Selections have been preserved here in case you have more like this to create.'));
							$this->set(compact('team_event'));
							$this->render('add_dates');
							return;
						}
					} else {
						$date = $team_event->date;
						for ($i = 0; $i < $this->getRequest()->getData('repeat_count'); ++ $i) {
							// Note: We intentionally use a different variable than $team_event,
							// so that the team_event that is sent to the view has original data
							// in it, not the modified date.
							$team_event['dates'][$i] = $this->TeamEvents->newEntity(array_merge($this->getRequest()->getData(), ['team_id' => $id, 'date' => $date]));

							// Calculate the date of the next event
							switch ($this->getRequest()->getData('repeat_type')) {
								case 'weekly':
									$date = $date->addWeeks(1);
									break;

								case 'daily':
									$date = $date->addDays(1);
									break;

								// TODO: Confirm that the first day is a Monday
								case 'weekdays':
									$date = $date->addWeekday();
									break;

								// TODO: Confirm that the first day is a Saturday
								case 'weekends':
									do {
										$date = $date->addDays(1);
									} while ($date->isWeekday());
									break;
							}
						}
						$this->TeamEvents->saveMany($team_event['dates']);
						$this->Flash->success(__('Your team events have been created. Selections have been preserved here in case you have more like this to create.'));
					}
				}
			} else {
				if ($this->TeamEvents->save($team_event)) {
					$this->Flash->success(__('The team event has been saved.'));
					return $this->redirect('/');
				} else {
					$this->Flash->warning(__('The team event could not be saved. Please correct the errors below and try again.'));
				}
			}
		} else {
			$team_event->team_id = $id;
		}

		$this->_loadAddressOptions();

		$this->set(compact('team_event'));
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
			$team_event = $this->TeamEvents->get($id, [
				'contain' => [
					'Teams' => [
						'Divisions' => ['Leagues'],
					],
				],
			]);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($team_event);

		if ($this->getRequest()->is(['patch', 'post', 'put'])) {
			$team_event = $this->TeamEvents->patchEntity($team_event, $this->getRequest()->getData());
			if ($this->TeamEvents->save($team_event)) {
				$this->Flash->success(__('The team event has been saved.'));
				return $this->redirect('/');
			} else {
				$this->Flash->warning(__('The team event could not be saved. Please correct the errors below and try again.'));
			}
		}
		$this->Configuration->loadAffiliate($this->TeamEvents->Teams->affiliate($team_event->team_id));
		$this->_loadAddressOptions();

		$this->set(compact('team_event'));
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
			$team_event = $this->TeamEvents->get($id);
		} catch (RecordNotFoundException|InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		}

		$this->Authorization->authorize($team_event);

		if ($this->TeamEvents->delete($team_event)) {
			$this->Flash->success(__('The team event has been deleted.'));
		} else if ($team_event->getError('delete')) {
			$this->Flash->warning(current($team_event->getError('delete')));
		} else {
			$this->Flash->warning(__('The team event could not be deleted. Please, try again.'));
		}

		return $this->redirect('/');
	}

	public function attendance_change() {
		$id = $this->getRequest()->getQuery('event');
		$person_id = $this->getRequest()->getQuery('person') ?: $this->UserCache->currentId();
		if (!$person_id) {
			throw new ForbiddenException(new MissingIdentityResult(), ['attendance_change', self::class]);
		}

		$captains_contain = [
			'queryBuilder' => function (Query $q) {
				$q = $q->where([
					'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
					'TeamsPeople.status' => ROSTER_APPROVED,
				]);
				$my_id = $this->UserCache->currentId();
				if ($my_id) {
					$q = $q->where(['TeamsPeople.person_id !=' => $my_id]);
				}
				return $q;
			},
			Configure::read('Security.authModel'),
		];

		// We need the team ID for the where clause when reading the team record for the person
		// (which we only really need for the role from teams_people, but this is the easiest
		// way to get it).
		try {
			$team_id = $this->TeamEvents->field('team_id', ['TeamEvents.id' => $id]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		}

		$team_event = $this->TeamEvents->get($id, [
			'contain' => [
				// Get the list of captains, we may need to email them
				'Teams' => [
					'People' => $captains_contain,
					'Divisions' => ['Leagues'],
				],
				'Attendances' => [
					'queryBuilder' => function (Query $q) use ($person_id) {
						return $q->where(compact('person_id'));
					},
					'People' => [
						Configure::read('Security.authModel'),
						'Teams' => [
							'queryBuilder' => function (Query $q) use ($team_id) {
								return $q->where(compact('team_id'));
							},
						],
					],
				],
			]
		]);

		if (!empty($team_event->attendances)) {
			$attendance = $team_event->attendances[0];
		} else {
			$attendance = null;
		}
		$team = $team_event->team;

		$code = $this->getRequest()->getQuery('code');
		// After authorization, the context will also include an indication of whether it's a player or captain
		$context = new ContextResource($team, ['attendance' => $attendance, 'code' => $code, 'event' => $team_event]);
		$this->Authorization->authorize($context);

		$this->Configuration->loadAffiliate($team_event->team->division->league->affiliate_id);
		$date = $team_event->date;
		$past = $team_event->start_time->isPast();

		$identity = $this->Authentication->getIdentity();
		// The is_player and is_captain may have been set by TeamPolicy::canAttendance_change
		$is_me = $context->is_player || ($identity && ($identity->isMe($attendance) || $identity->isRelative($attendance)));
		$is_captain = $context->is_captain || ($identity && $identity->isCaptainOf($attendance));

		$role = $attendance->person->teams[0]->_joinData->role;
		$attendance_options = GamesTable::attendanceOptions($role, $attendance->status, $past, $is_captain);

		if ($code || $this->getRequest()->is(['patch', 'post', 'put'])) {
			if ($code) {
				// Fake the posted data array with the status from the URL
				$data = ['status' => $this->getRequest()->getQuery('status')];
			} else {
				$data = $this->getRequest()->getData();
			}

			$days_to_event = FrozenDate::now()->diffInDays($date, false);
			$service = new AttendanceService($this->Flash, $this->getRequest()->is('ajax'));

			if (array_key_exists('status', $data) && $data['status'] == 'comment') {
				// Comments that come via Ajax will have the status set to comment, which is not useful.
				unset($data['status']);
				$result = $service->updateEventAttendanceComment($data, $attendance, $team_event, $date, $team, $is_me, $days_to_event, $past);
			} else {
				$role = $attendance->person->teams[0]->_joinData->role;
				$result = $service->updateEventAttendanceStatus($data, $attendance, $team_event, $date, $team, $role, $is_captain, $is_me, $days_to_event, $past, $attendance_options);
			}

			// Where do we go from here? It depends...
			if (!$result) {
				if ($code) {
					return $this->redirect('/');
				}
			} else {
				if ($this->getRequest()->is('ajax')) {
					$this->set('dedicated', $this->getRequest()->getQuery('dedicated'));
				} else if (!$this->Authorization->can($team, 'attendance')) {
					return $this->redirect(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team_id]]);
				} else {
					return $this->redirect(['action' => 'view', '?' => ['event' => $id]]);
				}
			}
		}

		$this->set(array_merge(compact('attendance', 'date', 'team', 'attendance_options', 'is_captain', 'is_me'), [
			'event' => $team_event,
			'person' => $attendance->person,
		]));
	}
}
