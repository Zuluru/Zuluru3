<?php
namespace App\View\Helper;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\View\Helper;

class ZuluruGameHelper extends Helper {
	public $helpers = ['Html', 'ZuluruHtml', 'Session', 'UserCache'];

	public function displayScore($game, $division, $league, $show_score_for_team = false) {
		$is_coordinator = in_array($game->division_id, $this->UserCache->read('DivisionIDs'));

		// Check if one of the teams involved in the game is a team the current user is a captain of
		$teams = array_intersect([$game->home_team_id, $game->away_team_id], $this->UserCache->read('OwnedTeamIDs'));
		$team_id = array_pop($teams);

		$links = [];
		if ($game->isFinalized()) {
			if (in_array($game->status, Configure::read('unplayed_status'))) {
				echo __($game->status) . "\n";
			} else {
				if ($division->schedule_type == 'competition') {
					echo $game->home_score . "\n";
				} else {
					// If scores are being shown from a particular team's perspective,
					// we may need to swap the home and away scores.
					if ($show_score_for_team == $game->away_team_id) {
						$first_score = $game->away_score;
						$second_score = $game->home_score;
					} else {
						$first_score = $game->home_score;
						$second_score = $game->away_score;
					}
					echo "{$first_score} - {$second_score}\n";
				}
				if (strpos($game->status, 'default') !== false) {
					echo ' (' . __('default') . ')' . "\n";
				}

				if ($league->hasStats()) {
					if ($team_id || Configure::read('Perm.is_admin') || $is_coordinator) {
						$links[] = $this->Html->link(
							__('Submit Stats'),
							['controller' => 'Games', 'action' => 'submit_stats', 'game' => $game->id, 'team' => $team_id]);
					}
					if (($this->request->getParam('controller') != 'Games' || $this->request->getParam('action') != 'stats') && (Configure::read('Perm.is_logged_in') || Configure::read('feature.public'))) {
						$links[] = $this->ZuluruHtml->iconLink('stats_24.png',
							['controller' => 'Games', 'action' => 'stats', 'game' => $game->id, 'team' => $show_score_for_team],
							['alt' => __('Game Stats'), 'title' => __('Game Stats')]);
					}
				}
			}
		} else {
			$score_entry = $game->getBestScoreEntry();
			if (!empty($score_entry)) {
				if (in_array($score_entry->status, Configure::read('unplayed_status'))) {
					echo __($score_entry->status) . "\n";
				} else {
					if ($division->schedule_type == 'competition') {
						echo $score_entry->score_for . "\n";
					} else {
						// If scores are being shown from a particular team's perspective,
						// we may need to swap the home and away scores.
						if ($show_score_for_team == $score_entry->team_id ||
							($show_score_for_team === false && $score_entry->team_id == $game->home_team_id))
						{
							$first_score = $score_entry->score_for;
							$second_score = $score_entry->score_against;
						} else {
							$first_score = $score_entry->score_against;
							$second_score = $score_entry->score_for;
						}
						echo "{$first_score} - {$second_score}\n";
					}
				}

				if ($team_id) {
					if ($score_entry->status == 'in_progress') {
						$links[] = $this->Html->link(
							__('Live Score'),
							['controller' => 'Games', 'action' => 'live_score', 'game' => $game->id, 'team' => $team_id]);
					} else if ($score_entry->team_id == $team_id) {
						$links[] = $this->Html->link(
							__('Edit score'),
							['controller' => 'Games', 'action' => 'submit_score', 'game' => $game->id, 'team' => $team_id]);
					} else {
						$links[] = $this->Html->link(
							__('Submit'),
							['controller' => 'Games', 'action' => 'submit_score', 'game' => $game->id, 'team' => $team_id]);
					}

					// Check if someone is a captain on both teams that played each other
					$second_team_id = array_pop($teams);
					if ($second_team_id) {
						$links[] = $this->Html->link(
							__('Submit'),
							['controller' => 'Games', 'action' => 'submit_score', 'game' => $game->id, 'team' => $second_team_id]);
					}
				} else if (Configure::read('Perm.is_volunteer') || Configure::read('Perm.is_official')) {
					/* TODOLATER: Revisit these permissions: there is currently no restriction on who can be a volunteer
					// Allow specified individuals (referees, umpires, volunteers) to live score without a team id
					if ($score_entry->status == 'in_progress') {
						$links[] = $this->Html->link(
							__('Live Score'),
							['controller' => 'Games', 'action' => 'live_score', 'game' => $game->id]);
					} else {
						$links[] = $this->Html->link(
							__('Edit score'),
							['controller' => 'Games', 'action' => 'edit', 'game' => $game->id]);
					}
					*/
				}

				if ($score_entry->status == 'in_progress') {
					echo ' (' . __('in progress') . ')' . "\n";
				} else {
					echo ' (' . __('unofficial') . ')' . "\n";
				}
			} else if ($score_entry === null) {
				echo __('score mismatch') . "\n";

				if ($team_id) {
					if ($score_entry->status == 'in_progress') {
						$links[] = $this->Html->link(
							__('Live Score'),
							['controller' => 'Games', 'action' => 'live_score', 'game' => $game->id, 'team' => $team_id]);
					} else {
						$links[] = $this->Html->link(
							__('Edit score'),
							['controller' => 'Games', 'action' => 'submit_score', 'game' => $game->id, 'team' => $team_id]);
					}
				}
			} else if ($game->game_slot->end_time->subHour()->isPast()) {
				if ($division->schedule_type != 'competition') {
					// Allow score submissions any time after an hour before the scheduled end time.
					// Some people like to submit via mobile phone immediately, and games can end early.
					if ($team_id) {
						$links[] = $this->Html->link(
							__('Submit'),
							['controller' => 'Games', 'action' => 'submit_score', 'game' => $game->id, 'team' => $team_id]);
					} else {
						echo __('not entered') . "\n";
					}
				} else if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator) {
					$links[] = $this->Html->link(
						__('Submit'),
						['controller' => 'GameSlots', 'action' => 'submit_score', 'slot' => $game->game_slot_id]);
				}
/* TODOLATER: Add live scoring link back in, once the feature is re-implemented
			} else if ($game->game_slot->start_time->subMinutes(30)->isPast()) {
				if ($game->home_team_id != null && $game->away_team_id != null) {
					// Allow live scoring to start up to half an hour before scheduled game start time.
					// This allows score keepers to get the page loaded and ready to go in advance.
					if ($team_id) {
						$links[] = $this->Html->link(
							__('Live Score'),
							['controller' => 'Games', 'action' => 'live_score', 'game' => $game->id, 'team' => $team_id]);
					} else if (Configure::read('Perm.is_volunteer') || Configure::read('Perm.is_official')) {
						/* TODOLATER: Revisit these permissions: there is currently no restriction on who can be a volunteer
						// Allow specified individuals (referees, umpires, volunteers) to live score without a team id
						$links[] = $this->Html->link(
							__('Live Score'),
							['controller' => 'Games', 'action' => 'live_score', 'game' => $game->id]);
						*
					}
				}
*/
			} else {
				// Check if one of the teams involved in the game is a team the current user is on
				$player_team_ids = array_intersect([$game->home_team_id, $game->away_team_id], $this->UserCache->read('TeamIDs'));
				if (empty($player_team_ids)) {
					$player_team_ids = array_intersect([$game->home_team_id, $game->away_team_id], $this->UserCache->read('RelativeTeamIDs'));
				}
				if (!empty($player_team_ids)) {
					$links[] = $this->Html->link(
						__('iCal'),
						['controller' => 'Games', 'action' => 'ical', $game->id, current($player_team_ids), 'game.ics']);
				}
			}
		}

		// Give admins, managers and coordinators the option to edit games
		if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator) {
			$links[] = $this->ZuluruHtml->iconLink('edit_24.png',
				['controller' => 'Games', 'action' => 'edit', 'game' => $game->id, 'return' => AppController::_return()],
				['alt' => __('Edit'), 'title' => __('Edit')]);
		}

		echo $this->Html->tag('span', implode("\n", $links), ['class' => 'actions']);

	}
}
