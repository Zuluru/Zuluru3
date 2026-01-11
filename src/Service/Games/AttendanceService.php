<?php
declare(strict_types=1);

namespace App\Service\Games;

use App\Controller\AppController;
use App\Core\UserCache;
use App\Model\Entity\Attendance;
use App\Model\Entity\Game;
use App\Model\Entity\Team;
use App\Model\Entity\TeamEvent;
use App\Model\Table\AttendancesTable;
use App\Model\Table\GamesTable;
use App\PasswordHasher\HasherTrait;
use Cake\Controller\Component\FlashComponent;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\ORM\Locator\LocatorAwareTrait;

class AttendanceService
{
	use LocatorAwareTrait;
	use HasherTrait;

	private FlashComponent $Flash;
	private AttendancesTable $Attendances;
	private bool $ajax;

	public function __construct(FlashComponent $flash, bool $ajax) {
		$this->Flash = $flash;
		$this->Attendances = $this->fetchTable('Attendances');
		$this->ajax = $ajax;
	}

	public function updateGameAttendanceStatus(array  $data, Attendance $attendance, Game $game, FrozenDate $date, Team $team, Team $opponent,
		string $role, bool $is_captain, bool $is_me, int $days_to_game, bool $past, array $attendance_options): bool
	{
		if (!array_key_exists($data['status'], $attendance_options)) {
			$this->Flash->info(__('That is not currently a valid attendance status for this person for this game.'));
			return false;
		}

		$attendance = $this->Attendances->patchEntity($attendance, $data);
		if (!$attendance->isDirty('status') && !$attendance->isDirty('comment') && !$attendance->isDirty('note')) {
			return true;
		}

		if (!$this->Attendances->save($attendance)) {
			$this->Flash->warning(__('Failed to update the attendance status!'));
			return false;
		}

		if (!$this->ajax) {
			$this->Flash->success(__('Attendance has been updated to {0}.', $attendance_options[$attendance->status]));
		}

		// Maybe send some emails, only if the game is in the future
		if ($past) {
			return true;
		}

		// Send email from the player to the captain(s) if it's within the configured date range
		if ($is_me && $team->attendance_notification >= $days_to_game) {
			if (!empty($team->people)) {
				AppController::_sendMail([
					'to' => $team->people,
					'replyTo' => $attendance->person,
					'subject' => function() use ($team) { return __('{0} attendance change', $team->name); },
					'template' => 'attendance_captain_notification',
					'sendAs' => 'both',
					'viewVars' => array_merge([
						'captains' => implode(', ', collection($team->people)->extract('first_name')->toArray()),
						'person' => $attendance->person,
						'code' => $this->_makeHash([$attendance->id, $attendance->team_id, $attendance->game_id, $attendance->person_id, $attendance->created, 'captain']),
					], compact('attendance', 'game', 'date', 'team', 'opponent')),
				]);
			}
		}
		// Always send an email from the captain to substitute players. It will likely
		// be an invitation to play or a response to a request or cancelling attendance
		// if another player is available. Regardless, we need to communicate this.
		else if ($is_captain && !in_array($role, Configure::read('playing_roster_roles'))) {
			$captain = UserCache::getInstance()->read('Person.full_name');
			AppController::_sendMail([
				'to' => $attendance->person,
				'replyTo' => UserCache::getInstance()->read('Person'),
				'subject' => function() use ($team, $date) { return __('{0} attendance change for {1} on {2}', $team->name, __('game'), $date); },
				'template' => 'attendance_substitute_notification',
				'sendAs' => 'both',
				'viewVars' => array_merge([
					'captain' => $captain ? $captain : __('A coach or captain'),
					'person' => $attendance->person,
					'code' => $this->_makeHash([$attendance->id, $attendance->team_id, $attendance->game_id, $attendance->person_id, $attendance->created]),
					'player_options' => GamesTable::attendanceOptions($role, $attendance->status, $past, false),
				], compact('attendance', 'game', 'date', 'team', 'opponent')),
			]);
		}

		return true;
	}

	public function updateGameAttendanceComment(array $data, Attendance $attendance, Game $game, FrozenDate $date, Team $team, Team $opponent,
		bool  $is_me, int $days_to_game, bool $past): bool
	{
		$attendance = $this->Attendances->patchEntity($attendance, $data);
		if (!$attendance->isDirty('comment')) {
			return true;
		}

		if (!$this->Attendances->save($attendance)) {
			$this->Flash->warning(__('Failed to update the attendance comment!'));
			return false;
		}

		if (!$this->ajax) {
			$this->Flash->success(__('Attendance comment has been updated.'));
		}

		// Maybe send some emails, only if the game is in the future
		if ($past) {
			return true;
		}

		// Send email from the player to the captain(s) if it's within the configured date range
		if ($is_me && $team->attendance_notification >= $days_to_game) {
			if (!empty($team->people)) {
				AppController::_sendMail([
					'to' => $team->people,
					'replyTo' => $attendance->person,
					'subject' => function() use ($team) { return __('{0} attendance comment', $team->name); },
					'template' => 'attendance_comment_captain_notification',
					'sendAs' => 'both',
					'viewVars' => array_merge([
						'captains' => implode(', ', collection($team->people)->extract('first_name')->toArray()),
						'person' => $attendance->person,
					], compact('attendance', 'game', 'date', 'team', 'opponent')),
				]);
			}
		}

		return true;
	}

	public function updateEventAttendanceStatus(array $data, Attendance $attendance, TeamEvent $team_event, FrozenDate $date, Team $team,
		string $role, bool $is_captain, bool $is_me, int $days_to_event, bool $past, array $attendance_options): bool
	{
		if (!array_key_exists($data['status'], $attendance_options)) {
			$this->Flash->info(__('That is not currently a valid attendance status for this person for this event.'));
			return false;
		}

		$attendance = $this->Attendances->patchEntity($attendance, $data);
		if (!$attendance->isDirty('status') && !$attendance->isDirty('comment') && !$attendance->isDirty('note')) {
			return true;
		}

		if (!$this->Attendances->save($attendance)) {
			$this->Flash->warning(__('Failed to update the attendance status!'));
			return false;
		}

		if (!$this->ajax) {
			$this->Flash->success(__('Attendance has been updated to {0}.', $attendance_options[$attendance->status]));
		}

		// Maybe send some emails, only if the event is in the future
		if ($past) {
			return true;
		}

		// Send email from the player to the captain(s) if it's within the configured date range
		if ($is_me && $team->attendance_notification >= $days_to_event) {
			if (!empty($team->people)) {
				AppController::_sendMail([
					'to' => $team->people,
					'replyTo' => $attendance->person,
					'subject' => function() use ($team) { return __('{0} attendance change', $team->name); },
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
			$captain = UserCache::getInstance()->read('Person.full_name');
			AppController::_sendMail([
				'to' => $attendance->person,
				'replyTo' => UserCache::getInstance()->read('Person'),
				'subject' => function() use ($team, $date) { return __('{0} attendance change for {1} on {2}', $team->name, __('event'), $date); },
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

	public function updateEventAttendanceComment(array $data, Attendance $attendance, TeamEvent $team_event, FrozenDate $date, Team $team,
		bool $is_me, int $days_to_event, bool $past): bool
	{
		$attendance = $this->Attendances->patchEntity($attendance, $data);
		if (!$attendance->isDirty('comment')) {
			return true;
		}

		if (!$this->Attendances->save($attendance)) {
			$this->Flash->warning(__('Failed to update the attendance comment!'));
			return false;
		}

		if (!$this->ajax) {
			$this->Flash->success(__('Attendance comment has been updated.'));
		}

		// Maybe send some emails, only if the event is in the future
		if ($past) {
			return true;
		}

		// Send email from the player to the captain(s) if it's within the configured date range
		if ($is_me && $team->attendance_notification >= $days_to_event) {
			if (!empty($team->people)) {
				AppController::_sendMail([
					'to' => $team->people,
					'replyTo' => $attendance->person,
					'subject' => function() use ($team) { return __('{0} attendance comment', $team->name); },
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
