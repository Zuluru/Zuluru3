<?php
namespace App\Shell\Task;

use App\Middleware\ConfigurationLoader;
use App\PasswordHasher\HasherTrait;
use App\Controller\AppController;
use App\Model\Entity\Team;
use App\Model\Entity\TeamEvent;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * TeamEventAttendance Task
 *
 * @property \App\Model\Table\TeamEventsTable $events_table
 * @property \App\Model\Table\ActivityLogsTable $logs_table
 */
class TeamEventAttendanceTask extends Shell {

	use HasherTrait;

	public function main() {
		ConfigurationLoader::loadConfiguration();
		$this->events_table = TableRegistry::get('TeamEvents');
		$this->logs_table = TableRegistry::get('ActivityLogs');

		$captain_contain = [
			'People' => [
				'queryBuilder' => function (Query $q) {
					return $q->where([
						'TeamsPeople.role IN' => Configure::read('privileged_roster_roles'),
						'TeamsPeople.status' => ROSTER_APPROVED,
					]);
				},
				Configure::read('Security.authModel'),
			],
		];

		// Find all of the events that might have players that need to be reminded about attendance
		$remind = $this->events_table->find()
			->contain([
				'Teams' => $captain_contain,
				'AttendanceReminderEmails',
			])
			->where([
				'TeamEvents.date >=' => FrozenDate::now(),
				'Teams.track_attendance' => true,
				'Teams.attendance_reminder !=' => -1,
			])
			->andWhere(function (QueryExpression $exp, Query $q) {
				$days_to_event = $q->func()->dateDiff(['TeamEvents.date' => 'literal', FrozenDate::now()->toDateString()]);
				return $exp->gte($days_to_event, 'Teams.attendance_reminder');
			});

		$teams = [];
		foreach ($remind as $team_event) {
			if (!array_key_exists($team_event->team_id, $teams)) {
				$teams[$team_event->team_id] = $this->events_table->Teams->get($team_event->team_id, [
					'contain' => [
						'People' => [
							Configure::read('Security.authModel'),
							'Settings' => [
								'queryBuilder' => function (Query $q) {
									return $q->where(['category' => 'personal', 'name' => 'attendance_emails']);
								},
							],
						],
					]
				]);
			}

			// Future dates give a negative diff; a positive number is more logical here.
			$days_to_event = - $team_event->date->diffInDays(null, false);
			$reminded = collection($team_event->attendance_reminder_emails)->extract('person_id')->toArray();

			if ($team_event->team->track_attendance && $team_event->team->attendance_reminder >= $days_to_event) {
				$this->_remindAttendance($team_event, $teams[$team_event->team_id], $reminded);
			}
		}

		// Find all of the events that might have captains that need attendance summaries
		$summary = $this->events_table->find()
			->contain([
				'Teams' => $captain_contain,
				'AttendanceSummaryEmails',
			])
			->where([
				'TeamEvents.date >=' => FrozenDate::now(),
				'Teams.track_attendance' => true,
				'Teams.attendance_summary !=' => -1,
			])
			->andWhere(function (QueryExpression $exp, Query $q) {
				$days_to_event = $q->func()->dateDiff(['TeamEvents.date' => 'literal', FrozenDate::now()->toDateString()]);
				return $exp->gte($days_to_event, 'Teams.attendance_summary');
			});

		$teams = [];
		foreach ($summary as $team_event) {
			if (!array_key_exists($team_event->team_id, $teams)) {
				$teams[$team_event->team_id] = $this->events_table->Teams->get($team_event->team_id, [
					'contain' => [
						'People' => [
							Configure::read('Security.authModel'),
						],
					]
				]);
			}

			// Future dates give a negative diff; a positive number is more logical here.
			$days_to_event = - $team_event->date->diffInDays(null, false);
			$summarized = collection($team_event->attendance_summary_emails)->extract('team_id')->toArray();

			if ($team_event->team->track_attendance && $team_event->team->attendance_summary >= $days_to_event) {
				$this->_summarizeAttendance($team_event, $teams[$team_event->team_id], $summarized);
			}
		}
	}

	protected function _remindAttendance(TeamEvent $team_event, Team $team, $reminded) {
		// Read the attendance records for this event.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = $this->events_table->readAttendance($team, $team_event->id);
		$regular_roles = Configure::read('playing_roster_roles');
		$sub_roles = Configure::read('extended_playing_roster_roles');

		foreach ($attendance->attendances as $record) {
			$person = collection($team->people)->firstMatch(['id' => $record->person_id]);
			$regular = in_array($person->_joinData->role, $regular_roles);
			$sub = (!$regular && in_array($person->_joinData->role, $sub_roles));
			$always = (!empty($person->settings) && $person->settings[0]->value != false);
			if (!in_array($person->id, $reminded)) {
				if (($regular && $record->status == ATTENDANCE_UNKNOWN) ||
					($sub && $record->status == ATTENDANCE_INVITED) ||
					$always)
				{
					if (AppController::_sendMail([
						'to' => $person,
						'replyTo' => $team_event->team->people,
						'subject' => function() use ($team) { return __('{0} attendance reminder', $team->name); },
						'template' => 'event_attendance_reminder',
						'sendAs' => 'both',
						'viewVars' => array_merge([
							'person' => $person,
							'status' => $record->status,
							'code' => $this->_makeHash([$record->id, $record->team_event_id, $record->person_id, $record->created]),
						], compact('team_event', 'team')),
						'header' => [
							'Auto-Submitted' => 'auto-generated',
							'X-Auto-Response-Suppress' => 'OOF',
						],
					]))
					{
						$this->logs_table->save($this->logs_table->newEntity([
							'type' => 'email_event_attendance_reminder',
							'team_event_id' => $team_event->id,
							'person_id' => $person->id,
						]));
					}
				}
			}
		}
	}

	protected function _summarizeAttendance(TeamEvent $team_event, Team $team, $summarized) {
		if (in_array($team->id, $summarized)) {
			return;
		}

		// Read the attendance records for this event.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = $this->events_table->readAttendance($team, $team_event->id);

		// Summarize by attendance status
		$column = Configure::read('gender.column');
		$summary = array_fill_keys(array_keys(Configure::read('attendance')),
			array_fill_keys(array_keys(Configure::read("options.$column")), [])
		);
		foreach ($attendance->attendances as $record) {
			$person = collection($team->people)->firstMatch(['id' => $record->person_id]);
			$summary[$record->status][$person->$column][] = $person->full_name;
		}

		if (AppController::_sendMail([
			'to' => $team_event->team->people,
			'subject' => function() use ($team) { return __('{0} attendance summary', $team->name); },
			'template' => 'event_attendance_summary',
			'sendAs' => 'both',
			'viewVars' => array_merge([
				'captains' => implode(', ', collection($team_event->team->people)->extract('first_name')->toArray()),
			], compact('team_event', 'team', 'summary')),
			'header' => [
				'Auto-Submitted' => 'auto-generated',
				'X-Auto-Response-Suppress' => 'OOF',
			],
		])) {
			$this->logs_table->save($this->logs_table->newEntity([
				'type' => 'email_event_attendance_summary',
				'team_event_id' => $team_event->id,
				'team_id' => $team->id,
			]));
		}
	}

}
