<?php
namespace App\View\Helper;

use App\Authorization\ContextResource;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property ZuluruHtmlHelper $ZuluruHtml
 * @property UserCacheHelper $UserCache
 * @property AuthorizeHelper $Authorize
 */
class ZuluruGameHelper extends Helper {
	public $helpers = ['Html', 'ZuluruHtml', 'UserCache', 'Authorize'];

	public function displayScore($game, $division, $league, $show_score_for_team = false): string {
		$text = '';
		$identity = $this->Authorize->getIdentity();

		// Check if one of the teams involved in the game is a team the current user is a captain of
		$teams = array_intersect([$game->home_team_id, $game->away_team_id], $this->UserCache->read('OwnedTeamIDs'));
		$team_id = array_pop($teams);

		$links = [];
		if ($game->isFinalized()) {
			if (in_array($game->status, Configure::read('unplayed_status'))) {
				$text .= __($game->status) . "\n";
			} else {
				if ($division->schedule_type === 'competition') {
					if ($show_score_for_team !== null) {
						$text .= $game->home_score . "\n";
					}
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
					$text .= "{$first_score} - {$second_score}\n";
				}
				if (strpos($game->status, 'default') !== false) {
					$text .= __(' ({0})', __('default')) . "\n";
				}

				if ($identity) {
					try {
						if ($identity->can('submit_stats', new ContextResource($game, ['team_id' => $team_id, 'league' => $league, 'stat_types' => $league->stat_types]))) {
							$links[] = $this->Html->link(
								__('Submit Stats'),
								['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $team_id]]);
						}
					} catch (\Authorization\Exception\Exception $ex) {
						// No problem, just don't show the link.
					}

					try {
						if (($this->getView()->getRequest()->getParam('controller') !== 'Games' ||
							$this->getView()->getRequest()->getParam('action') !== 'stats') && $identity->can('stats', $league)
						) {
							$links[] = $this->ZuluruHtml->iconLink('stats_24.png',
								['controller' => 'Games', 'action' => 'stats', '?' => ['game' => $game->id, 'team' => $show_score_for_team]],
								['alt' => __('Game Stats'), 'title' => __('Game Stats')]);
						}
					} catch (\Authorization\Exception\Exception $ex) {
						// No problem, just don't show the link.
					}
				}
			}
		} else {
			$score_entry = $game->getBestScoreEntry();
			if (!empty($score_entry)) {
				if (in_array($score_entry->status, Configure::read('unplayed_status'))) {
					$text .= __($score_entry->status) . "\n";
				} else {
					if ($division->schedule_type === 'competition') {
						if ($show_score_for_team !== null) {
							$text .= $score_entry->score_for . "\n";
						}
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
						$text .= "{$first_score} - {$second_score}\n";
					}
				}

				if ($team_id) {
					if ($score_entry->status === 'in_progress') {
						$links[] = $this->Html->link(
							__('Live Score'),
							['controller' => 'Games', 'action' => 'live_score', '?' => ['game' => $game->id, 'team' => $team_id]]);
					} else if ($score_entry->team_id == $team_id) {
						$links[] = $this->Html->link(
							__('Edit Score'),
							['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $team_id]]);
					} else {
						$links[] = $this->Html->link(
							__('Submit'),
							['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $team_id]]);
					}

					// Check if someone is a captain on both teams that played each other
					$second_team_id = array_pop($teams);
					if ($second_team_id) {
						$links[] = $this->Html->link(
							__('Submit'),
							['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $second_team_id]]);
					}
				/* TODOLATER: Re-enable these options when live scoring is working again
				} else if ($score_entry->status == 'in_progress' && $identity && $identity->can('live_score', $game)) {
					$links[] = $this->Html->link(
						__('Live Score'),
						['controller' => 'Games', 'action' => 'live_score', '?' => ['game' => $game->id]]);
				} else if ($identity && $identity->can('edit', $game)) {
					$links[] = $this->Html->link(
						__('Edit Score'),
						['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id]]);
				*/
				}

				if ($score_entry->status === 'in_progress') {
					$text .= __(' ({0})', __('in progress')) . "\n";
				} else {
					$text .= __(' ({0})', __('unofficial')) . "\n";
				}
			} else if ($score_entry === null) {
				$text .= __('score mismatch') . "\n";

				if ($team_id) {
					$links[] = $this->Html->link(
						__('Edit Score'),
						['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $team_id]]);
				}
			} else if ($game->game_slot->end_time->subHours(1)->isPast()) {
				if ($division->schedule_type !== 'competition') {
					// Allow score submissions any time after an hour before the scheduled end time.
					// Some people like to submit via mobile phone immediately, and games can end early.
					if ($team_id && $identity->can('submit_score', $game)) {
						$links[] = $this->Html->link(
							__('Submit'),
							['controller' => 'Games', 'action' => 'submit_score', '?' => ['game' => $game->id, 'team' => $team_id]]);
					} else {
						$text .= __('not entered') . "\n";
					}
				} else if ($identity) {
					try {
						if ($identity->can('submit_score', $game)) {
							$links[] = $this->Html->link(
								__('Submit'),
								['controller' => 'GameSlots', 'action' => 'submit_score', '?' => ['slot' => $game->game_slot_id]]);
						}
					} catch (\Authorization\Exception\Exception $ex) {
						// No problem, just don't show the link.
					}
				}
/* TODOLATER: Add live scoring link back in, once the feature is re-implemented
			} else if ($game->game_slot->start_time->subMinutes(30)->isPast()) {
				if ($game->home_team_id != null && $game->away_team_id != null) {
					// Allow live scoring to start up to half an hour before scheduled game start time.
					// This allows score keepers to get the page loaded and ready to go in advance.
					if ($team_id) {
						$links[] = $this->Html->link(
							__('Live Score'),
							['controller' => 'Games', 'action' => 'live_score', '?' => ['game' => $game->id, 'team' => $team_id]]);
					} else if ($identity && $identity->can('live_score', $game)) {
						$links[] = $this->Html->link(
							__('Live Score'),
							['controller' => 'Games', 'action' => 'live_score', '?' => ['game' => $game->id]]);
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
		try {
			if ($identity && $identity->can('edit', $game)) {
				$links[] = $this->ZuluruHtml->iconLink('edit_24.png',
					['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id, 'return' => AppController::_return()]],
					['alt' => __('Edit'), 'title' => __('Edit')]);
			}
		} catch (\Authorization\Exception\Exception $ex) {
			// No problem, just don't show the link.
		}

		$text .= $this->Html->tag('span', implode("\n", $links), ['class' => 'actions']);

		return $text;
	}
}
