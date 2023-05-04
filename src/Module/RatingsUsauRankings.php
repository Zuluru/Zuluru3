<?php
/**
 * Derived class for implementing functionality for the USA Ultimate College ratings calculator.
 */
namespace App\Module;

use App\Model\Entity\Division;

class RatingsUsauRankings extends Ratings {
	protected $per_game_ratings = false;
	protected $iterations = 100;

	protected function recalculateGameRatings() {
		// Clear each team's list of game results
		foreach ($this->teams as $team) {
			$team->points_list = [];
		}

		foreach ($this->games as $game) {
			$winning_score = max($game->home_score, $game->away_score);
			$losing_score = min($game->home_score, $game->away_score);
			$differential = $this->differential($winning_score, $losing_score);
			$score_weight = min(1, sqrt(
				($winning_score + max($losing_score, floor(($winning_score - 1) / 2))) / 19
			));

			if ($game->home_score == $winning_score) {
				$winner = $this->teams[$game->home_team_id];
				$loser = $this->teams[$game->away_team_id];
			} else {
				$winner = $this->teams[$game->away_team_id];
				$loser = $this->teams[$game->home_team_id];
			}

			$can_ignore = $winning_score > ($losing_score * 2 + 1) &&
				$winner->current_rating - $loser->current_rating > 600;

			$winner->points_list[] = [
				'points' => $loser->current_rating + $differential,
				'score_weight' => $score_weight,
				'can_ignore' => $can_ignore,
			];

			$loser->points_list[] = [
				'points' => $winner->current_rating - $differential,
				'score_weight' => $score_weight,
				'can_ignore' => $can_ignore,
			];
		}

		foreach ($this->teams as $team) {
			if (!empty($team->points_list)) {
				$team->current_rating = $this->consolidate($team->points_list);
				//\Cake\Log\Log::write('error', "{$team->current_rating} {$team->name}");
			}
		}
	}

	protected function differential($winning_score, $losing_score): int {
		if ($winning_score == $losing_score) {
			return 0;
		}

		$r = ($winning_score > 1) ? $losing_score / ($winning_score - 1) : 0;
		$m = min(1, (1 - $r) / 0.5) * 0.4 * M_PI;
		return 125 + intval(round(475 * sin($m) / sin(0.4 * M_PI)));
	}

	protected function consolidate(array $results) {
		while (count($results) > 5) {
			$removable = collection($results)->match(['can_ignore' => true])->toArray();
			if (empty($removable)) {
				break;
			}

			//\Cake\Log\Log::write('error', $results);
			//\Cake\Log\Log::write('error', $removable);
			break;
		}

		$date_weight = 0.5;
		$date_multiple = pow(2, 1 / (count($results) - 1));

		//$date_weight = 1;
		//$date_multiple = 1;

		$sum = $divisor = 0;
		foreach ($results as $result) {
			$sum += intval(round($result['points'] * $result['score_weight'] * $date_weight));
			$divisor += $date_weight;
			$date_weight *= $date_multiple;
		}

		return intval(round($sum / $divisor));
	}
}
