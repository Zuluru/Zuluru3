<?php
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Query;
use App\Auth\HasherTrait;
use App\Model\Table\GamesTable;

/**
 * TeamEvents Controller
 *
 * @property \App\Model\Table\TeamEventsTable $TeamEvents
 */
class TeamEventsController extends AppController {

	use HasherTrait;

	/**
	 * _publicActions method
	 *
	 * @return array of actions that can be taken even by visitors that are not logged in.
	 */
	protected function _publicActions() {
		// Attendance updates may come from emailed links; people might not be logged in
		return ['attendance_change'];
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
				// Managers can perform these operations in affiliates they manage
				if (in_array($this->request->params['action'], [
					'add',
				])) {
					// If a team id is specified, check if we're a manager of that team's affiliate
					$team = $this->request->query('team');
					if ($team) {
						if (in_array($this->Teams->affiliate($team), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
				if (in_array($this->request->params['action'], [
					'edit',
					'delete',
					'view',
				]))
				{
					// If an event id is specified, check if we're a manager of that event's affiliate
					$event = $this->request->query('event');
					if ($event) {
						if (in_array($this->TeamEvents->affiliate($event), $this->UserCache->read('ManagedAffiliateIDs'))) {
							return true;
						} else {
							Configure::write('Perm.is_manager', false);
						}
					}
				}
			}

			// People can perform these operations on teams they run
			if (in_array($this->request->params['action'], [
				'add',
			])) {
				// If a team id is specified, check if we're a captain of that team
				$team = $this->request->query('team');
				if ($team && in_array($team, $this->UserCache->read('OwnedTeamIDs'))) {
					return true;
				}
			}
			if (in_array($this->request->params['action'], [
				'edit',
				'delete',
			])) {
				$event = $this->request->query('event');
				if ($event) {
					$team = $this->TeamEvents->field('team_id', ['id' => $event]);
					if ($team && in_array($team, $this->UserCache->read('OwnedTeamIDs'))) {
						return true;
					}
				}
			}

			// People can perform these operations on teams they or their relatives are on
			if (in_array($this->request->params['action'], [
				'view',
			])) {
				$event = $this->request->query('event');
				if ($event) {
					$team = $this->TeamEvents->field('team_id', ['id' => $event]);
					if ($team) {
						if (in_array($team, $this->UserCache->read('AllTeamIDs')) || in_array($team, $this->UserCache->read('AllRelativeTeamIDs'))) {
							return true;
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
	 * View method
	 *
	 * @return void|\Cake\Network\Response
	 */
	public function view() {
		$id = $this->request->query('event');
		try {
			$team_event = $this->TeamEvents->get($id, [
				'contain' => [
					'Teams' => [
						'People',
						'Divisions' => ['Leagues'],
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		}
		$this->Configuration->loadAffiliate($team_event->team->division->league->affiliate_id);

		\App\lib\context_usort($team_event->team->people, ['App\Model\Table\TeamsTable', 'compareRoster'], ['team' => $team_event->team]);

		$attendance = $this->TeamEvents->readAttendance($team_event->team, $id)->attendances;
		$this->set(compact('team_event', 'attendance'));
		$this->set('is_captain', in_array($team_event->team->id, $this->UserCache->read('OwnedTeamIDs')));
	}

	/**
	 * Add method
	 *
	 * @return void|\Cake\Network\Response Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$id = $this->request->query('team');
		try {
			$team = $this->TeamEvents->Teams->get($id, [
				'contain' => ['Divisions' => ['Leagues']],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team.'));
			return $this->redirect('/');
		}

		if (!empty($team->division)) {
			$this->Configuration->loadAffiliate($team->division->league->affiliate_id);
		} else {
			$this->Configuration->loadAffiliate($team->affiliate_id);
		}

		$team_event = $this->TeamEvents->newEntity();

		if ($this->request->is('post')) {
			$team_event = $this->TeamEvents->patchEntity($team_event, array_merge($this->request->data, ['team_id' => $id, 'dates' => []]));

			// TODO: This entire block could benefit from some refactoring to improve code re-use and error detection
			if (!empty($this->request->data['repeat'])) {
				if (!$team_event->errors()) {
					if ($this->request->data['repeat_type'] == 'custom') {
						if (!empty($this->request->data['dates'])) {
							if (!$this->TeamEvents->connection()->transactional(function () use ($id, $team_event) {
								$team_event['dates'] = $dates = [];
								for ($i = 0; $i < $this->request->data['repeat_count']; ++ $i) {
									// Note: We intentionally use a different variable than $team_event,
									// so that the team_event that is sent to the view has original data
									// in it, not the modified date.
									$team_event['dates'][$i] = $this->TeamEvents->newEntity(array_merge($this->request->data, ['date' => $this->request->data['dates'][$i]['date']]));
									$this->TeamEvents->save($team_event['dates'][$i]);
									if (in_array($team_event['dates'][$i]->date, $dates)) {
										$team_event['dates'][$i]->errors('date', __('You cannot select the same date more than once.'));
									} else {
										$dates[] = $team_event['dates'][$i]->date;
									}
								}
								if ($team_event->errors()) {
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
						for ($i = 0; $i < $this->request->data['repeat_count']; ++ $i) {
							// Note: We intentionally use a different variable than $team_event,
							// so that the team_event that is sent to the view has original data
							// in it, not the modified date.
							$team_event['dates'][$i] = $this->TeamEvents->newEntity(array_merge($this->request->data, ['team_id' => $id, 'date' => $date]));
							$this->TeamEvents->save($team_event['dates'][$i]);

							// Calculate the date of the next event
							switch ($this->request->data['repeat_type']) {
								case 'weekly':
									$date = $date->addWeek();
									break;

								case 'daily':
									$date = $date->addDay();
									break;

								// TODO: Confirm that the first day is a Monday
								case 'weekdays':
									$date = $date->addWeekday();
									break;

								// TODO: Confirm that the first day is a Saturday
								case 'weekends':
									do {
										$date = $date->addDay();
									} while ($date->isWeekday());
									break;
							}
						}
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
	 * @return void|\Cake\Network\Response Redirects on successful edit, renders view otherwise.
	 */
	public function edit() {
		$id = $this->request->query('event');
		try {
			$team_event = $this->TeamEvents->get($id, [
				'contain' => [
					'Teams' => [
						'Divisions' => ['Leagues'],
					],
				],
			]);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			$team_event = $this->TeamEvents->patchEntity($team_event, $this->request->data);
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
	 * @return void|\Cake\Network\Response Redirects to index.
	 */
	public function delete() {
		$this->request->allowMethod(['post', 'delete']);

		$id = $this->request->query('event');
		try {
			$team_event = $this->TeamEvents->get($id);
		} catch (RecordNotFoundException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		} catch (InvalidPrimaryKeyException $ex) {
			$this->Flash->info(__('Invalid team event.'));
			return $this->redirect('/');
		}

		if ($this->TeamEvents->delete($team_event)) {
			$this->Flash->success(__('The team event has been deleted.'));
		} else if ($team_event->errors('delete')) {
			$this->Flash->warning(current($team_event->errors('delete')));
		} else {
			$this->Flash->warning(__('The team event could not be deleted. Please, try again.'));
		}

		return $this->redirect('/');
	}

	public function attendance_change() {
		$id = $this->request->query('event');
		$person_id = $this->request->query('person') ?: Configure::read('Perm.my_id');

		$captains_contain = [
			'queryBuilder' => function (Query $q) {
				return $q->where([
					'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
					'TeamsPeople.status' => ROSTER_APPROVED,
					'TeamsPeople.person_id !=' => Configure::read('Perm.my_id'),
				]);
			},
			Configure::read('Security.authModel'),
		];

		// We need the team ID for the where clause when reading the team record for the person
		// (which we only really need for the role from teams_people, but this is the easiest
		// way to get it).
		try {
			$team_id = $this->TeamEvents->field('team_id', compact('id'));
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
		$this->Configuration->loadAffiliate($team_event->team->division->league->affiliate_id);
		$date = $team_event->date;
		$past = $team_event->start_time->isPast();

		if (!empty($team_event->attendances)) {
			$attendance = $team_event->attendances[0];
		}
		$team = $team_event->team;

		if (!$team->track_attendance) {
			$this->Flash->info(__('That team does not have attendance tracking enabled.'));
			return $this->redirect('/');
		}

		if (empty($attendance)) {
			$this->Flash->info(__('That person does not have an attendance record for this event.'));
			return $this->redirect('/');
		}

		if (empty($attendance->person->teams[0])) {
			$this->Flash->info(__('That person is not on this team.'));
			return $this->redirect('/');
		}

		$is_me = ($person_id == $this->UserCache->currentId() || in_array($person_id, $this->UserCache->read('RelativeIDs')));
		$is_captain = in_array($team_id, $this->UserCache->read('OwnedTeamIDs'));
		$is_coordinator = in_array($team->division_id, $this->UserCache->read('DivisionIDs'));

		// We must do other permission checks here, because we allow non-logged-in users to accept
		// through email links
		$code = $this->request->query('code');
		if ($code) {
			// Authenticate the hash code
			if ($this->_checkHash([$attendance->id, $attendance->team_event_id, $attendance->person_id, $attendance->created], $code)) {
				// Only the player will have this confirmation code
				$is_me = true;
			} else if ($this->_checkHash([$attendance->id, $attendance->team_event_id, $attendance->person_id, $attendance->created, 'captain'], $code)) {
				$is_captain = true;
			} else {
				$this->Flash->warning(__('The authorization code is invalid.'));
				return $this->redirect('/');
			}

			// Fake the posted data array with the status from the URL
			$this->request->data = ['status' => $this->request->query('status')];
		} else {
			// Players can change their own attendance, captains and coordinators can change any attendance on their teams
			if (!$is_me && !$is_captain && !$is_coordinator) {
				$this->Flash->info(__('You are not allowed to change this attendance record.'));
				return $this->redirect('/');
			}
		}

		$role = $attendance->person->teams[0]->_joinData->role;
		$attendance_options = GamesTable::attendanceOptions($role, $attendance->status, $past, $is_captain);

		if ($code || $this->request->is(['patch', 'post', 'put'])) {
			// Future dates give a negative diff; a positive number is more logical here.
			$days_to_event = - $date->diffInDays(null, false);

			if (array_key_exists('status', $this->request->data) && $this->request->data['status'] == 'comment') {
				// Comments that come via Ajax will have the status set to comment, which is not useful.
				unset($this->request->data['status']);
				$result = $this->_updateAttendanceComment($attendance, $team_event, $date, $team, $is_me, $days_to_event, $past);
			} else {
				$result = $this->_updateAttendanceStatus($attendance, $team_event, $date, $team, $is_captain, $is_me, $days_to_event, $past, $attendance_options);
			}

			// Where do we go from here? It depends...
			if (!$result) {
				if ($code) {
					return $this->redirect('/');
				}
			} else {
				if ($this->request->is('ajax')) {
					$this->set('dedicated', $this->request->query('dedicated'));
				} else if (!Configure::read('Perm.is_logged_in')) {
					return $this->redirect(['controller' => 'Teams', 'action' => 'view', 'team' => $team_id]);
				} else {
					return $this->redirect(['action' => 'view', 'event' => $id]);
				}
			}
		}

		$this->set(array_merge(compact('attendance', 'date', 'team', 'attendance_options', 'is_captain', 'is_me'), [
			'event' => $team_event,
			'person' => $attendance->person,
			'status' => $attendance->status,
			'comment' => $attendance->comment,
		]));
	}

	protected function _updateAttendanceStatus($attendance, $team_event, $date, $team, $is_captain, $is_me, $days_to_event, $past, $attendance_options) {
		if (!array_key_exists($this->request->data['status'], $attendance_options)) {
			$this->Flash->info(__('That is not currently a valid attendance status for this person for this event.'));
			return false;
		}

		$attendance = $this->TeamEvents->Attendances->patchEntity($attendance, $this->request->data);
		if (!$attendance->dirty('status') && !$attendance->dirty('comment') && !$attendance->dirty('note')) {
			return true;
		}

		if (!$this->TeamEvents->Attendances->save($attendance)) {
			$this->Flash->warning(__('Failed to update the attendance status!'));
			return false;
		}

		if (!$this->request->is('ajax')) {
			$this->Flash->success(__('Attendance has been updated to {0}.', $attendance_options[$attendance->status]));
		}

		// Maybe send some emails, only if the event is in the future
		if ($past) {
			return true;
		}

		$role = $attendance->person->teams[0]->_joinData->role;

		// Send email from the player to the captain(s) if it's within the configured date range
		if ($is_me && $team->attendance_notification >= $days_to_event) {
			if (!empty($team->people)) {
				$this->_sendMail([
					'to' => $team->people,
					'replyTo' => $attendance->person,
					'subject' => __('{0} attendance change', $team->name),
					'template' => 'event_attendance_captain_notification',
					'sendAs' => 'both',
					'viewVars' => array_merge([
						'captains' => implode(', ', collection($team->people)->extract('first_name')->toArray()),
						'person' => $attendance->person,
						'code' => $this->_makeHash([$attendance->id, $attendance->team_event_id, $attendance->person_id, $attendance->created, 'captain']),
					], compact('attendance', 'team_event', 'date', 'team')),
				]);
			}
		}
		// Always send an email from the captain to substitute players. It will likely
		// be an invitation to play or a response to a request or cancelling attendance
		// if another player is available. Regardless, we need to communicate this.
		else if ($is_captain && !in_array($role, Configure::read('playing_roster_roles'))) {
			$captain = $this->UserCache->read('Person.full_name');
			$this->_sendMail([
				'to' => $attendance->person,
				'replyTo' => $this->UserCache->read('Person'),
				'subject' => __('{0} attendance change for {1} on {2}', $team->name, __('event'), $date),
				'template' => 'event_attendance_substitute_notification',
				'sendAs' => 'both',
				'viewVars' => array_merge([
					'captain' => $captain ? $captain : __('A coach or captain'),
					'person' => $attendance->person,
					'code' => $this->_makeHash([$attendance->id, $attendance->team_event_id, $attendance->person_id, $attendance->created]),
					'player_options' => GamesTable::attendanceOptions($role, $attendance->status, $past, false),
				], compact('attendance', 'team_event', 'date', 'team')),
			]);
		}

		return true;
	}

	protected function _updateAttendanceComment($attendance, $team_event, $date, $team, $is_me, $days_to_event, $past) {
		$attendance = $this->TeamEvents->Attendances->patchEntity($attendance, $this->request->data);
		if (!$attendance->dirty('comment')) {
			return true;
		}

		if (!$this->TeamEvents->Attendances->save($attendance)) {
			$this->Flash->warning(__('Failed to update the attendance comment!'));
			return false;
		}

		if (!$this->request->is('ajax')) {
			$this->Flash->success(__('Attendance comment has been updated.'));
		}

		// Maybe send some emails, only if the event is in the future
		if ($past) {
			return true;
		}

		// Send email from the player to the captain(s) if it's within the configured date range
		if ($is_me && $team->attendance_notification >= $days_to_event) {
			if (!empty($team->people)) {
				$this->_sendMail([
					'to' => $team->people,
					'replyTo' => $attendance->person,
					'subject' => __('{0} attendance comment', $team->name),
					'template' => 'event_attendance_comment_captain_notification',
					'sendAs' => 'both',
					'viewVars' => array_merge([
						'captains' => implode(', ', collection($team->people)->extract('first_name')->toArray()),
						'person' => $attendance->person,
					], compact('attendance', 'team_event', 'date', 'team')),
				]);
			}
		}

		return true;
	}

}
