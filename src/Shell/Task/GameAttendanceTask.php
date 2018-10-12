<?php
namespace App\Shell\Task;

use App\Auth\HasherTrait;
use App\Controller\AppController;
use App\Model\Entity\Game;
use App\Model\Entity\Team;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;
use Cake\I18n\FrozenDate;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * GameAttendance Task
 *
 * @property \App\Model\Table\GamesTable $games_table
 * @property \App\Model\Table\ActivityLogsTable $logs_table
 */
class GameAttendanceTask extends Shell {

	use HasherTrait;

	public function main() {
		$event = new CakeEvent('Configuration.initialize', $this);
		EventManager::instance()->dispatch($event);

		$this->games_table = TableRegistry::get('Games');
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

		// Find all of the games that might have players that need to be reminded about attendance
		// TODO: Do we need to do something to handle games that aren't yet scheduled?
		$remind = $this->games_table->find()
			->contain([
				'Divisions' => ['Days'],
				'GameSlots' => ['Fields' => ['Facilities']],
				'HomeTeam' => $captain_contain,
				'AwayTeam' => $captain_contain,
				'AttendanceReminderEmails',
			])
			->where([
				'Games.status' => 'normal',
				'Games.published' => true,
				'GameSlots.game_date >=' => FrozenDate::now(),
			])
			->andWhere(function (QueryExpression $exp, Query $q) {
				$days_to_game = $q->func()->dateDiff(['GameSlots.game_date' => 'literal', FrozenDate::now()->toDateString()]);
				$home_conditions = $q->newExpr()
					->eq('HomeTeam.track_attendance', true)
					->notEq('HomeTeam.attendance_reminder', -1)
					->gte($days_to_game, 'HomeTeam.attendance_reminder');
				$away_conditions = $q->newExpr()
					->eq('AwayTeam.track_attendance', true)
					->notEq('AwayTeam.attendance_reminder', -1)
					->gte($days_to_game, 'AwayTeam.attendance_reminder');

				return $exp->or_([$home_conditions, $away_conditions]);
			});

		foreach ($remind as $game) {
			// Future dates give a negative diff; a positive number is more logical here.
			$days_to_game = - $game->game_slot->game_date->diffInDays(null, false);
			$reminded = collection($game->attendance_reminder_emails)->extract('person_id')->toArray();

			if ($game->home_team && $game->home_team->track_attendance && $game->home_team->attendance_reminder >= $days_to_game) {
				$this->_remindAttendance($game, $game->home_team, $game->away_team, $reminded);
			}
			if ($game->away_team && $game->away_team->track_attendance && $game->away_team->attendance_reminder >= $days_to_game) {
				$this->_remindAttendance($game, $game->away_team, $game->home_team, $reminded);
			}
		}

		// Find all of the games that might have captains that need attendance summaries
		// TODO: Do we need to do something to handle games that aren't yet scheduled?
		$summary = $this->games_table->find()
			->contain([
				'Divisions' => ['Days'],
				'GameSlots' => ['Fields' => ['Facilities']],
				'HomeTeam' => $captain_contain,
				'AwayTeam' => $captain_contain,
				'AttendanceSummaryEmails',
			])
			->where([
				'Games.status' => 'normal',
				'Games.published' => true,
				'GameSlots.game_date >=' => FrozenDate::now(),
			])
			->andWhere(function (QueryExpression $exp, Query $q) {
				$days_to_game = $q->func()->dateDiff(['GameSlots.game_date' => 'literal', FrozenDate::now()->toDateString()]);
				$home_conditions = $q->newExpr()
					->eq('HomeTeam.track_attendance', true)
					->notEq('HomeTeam.attendance_summary', -1)
					->gte($days_to_game, 'HomeTeam.attendance_summary');
				$away_conditions = $q->newExpr()
					->eq('AwayTeam.track_attendance', true)
					->notEq('AwayTeam.attendance_summary', -1)
					->gte($days_to_game, 'AwayTeam.attendance_summary');

				return $exp->or_([$home_conditions, $away_conditions]);
			});

		foreach ($summary as $game) {
			// Future dates give a negative diff; a positive number is more logical here.
			$days_to_game = - $game->game_slot->game_date->diffInDays(null, false);
			$summarized = collection($game->attendance_summary_emails)->extract('team_id')->toArray();

			if ($game->home_team && $game->home_team->track_attendance && $game->home_team->attendance_summary >= $days_to_game) {
				$this->_summarizeAttendance($game, $game->home_team, $game->away_team, $summarized);
			}
			if ($game->away_team && $game->away_team->track_attendance && $game->away_team->attendance_summary >= $days_to_game) {
				$this->_summarizeAttendance($game, $game->away_team, $game->home_team, $summarized);
			}
		}
	}

	protected function _remindAttendance(Game $game, Team $team, Team $opponent = null, $reminded) {
		// Read the attendance records for this game and team.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = $this->games_table->readAttendance($team->id, collection($game->division->days)->extract('id')->toArray(), $game->id);
		$regular_roles = Configure::read('playing_roster_roles');
		$sub_roles = Configure::read('extended_playing_roster_roles');
		foreach ($attendance->people as $person) {
			$regular = in_array($person->_joinData->role, $regular_roles);
			$sub = (!$regular && in_array($person->_joinData->role, $sub_roles));
			$always = (!empty($person->settings) && $person->settings[0]->value != false);
			if (!in_array($person->id, $reminded)) {
				if (($regular && $person->attendances[0]->status == ATTENDANCE_UNKNOWN) ||
					($sub && $person->attendances[0]->status == ATTENDANCE_INVITED) ||
					$always)
				{
					$record = $person->attendances[0];
					if (AppController::_sendMail([
						'to' => $person,
						'replyTo' => $team->people,
						'subject' => __('{0} attendance reminder', $team->name),
						'template' => 'attendance_reminder',
						'sendAs' => 'both',
						'viewVars' => array_merge([
							'person' => $person,
							'status' => $record->status,
							'code' => $this->_makeHash([$record->id, $record->team_id, $record->game_id, $record->person_id, $record->created]),
						], compact('game', 'team', 'opponent')),
						'header' => [
							'Auto-Submitted' => 'auto-generated',
							'X-Auto-Response-Suppress' => 'OOF',
						],
					]))
					{
						$this->logs_table->save($this->logs_table->newEntity([
							'type' => 'email_attendance_reminder',
							'game_id' => $game->id,
							'person_id' => $person->id,
						]));
					}
				}
			}
		}
	}

	protected function _summarizeAttendance(Game $game, Team $team, Team $opponent = null, $summarized) {
		if (in_array($team->id, $summarized)) {
			return;
		}

		// Read the attendance records for this game and team.
		// We have to do it this way, not as a contain on the main find,
		// so that any missing records are created for us.
		$attendance = $this->games_table->readAttendance($team->id, collection($game->division->days)->extract('id')->toArray(), $game->id);

		// Summarize by attendance status
		$column = Configure::read('gender.column');
		$summary = array_fill_keys(array_keys(Configure::read('attendance')),
			array_fill_keys(array_keys(Configure::read("options.$column")), [])
		);
		foreach ($attendance->people as $person) {
			$summary[$person->attendances[0]->status][$person->$column][] = $person->full_name;
		}

		if (AppController::_sendMail([
			'to' => $team->people,
			'subject' => __('{0} attendance summary', $team->name),
			'template' => 'attendance_summary',
			'sendAs' => 'both',
			'viewVars' => array_merge([
				'summary' => $summary,
				'captains' => implode(', ', collection($team->people)->extract('first_name')->toArray()),
			], compact('game', 'team', 'opponent')),
			'header' => [
				'Auto-Submitted' => 'auto-generated',
				'X-Auto-Response-Suppress' => 'OOF',
			],
		])) {
			$this->logs_table->save($this->logs_table->newEntity([
				'type' => 'email_attendance_summary',
				'game_id' => $game->id,
				'team_id' => $team->id,
			]));
		}
	}

}
