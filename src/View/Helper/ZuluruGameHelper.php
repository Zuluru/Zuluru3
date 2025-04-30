<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Authorization\ContextResource;
use App\Controller\AppController;
use App\Model\Entity\Division;
use App\Model\Entity\Game;
use App\Model\Entity\League;
use App\Service\Games\ScoreService;
use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\TextHelper $Text
 * @property ZuluruHtmlHelper $ZuluruHtml
 * @property UserCacheHelper $UserCache
 * @property AuthorizeHelper $Authorize
 */
class ZuluruGameHelper extends Helper {
	public $helpers = ['Html', 'Text', 'ZuluruHtml', 'UserCache', 'Authorize'];

	/**
	 * @param int|bool|null $show_score_for_team The ID of the team to show the score from the perspective of, or false
	 * to not do that.
	 */
	public function score(Game $game, Division $division, $show_score_for_team = false): string
	{
		$swap_score = $show_score_for_team === $game->away_team_id;
		if ($game->isFinalized()) {
			return $this->scoreString($game->status, $division->schedule_type, $game->home_score, $game->away_score, $swap_score);
		}

		$score_service = new ScoreService($game->score_entries ?? []);
		$score_entry = $score_service->getBestScoreEntry();
		if (!empty($score_entry)) {
			if ($score_entry->team_id === $game->home_team_id) {
				$text = $this->scoreString($score_entry->status, $division->schedule_type, $score_entry->score_for, $score_entry->score_against, $swap_score);
			} else {
				$text = $this->scoreString($score_entry->status, $division->schedule_type, $score_entry->score_against, $score_entry->score_for, $swap_score);
			}

			if ($score_entry->status === 'in_progress') {
				$text .= __(' ({0})', __('in progress')) . "\n";
			} else {
				$text .= __(' ({0})', __('unofficial')) . "\n";
			}

			return $text;
		}

		if ($score_entry === null) {
			return __('score mismatch') . "\n";
		}

		if ($game->game_slot->end_time->subHours(1)->isPast()) {
			return __('not entered') . "\n";
		}

		return '';
	}

	private function scoreString(string $status, string $schedule_type, ?int $home_score, ?int $away_score, bool $swap_scores): string
	{
		if (in_array($status, Configure::read('unplayed_status'))) {
			return __($status) . "\n";
		}

		if ($schedule_type === 'competition') {
			$text = $home_score . "\n";
		} else {
			// If scores are being shown from a particular team's perspective, we may need to swap the home and away scores.
			if ($swap_scores) {
				$text = "{$away_score} - {$home_score}\n";
			} else {
				$text = "{$home_score} - {$away_score}\n";
			}
		}

		if (strpos($status, 'default') !== false) {
			$text .= __(' ({0})', __('default')) . "\n";
		}

		return $text;
	}

	public function actions(Game $game, Division $division, League $league, ?bool $show_score_for_team = false): string
	{
		$score_service = new ScoreService($game->score_entries ?? []);
		$score_entry = $score_service->getBestScoreEntry();

		if ($game->home_team_id) {
			$resource = new ContextResource($game, ['team_id' => $game->home_team_id, 'league' => $league, 'stat_types' => $league->stat_types, 'is_captain' => true]);
			$home_links = $this->participant_actions($score_entry, $resource, $game, $game->home_team_id, $score_service, $division);
		} else {
			$home_links = [];
		}

		if ($game->away_team_id) {
			$resource = new ContextResource($game, ['team_id' => $game->away_team_id, 'league' => $league, 'stat_types' => $league->stat_types, 'is_captain' => true]);
			$away_links = $this->participant_actions($score_entry, $resource, $game, $game->away_team_id, $score_service, $division);
		} else {
			$away_links = [];
		}

		$resource = new ContextResource($game, ['team_id' => $game->away_team_id, 'league' => $league, 'stat_types' => $league->stat_types, 'is_official' => true]);
		$official_links = $this->participant_actions($score_entry, $resource, $game, $game->away_team_id, $score_service, $division);

		$links = [];

		if (($this->getView()->getRequest()->getParam('controller') !== 'Games' || $this->getView()->getRequest()->getParam('action') !== 'stats') &&
			$this->Authorize->can('stats', $league)
		) {
			$links[] = $this->ZuluruHtml->iconLink('stats_24.png',
				['controller' => 'Games', 'action' => 'stats', '?' => ['game' => $game->id, 'team' => $show_score_for_team ?? null]],
				['alt' => __('Game Stats'), 'title' => __('Game Stats')]);
		}

		// Give admins, managers and coordinators the option to edit games
		if ($this->Authorize->can('edit', $game)) {
			$links[] = $this->ZuluruHtml->iconLink('edit_24.png',
				['controller' => 'Games', 'action' => 'edit', '?' => ['game' => $game->id, 'return' => AppController::_return()]],
				['alt' => __('Edit'), 'title' => __('Edit')]);
		}

		if ($this->Authorize->can('note', new ContextResource($game, ['home_team' => $game->home_team, 'away_team' => $game->away_team]))) {
			$links[] = $this->Html->link(__('Add Note'), ['controller' => 'Games', 'action' => 'note', '?' => ['game' => $game->id]]);
		}

		if (!empty($home_links) && !empty($away_links)) {
			return $this->Html->tag('span',
				implode("\n", $links) . implode("\n", $official_links) . '<br>' .
				$this->Text->truncate($game->home_team->name ?? '', 18) . ': ' . implode("\n", $home_links) . '<br>' .
				$this->Text->truncate($game->away_team->name ?? '', 18) . ': ' . implode("\n", $away_links),
				['class' => 'actions']
			);
		}

		return $this->Html->tag('span', implode("\n", array_merge($links, $official_links, $home_links, $away_links)), ['class' => 'actions']);
	}

	private function participant_actions($score_entry, ContextResource $resource, Game $game, ?int $team_id, ScoreService $score_service, Division $division): array
	{
		$links = [];

		if ($score_entry && $score_entry->status === 'in_progress' && $this->Authorize->can('live_score', $resource)) {
			$links[] = $this->Html->link(
				__('Live Score'),
				['controller' => 'Games', 'action' => 'live_score', '?' => ['game' => $game->id, 'team' => $team_id]]);
		}

		if ($this->Authorize->can('submit', $resource)) {
			if ($score_service->hasScoreEntryFrom($team_id)) {
				$links[] = $this->Html->link(
					__('Edit Score'),
					['controller' => 'Games', 'action' => 'submit', '?' => ['game' => $game->id, 'team' => $team_id]]);
			} else if ($division->schedule_type === 'competition') {
				$links[] = $this->Html->link(
					__('Submit'),
					['controller' => 'GameSlots', 'action' => 'submit', '?' => ['slot' => $game->game_slot_id]]);
			} else {
				$links[] = $this->Html->link(
					__('Submit'),
					['controller' => 'Games', 'action' => 'submit', '?' => ['game' => $game->id, 'team' => $team_id]]);
			}
		}

		if ($this->Authorize->can('submit_stats', $resource)) {
			$links[] = $this->Html->link(
				__('Submit Stats'),
				['controller' => 'Games', 'action' => 'submit_stats', '?' => ['game' => $game->id, 'team' => $team_id]]);
		}

		if ($game->game_slot->start_time->isFuture()) {
			$player = !$resource->is_official && (
				in_array($team_id, $this->UserCache->read('TeamIDs')) || in_array($team_id, $this->UserCache->read('RelativeTeamIDs'))
			);
			$identity = $this->Authorize->getIdentity();
			$official = $resource->is_official && $identity && $identity->isOfficialOf($game);
			if ($player || $official) {
				$links[] = $this->Html->link(
					__('iCal'),
					['controller' => 'Games', 'action' => 'ical', $game->id, $team_id, 'game.ics']);
			}
		}

		return $links;
	}
}
