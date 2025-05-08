<?php
namespace App\Policy;

use App\Authorization\ContextResource;
use App\Exception\ForbiddenRedirectException;
use App\Model\Entity\Game;
use App\Service\Games\ScoreService;
use Authorization\IdentityInterface;
use Cake\Core\Configure;
use Cake\Http\Exception\GoneException;
use Cake\I18n\FrozenDate;

class GamePolicy extends AppPolicy {

	/**
	 * This policy overrides the default before function, because there are a few
	 * situations where admins don't actually have complete access.
	 */
	public function before($identity, $resource, $action) {
		$this->blockAnonymousExcept($identity, $action, ['view', 'tooltip', 'ical']);
		$this->blockLocked($identity);
	}

	public function canView(IdentityInterface $identity = null, Game $game) {
		if ($game->published) {
			return true;
		}

		return ($identity && ($identity->isManagerOf($game) || $identity->isCoordinatorOf($game)));
	}

	public function canRatings_table(IdentityInterface $identity = null, ContextResource $resource) {
		$game = $resource->resource();
		$ratings_obj = $resource->ratings_obj;
		if (!$ratings_obj->perGameRatings()) {
			throw new ForbiddenRedirectException(__('The ratings calculator in use for this division does not support per-game ratings.'),
				['action' => 'view', '?' => ['game' => $game->id]]);
		}

		$division = $resource->division;
		if ($division->schedule_type === 'competition') {
			throw new ForbiddenRedirectException(__('Ratings table does not apply to competition divisions.'),
				['action' => 'view', '?' => ['game' => $game->id]]);
		}
		$preliminary = ($game->home_team_id === null || $game->away_team_id === null);

		return !$preliminary && $division->schedule_type != 'roundrobin' && $ratings_obj->perGameRatings() && !$game->isFinalized() && $identity && $identity->isLoggedIn();
	}

	public function canIcal(IdentityInterface $identity = null, ContextResource $resource) {
		$game = $resource->resource();
		$team_id = $resource->team_id;
		if (!$game->published || $resource->division->close < FrozenDate::now()->subWeeks(2) ||
			($team_id != $game->home_team_id && $team_id != $game->away_team_id))
		{
			throw new GoneException();
		}

		if ($game->published) {
			return true;
		}

		return ($identity && ($identity->isManagerOf($game) || $identity->isCoordinatorOf($game)));
	}

	public function canEdit(IdentityInterface $identity, Game $game) {
		return $identity->isManagerOf($game) || $identity->isCoordinatorOf($game);
	}

	public function canEdit_boxscore(IdentityInterface $identity, Game $game) {
		return $identity->isManagerOf($game) || $identity->isCoordinatorOf($game);
	}

	public function canDelete_score(IdentityInterface $identity, Game $game) {
		return $identity->isManagerOf($game) || $identity->isCoordinatorOf($game);
	}

	public function canAdd_score(IdentityInterface $identity, Game $game) {
		return $identity->isManagerOf($game) || $identity->isCoordinatorOf($game);
	}

	public function canNote(IdentityInterface $identity, ContextResource $resource) {
		if (!Configure::read('feature.annotations')) {
			return false;
		}

		// Manager, coordinator, or anyone playing in this game
		$game = $resource->resource();
		if ($identity->isManagerOf($game) || $identity->isCoordinatorOf($game) ||
			($resource->has('home_team') && $identity->isPlayerOn($resource->home_team)) ||
			($resource->has('away_team') && $identity->isPlayerOn($resource->away_team))
		) {
			return true;
		}

		throw new ForbiddenRedirectException(__('You are not on the roster of a team playing in this game.'),
			['action' => 'view', '?' => ['game' => $game->id]]);
	}

	public function canDelete(IdentityInterface $identity, Game $game) {
		return $identity->isManagerOf($game) || $identity->isCoordinatorOf($game);
	}

	public function canAttendance(IdentityInterface $identity, ContextResource $resource) {
		// Manager, or anyone playing in this game
		$game = $resource->resource();

		if ($resource->has('home_team') && $resource->home_team->track_attendance &&
			($identity->wasPlayerOn($resource->home_team) || $identity->wasRelativePlayerOn($resource->home_team))
		) {
			$resource->team_id = $resource->home_team->id;
			return true;
		}

		if ($resource->has('away_team') && $resource->away_team->track_attendance &&
			($identity->wasPlayerOn($resource->away_team) || $identity->wasRelativePlayerOn($resource->away_team))
		) {
			$resource->team_id = $resource->away_team->id;
			return true;
		}

		if ($identity->isManagerOf($game)) {
			// No team ID set here; some views will depend on that being set to generate a player-specific link
			return true;
		}

		throw new ForbiddenRedirectException(__('You are not on the roster of a team playing in this game.'),
			['action' => 'view', '?' => ['game' => $game->id]]);
	}

	protected function _canLive_score(IdentityInterface $identity, ContextResource $resource, $message) {
		$game = $resource->resource();

		if ($game->isFinalized()) {
			throw new ForbiddenRedirectException(__('The score for that game has already been finalized.'),
				['action' => 'view', '?' => ['game' => $game->id]]);
		}

		if ($identity->isCaptainOf($game)) {
			return true;
		}

		if (!$resource->has('team')) {
			/* TODOLATER: Revisit these permissions: there is currently no restriction on who can be a volunteer *
			if (!$identity->isVolunteer() && !$identity->isOfficial()) {
				return true;
			}
			*/

			throw new ForbiddenRedirectException($message);
		}

		return false;
	}

	public function canLive_score(IdentityInterface $identity, ContextResource $resource) {
		return $this->_canLive_score($identity, $resource, __('Invalid team.'));
	}

	public function canScore_up(IdentityInterface $identity, ContextResource $resource) {
		return $this->_canLive_score($identity, $resource, __('Invalid submitter.'));
	}

	public function canScore_down(IdentityInterface $identity, ContextResource $resource) {
		return $this->_canLive_score($identity, $resource, __('Invalid submitter.'));
	}

	public function canTimeout(IdentityInterface $identity, ContextResource $resource) {
		return $this->_canLive_score($identity, $resource, __('Invalid submitter.'));
	}

	public function canPlay(IdentityInterface $identity, ContextResource $resource) {
		return $this->_canLive_score($identity, $resource, __('Invalid submitter.'));
	}

	public function canSubmit(IdentityInterface $identity, ContextResource $resource) {
		$game = $resource->resource();
		$redirect = ['action' => 'view', '?' => ['game' => $game->id]];

		if (empty($game->home_team_id) || empty($game->away_team_id)) {
			throw new ForbiddenRedirectException(__('The opponent for that game has not been determined, so a score cannot yet be submitted.'), $redirect);
		}

		if (!$identity->can('submit_score', $resource) && !$identity->can('submit_spirit', $resource)) {
			return false;
		}

		if (!$resource->is_official) {
			// These restrictions do not apply to officials.
			if ($resource->team_id != $game->home_team_id && $resource->team_id != $game->away_team_id) {
				throw new ForbiddenRedirectException(__('That team is not playing in this game.'), $redirect);
			}

			if ($game->isFinalized()) {
				throw new ForbiddenRedirectException(__('The score for that game has already been finalized.'), $redirect);
			}
		}

		// Allow score submissions any time after an hour before the scheduled end time.
		// Some people like to submit via mobile phone immediately, and games can end early.
		if ($game->game_slot->end_time->subHours(1)->isFuture()) {
			throw new ForbiddenRedirectException(__('That game has not yet occurred!'), $redirect);
		}

		return true;
	}

	public function canSubmit_score(IdentityInterface $identity, ContextResource $resource) {
		$game = $resource->resource();
		$submit_by = Configure::read('scoring.score_entry_by');
		switch ($submit_by) {
			case SCORE_BY_CAPTAIN:
				return $identity->isCaptainOf($resource->team_id);
			case SCORE_BY_OFFICIAL:
				return $identity->isOfficialOf($game);
			case SCORE_BY_BOTH:
				return $identity->isCaptainOf($resource->team_id) || $identity->isOfficialOf($game);
		}

		return false;
	}

	public function canSubmit_spirit(IdentityInterface $identity, ContextResource $resource) {
		if (!$resource->league->hasSpirit()) {
			return false;
		}

		$game = $resource->resource();
		$submit_by = Configure::read('scoring.spirit_entry_by');
		switch ($submit_by) {
			case SPIRIT_BY_CAPTAIN:
				return $identity->isCaptainOf($resource->team_id);
			case SPIRIT_BY_OFFICIAL:
				return $identity->isOfficialOf($game);
			case SPIRIT_BY_BOTH:
				return $identity->isCaptainOf($resource->team_id) || $identity->isOfficialOf($game);
		}

		return false;
	}

	public function canSubmit_stats(IdentityInterface $identity, ContextResource $resource) {
		if (!$resource->league->hasStats()) {
			throw new ForbiddenRedirectException(__('This league does not have stat tracking enabled.'));
		}
		if (empty($resource->stat_types)) {
			throw new ForbiddenRedirectException(__('This league does not have any entry-type stats selected.'));
		}

		/** @var Game $game */
		$game = $resource->resource();
		if ($game->game_slot->end_time->subHours(1)->isFuture()) {
			throw new ForbiddenRedirectException(__('That game has not yet occurred!'));
		}

		if ($resource->team_id) {
			$team_id = $resource->team_id;
			if ($team_id == $game->home_team_id) {
				$team = $game->home_team;
			} else if ($team_id == $game->away_team_id) {
				$team = $game->away_team;
			} else {
				throw new ForbiddenRedirectException(__('That team is not playing in this game.'), ['action' => 'view', '?' => ['game' => $game->id]]);
			}

			if (!$identity->isManagerOf($game) && !$identity->isCoordinatorOf($game) && !$identity->isOfficialOf($game) && !$identity->isCaptainOf($team)) {
				return false;
			}

			$score_service = new ScoreService($game->score_entries ?? []);
			if (!$game->isFinalized() && !$score_service->hasScoreEntryFrom($team_id)) {
				throw new ForbiddenRedirectException(__('You must submit a score for this game before you can submit stats.'),
					['action' => 'submit', '?' => ['game' => $game->id, 'team' => $team_id]]);
			}

			if ($game->isFinalized()) {
				$status = $game->status;
			} else {
				$status = $score_service->getScoreEntryFrom($team_id)->status;
			}
			if (in_array($status, Configure::read('unplayed_status'))) {
				throw new ForbiddenRedirectException(__('This game was not played.'),
					['action' => 'submit', '?' => ['game' => $game->id, 'team' => $team_id]]);
			}
		} else {
			// Allow specified individuals (referees, umpires, volunteers) to submit stats without a team id
			// TODOLATER: Revisit these permissions: there is currently no restriction on who can be a volunteer
			if (!$identity->isManagerOf($game) && !$identity->isCoordinatorOf($game) /* && !$identity->isVolunteer() && $identity->isOfficial() */) {
				throw new ForbiddenRedirectException(__('You must provide a team ID.'));
			}
		}

		return true;
	}

	public function canStats(IdentityInterface $identity = null, ContextResource $resource) {
		$game = $resource->resource();

		if (!$resource->league->hasStats()) {
			throw new ForbiddenRedirectException(__('This league does not have stat tracking enabled.'), ['action' => 'view', '?' => ['game' => $game->id]]);
		}

		if (!$game->isFinalized()) {
			throw new ForbiddenRedirectException(__('The score of this game has not yet been finalized.'), ['action' => 'view', '?' => ['game' => $game->id]]);
		}

		if ($game->game_slot->start_time->isFuture()) {
			throw new ForbiddenRedirectException(__('This game has not yet started.'), ['action' => 'view', '?' => ['game' => $game->id]]);
		}

		$team_id = $resource->team_id;

		if ($team_id !== null && $team_id != $game->home_team_id && $team_id != $game->away_team_id) {
			throw new ForbiddenRedirectException(__('That team is not playing in this game.'), ['action' => 'view', '?' => ['game' => $game->id]]);
		}

		if (empty($game->stats)) {
			// Default redirect, if nothing better presents itself
			$redirect = ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]];

			// Redirect coordinators to the stats entry page with whatever parameters were used here
			if ($identity && $identity->isCoordinatorOf($game)) {
				$redirect = ['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $team_id]];
			} else if (!$team_id) {
				// If there was no team ID given, check if one of the two teams is captained by the current user
				if ($game->home_team && $identity && $identity->isCaptainOf($game->home_team)) {
					$redirect = ['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->home_team_id]];
				} else if ($game->away_team && $identity && $identity->isCaptainOf($game->away_team)) {
					$redirect = ['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->away_team_id]];
				}
			} else {
				// If there was a team ID given, check if that team is captained by the current user
				if ($team_id == $game->home_team_id && $identity && $identity->isCaptainOf($game->home_team)) {
					$redirect = ['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->home_team_id]];
				} else if ($team_id == $game->away_team_id && $identity && $identity->isCaptainOf($game->away_team)) {
					$redirect = ['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $game->away_team_id]];
				}
			}

			throw new ForbiddenRedirectException(__('No stats have been entered for this game.'), $redirect);
		}

		return Configure::read('feature.public') || ($identity && $identity->isLoggedIn());
	}

	public function canEmail_captains(IdentityInterface $identity, Game $game) {
		return $identity->isManagerOf($game) || $identity->isCoordinatorOf($game) || $identity->isCaptainOf($game);
	}

	public function canUnassign_official(IdentityInterface $identity, Game $game) {
		return !empty($game->officials) && $identity->getIdentifier() == $game->officials[0]->id;
	}
}
