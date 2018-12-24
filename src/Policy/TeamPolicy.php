<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Event\FlashTrait;
use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Division;
use App\Model\Entity\Team;
use App\PasswordHasher\HasherTrait;
use Authorization\Exception\MissingIdentityException;
use Authorization\IdentityInterface;
use Cake\Core\Configure;
use Cake\Http\Exception\GoneException;
use Cake\I18n\FrozenDate;

class TeamPolicy extends AppPolicy {

	use FlashTrait;
	use HasherTrait;

	/**
	 * This policy overrides the default before function, because there are a few situations where admins
	 * don't actually have complete access, and because we allow some roster operations to happen
	 * through emailed links, usable by people who aren't logged in.
	 */
	public function before($identity, $resource, $action) {
		$this->blockAnonymousExcept($identity, $action, ['ical', 'attendance_change', 'roster_accept', 'roster_decline']);
		$this->blockLocked($identity);
	}

	public function canJoin(IdentityInterface $identity, $controller) {
		return true;
	}

	public function canUnassigned(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canStatistics(IdentityInterface $identity, $controller) {
		return $identity->isManager();
	}

	public function canDownload(IdentityInterface $identity, Team $team) {
		if ($identity->isManagerOf($team) || $identity->isCoordinatorOf($team) || $identity->isCaptainOf($team)) {
			return true;
		}

		throw new ForbiddenRedirectException(__('You do not have access to download this team roster.'),
			['action' => 'view', 'team' => $team->id]);
	}

	public function canNumbers(IdentityInterface $identity, ContextResource $resource) {
		$team = $resource->resource();

		if (!Configure::read('feature.shirt_numbers')) {
			throw new ForbiddenRedirectException(__('Shirt numbers are not enabled on this site.'),
				['controller' => 'Teams', 'action' => 'view', 'team' => $team->id]);
		}

		// TODO: Check roster deadline
		if ($resource->has('roster')) {
			if ($identity->isMe($resource->roster)) {
				return true;
			}
		}

		return $this->_canEditRoster($identity, $team, $resource->division);
	}

	public function canStats(IdentityInterface $identity, ContextResource $resource) {
		$team = $resource->resource();
		$league = $resource->league;

		if ($league === null) {
			// TODO: Any situation where it makes sense to have stat tracking for a team not in a division?
			throw new ForbiddenRedirectException(__('This team does not have stat tracking enabled.'),
				['controller' => 'Teams', 'action' => 'view', 'team' => $team->id]);
		}
		if (!$league->hasStats()) {
			throw new ForbiddenRedirectException(__('This league does not have stat tracking enabled.'),
				['controller' => 'Teams', 'action' => 'view', 'team' => $team->id]);
		}

		return true;
	}

	public function canStat_sheet(IdentityInterface $identity, ContextResource $resource) {
		if (!$resource->league->hasStats()) {
			throw new ForbiddenRedirectException(__('This league does not have stat tracking enabled.'));
		}
		if (empty($resource->stat_types)) {
			throw new ForbiddenRedirectException(__('This league does not have any entry-type stats selected.'));
		}

		$team = $resource->resource();
		if (!$team->track_attendance) {
			throw new ForbiddenRedirectException(__('That team does not have attendance tracking enabled.'));
		}

		return $identity->isManagerOf($team) || $identity->isCoordinatorOf($team) || $identity->isCaptainOf($team);
	}

	public function canAdd(IdentityInterface $identity, $controller) {
		if ($identity->isManager()) {
			return true;
		}

		if (Configure::read('feature.registration')) {
			throw new ForbiddenRedirectException(__('This system creates teams through the registration process. Team creation through {0} is disabled. If you need a team created for some other reason (e.g. a touring team), please email {1} with the details, or call the office.', ZULURU, Configure::read('email.admin_email')));
		}

		return true;
	}

	public function canEdit(IdentityInterface $identity, Team $team) {
		return $identity->isManagerOf($team) || $identity->wasCaptainOf($team);
	}

	public function canEdit_home_field(IdentityInterface $identity, Team $team) {
		return Configure::read('feature.home_field') && $identity->isManagerOf($team);
	}

	public function canNote(IdentityInterface $identity, Team $team) {
		return (bool)Configure::read('feature.annotations');
	}

	public function canDelete(IdentityInterface $identity, Team $team) {
		// If the registration module is enabled, captains can't delete their teams
		return $identity->isManagerOf($team) || (!Configure::read('feature.registration') && $identity->isCaptainOf($team));
	}

	public function canMove(IdentityInterface $identity, Team $team) {
		return $identity->isManagerOf($team);
	}

	public function canIcal(IdentityInterface $identity = null, ContextResource $resource) {
		$division = $resource->division;
		if (!$division || $division->close < FrozenDate::now()->subWeeks(2)) {
			throw new GoneException();
		}

		return true;
	}

	public function canSpirit(IdentityInterface $identity, Team $team) {
		return $identity->isManagerOf($team) || $identity->isCoordinatorOf($team);
	}

	public function canAttendance(IdentityInterface $identity, Team $team) {
		if (!$team->track_attendance) {
			throw new ForbiddenRedirectException(__('That team does not have attendance tracking enabled.'));
		}

		return $identity->isManagerOf($team) || $identity->wasPlayerOn($team) || $identity->wasRelativePlayerOn($team);
	}

	public function canAttendance_change(IdentityInterface $identity = null, ContextResource $resource) {
		$team = $resource->resource();
		if (!$team->track_attendance) {
			throw new ForbiddenRedirectException(__('That team does not have attendance tracking enabled.'));
		}

		// Checking whether there's an attendance record should suffice as a proxy for checking
		// whether the person is even on the team. There shouldn't be any attendance records for
		// anyone that's not!
		$attendance = $resource->attendance;
		if (!$attendance) {
			return false;
		}

		// Make sure that it's within a time period that we're willing to accept changes on
		if ($resource->game_date) {
			$time = $resource->game_date;
		} else if ($resource->game) {
			$time = $resource->game->game_slot->start_time;
		} else if ($resource->event) {
			$time = $resource->event->start_time;
		}
		$resource->future = $time->isFuture();
		$recent = $time->wasWithinLast('2 week');
		$allow_recent = !((bool)$resource->future_only);

		if (!$resource->future && !($recent && $allow_recent)) {
			return false;
		}

		// Authenticate the hash code
		if ($resource->has('code')) {
			if ($resource->event) {
				$check = [$attendance->id, $attendance->team_event_id, $attendance->person_id, $attendance->created];
			} else {
				$check = [$attendance->id, $attendance->team_id, $attendance->game_id, $attendance->person_id, $attendance->created];
			}

			if ($this->_checkHash($check, $resource->code)) {
				// Only the player will have this confirmation code
				$resource->is_player = true;
				return true;
			}

			$check[] = 'captain';
			if ($this->_checkHash($check, $resource->code)) {
				// Only the captain will have this confirmation code
				$resource->is_captain = true;
				return true;
			}

			throw new ForbiddenRedirectException(__('The authorization code is invalid.'));
		}

		// Players can change their own attendance; captains and coordinators can change any attendance on their teams; managers can change anything
		return ($identity->isMe($attendance) || $identity->isRelative($attendance) ||
			$identity->isCaptainOf($team) || $identity->isCoordinatorOf($team) || $identity->isManagerOf($team)
		);
	}

	public function canEmails(IdentityInterface $identity, Team $team) {
		return $identity->isManagerOf($team) || $identity->isCaptainOf($team);
	}

	public function canAdd_player(IdentityInterface $identity, ContextResource $resource) {
		return $this->_canEditRoster($identity, $resource->resource(), $resource->division);
	}

	public function canAdd_from_team(IdentityInterface $identity, ContextResource $resource) {
		return $this->_canEditRoster($identity, $resource->resource(), $resource->division);
	}

	public function canAdd_from_event(IdentityInterface $identity, ContextResource $resource) {
		return $this->_canEditRoster($identity, $resource->resource(), $resource->division, false);
	}

	public function canRoster_role(IdentityInterface $identity, ContextResource $resource) {
		$team = $resource->resource();
		if ($this->_canEditRoster($identity, $team, $resource->division)) {
			return true;
		}

		// The team view page checks whether a person can change roster roles in general.
		if (!$resource->has('roster')) {
			return false;
		}

		$roster = $resource->roster;
		if (!$identity->isManagerOf($roster) && !$identity->isCoordinatorOf($roster) && !$identity->isCaptainOf($roster) &&
			!$identity->isMe($roster) && !$identity->isRelative($roster)) {
			return false;
		}

		$division = $resource->division;
		if ($division && $division->roster_deadline_passed) {
			throw new ForbiddenRedirectException(__('The roster deadline for this division has already passed.'), ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id]);
		}

		return true;
	}

	public function canRoster_position(IdentityInterface $identity, ContextResource $resource) {
		$team = $resource->resource();
		if ($this->_canEditRoster($identity, $team, $resource->division)) {
			return true;
		}

		$roster = $resource->roster;
		if (!$identity->isManagerOf($roster) && !$identity->isCoordinatorOf($roster) && !$identity->isCaptainOf($roster) &&
			!$identity->isMe($roster) && !$identity->isRelative($roster)) {
			return false;
		}

		$division = $resource->division;
		if ($division && $division->roster_deadline_passed) {
			throw new ForbiddenRedirectException(__('The roster deadline for this division has already passed.'), ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id]);
		}

		return true;
	}

	public function canRoster_add(IdentityInterface $identity, ContextResource $resource) {
		return $this->_canEditRoster($identity, $resource->resource(), $resource->division);
	}

	public function canRoster_request(IdentityInterface $identity, ContextResource $resource) {
		$team = $resource->resource();
		$division = $resource->division;

		if ($division && $division->roster_deadline_passed) {
			throw new ForbiddenRedirectException(__('The roster deadline for this division has already passed.'), ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id]);
		}

		return $team->open_roster && $identity->isPlayer() && !$identity->isPlayerOn($team);
	}

	public function canRoster_accept(IdentityInterface $identity = null, ContextResource $resource) {
		if (!$resource->has('person')) {
			throw new ForbiddenRedirectException(__('This person has neither been invited nor requested to join this team.'),
				['action' => 'view', 'team' => $resource->resource()->id]);
		}

		$roster = $resource->roster;
		if ($roster->status == ROSTER_APPROVED) {
			throw new ForbiddenRedirectException(__('This person has already been added to the roster.'),
				['action' => 'view', 'team' => $roster->team_id]);
		}

		$team = $resource->resource();
		$division = $resource->division;

		if ($this->_canEditRoster($identity, $team, $division, false)) {
			return true;
		}

		// Authenticate the hash code
		if ($resource->has('code')) {
			$code = $resource->code;
			if (!$this->_checkHash([$roster->id, $roster->team_id, $roster->person_id, $roster->role, $roster->created], $code)) {
				throw new ForbiddenRedirectException(__('The authorization code is invalid.'),
					['action' => 'view', 'team' => $roster->team_id], 'warning');
			}

			if ($division && $division->roster_deadline_passed) {
				throw new ForbiddenRedirectException(__('The roster deadline for this division has already passed.'), ['controller' => 'Teams', 'action' => 'view', 'team' => $roster->team_id]);
			}

			return true;
		}

		// If there wasn't a code, then anyone not logged in cannot proceed
		if (!$identity) {
			throw new MissingIdentityException();
		}

		// Captains can accept requests to join their teams
		// Players can accept when they are invited
		if (($roster->status == ROSTER_REQUESTED && $identity->isCaptainOf($roster)) ||
			($roster->status == ROSTER_INVITED && ($identity->isMe($roster) || $identity->isRelative($roster)))
		) {
			if ($division && $division->roster_deadline_passed) {
				throw new ForbiddenRedirectException(__('The roster deadline for this division has already passed.'), ['controller' => 'Teams', 'action' => 'view', 'team' => $roster->team_id]);
			}

			return true;
		}

		throw new ForbiddenRedirectException(
			__('You are not allowed to accept this roster {0}.',
				($roster->status == ROSTER_INVITED) ? __('invitation') : __('request')),
			['action' => 'view', 'team' => $roster->team_id], 'warning'
		);
	}

	public function canRoster_decline(IdentityInterface $identity = null, ContextResource $resource) {
		if (!$resource->has('person')) {
			throw new ForbiddenRedirectException(__('This person has neither been invited nor requested to join this team.'),
				['action' => 'view', 'team' => $resource->resource()->id]);
		}

		$roster = $resource->roster;
		if ($roster->status == ROSTER_APPROVED) {
			throw new ForbiddenRedirectException(__('This person has already been added to the roster.'),
				['action' => 'view', 'team' => $roster->team_id]);
		}

		$team = $resource->resource();
		$division = $resource->division;

		if ($this->_canEditRoster($identity, $team, $division, false)) {
			return true;
		}

		// Authenticate the hash code
		if ($resource->has('code')) {
			$code = $resource->code;
			if (!$this->_checkHash([$roster->id, $roster->team_id, $roster->person_id, $roster->role, $roster->created], $code)) {
				throw new ForbiddenRedirectException(__('The authorization code is invalid.'),
					['action' => 'view', 'team' => $roster->team_id], 'warning');
			}

			if ($division && $division->roster_deadline_passed) {
				throw new ForbiddenRedirectException(__('The roster deadline for this division has already passed.'), ['controller' => 'Teams', 'action' => 'view', 'team' => $roster->team_id]);
			}

			return true;
		}

		// If there wasn't a code, then anyone not logged in cannot proceed
		if (!$identity) {
			throw new MissingIdentityException();
		}

		// Captains or players can either decline an invite or request from the other,
		// or remove one that they made themselves.
		if ($identity->isCaptainOf($roster) || $identity->isMe($roster) || $identity->isRelative($roster)) {
			if ($division && $division->roster_deadline_passed) {
				throw new ForbiddenRedirectException(__('The roster deadline for this division has already passed.'), ['controller' => 'Teams', 'action' => 'view', 'team' => $roster->team_id]);
			}

			return true;
		}

		throw new ForbiddenRedirectException(
			__('You are not allowed to decline this roster {0}.',
				($roster->status == ROSTER_INVITED) ? __('invitation') : __('request')),
			['action' => 'view', 'team' => $roster->team_id], 'warning'
		);
	}

	protected function _canEditRoster(IdentityInterface $identity = null, Team $team, Division $division = null, $allow_captain = true) {
		if (!$identity) {
			return false;
		}

		$administrative = $identity->isManagerOf($team) || $identity->isCoordinatorOf($team);

		// Any admin, manager or coordinator can edit, if they are not on the team
		if (!$identity->isPlayerOn($team) && !$identity->isRelativePlayerOn($team)) {
			return $administrative;
		}

		// Any admin, manager, coordinator or captain on the team can edit, if the roster deadline hasn't passed (or isn't set)
		$is_captain = $allow_captain && $identity->isCaptainOf($team);
		if ($administrative || $is_captain) {
			if ($division && !$division->roster_deadline_passed) {
				return true;
			}

			$msg = __('The roster deadline for this division has already passed.');
			if ($administrative) {
				$msg .= ' ' . __('As a member of this team, your permissions have been restricted to prevent accidental misuse.');
			}

			throw new ForbiddenRedirectException($msg, ['controller' => 'Teams', 'action' => 'view', 'team' => $team->id]);
		}

		return false;
	}

	public function canSubmit_score(IdentityInterface $identity, Team $team) {
		return $identity->isCaptainOf($team);
	}

	public function canLive_score(IdentityInterface $identity, Team $team) {
		return $identity->isCaptainOf($team);
	}

	public function canScore_up(IdentityInterface $identity, Team $team) {
		return $identity->isCaptainOf($team);
	}

	public function canScore_down(IdentityInterface $identity, Team $team) {
		return $identity->isCaptainOf($team);
	}

	public function canTimeout(IdentityInterface $identity, Team $team) {
		return $identity->isCaptainOf($team);
	}

	public function canPlay(IdentityInterface $identity, Team $team) {
		return $identity->isCaptainOf($team);
	}

	public function canView_roster(IdentityInterface $identity = null, $controller) {
		return Configure::read('feature.public') || ($identity && $identity->isLoggedIn());
	}

	public function canView_notes(IdentityInterface $identity, Team $team) {
		return Configure::read('feature.annotations') && ($identity->isPlayerOn($team) || $identity->isRelativePlayerOn($team));
	}

	public function canAdd_event(IdentityInterface $identity, Team $team) {
		if (!$team->track_attendance) {
			throw new ForbiddenRedirectException(__('That team does not have attendance tracking enabled.'));
		}

		return $identity->isManagerOf($team) || $identity->isCaptainOf($team);
	}

	public function canDisplay_gender(IdentityInterface $identity, ContextResource $resource) {
		$team = $resource->resource();
		// If the team isn't in a division that's currently open, or opening soon, don't show it.
		if (!$team->division_id) {
			return false;
		}

		$division = $resource->division;
		if ($division && ($division->is_open || $division->open->isFuture())) {
			return $identity->isManagerOf($team) || $identity->isCoordinatorOf($team) || $identity->isPlayerOn($team);
		}

		return false;
	}
}
