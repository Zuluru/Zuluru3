<?php
/**
 * Derived class for implementing functionality for the USA Ultimate College ratings calculator.
 */
namespace App\Module;

use App\Model\Entity\Division;

class RatingsUsauCollege extends Ratings {
	protected $per_game_ratings = false;

	protected function initializeRatings(Division $division) {
		if (parent::initializeRatings($division)) {
			foreach ($this->teams as $team) {
				$team->rating_sum = $team->weight_sum = 0;
			}

			return true;
		}
	}

	protected function recalculateGameRatings() {
		// TODOLATER: Don't use the current time, use a fixed time relative to the schedule, so that ratings don't change in the middle of the week when no games have been played. MZU issue.
		// Do we also need to fudge it so that divisions that play on multiple days of the week have the same weight regardless of which day the game happened?
		foreach ($this->games as $game) {
			$days = $game->game_slot->game_date->diffInDays();
			$weight = min(1, 1 / (pow(($days + 4) / 7, 0.4)));

			$this->teams[$game->home_team_id]->rating_sum += $this->calculateRating(
					$game->home_score, $game->away_score,
					$this->teams[$game->away_team_id]->current_rating) * $weight;
			$this->teams[$game->home_team_id]->weight_sum += $weight;

			$this->teams[$game->away_team_id]->rating_sum += $this->calculateRating(
					$game->away_score, $game->home_score,
					$this->teams[$game->home_team_id]->current_rating) * $weight;
			$this->teams[$game->away_team_id]->weight_sum += $weight;
		}

		foreach ($this->teams as $team) {
			if ($team->weight_sum != 0) {
				$team->current_rating = intval($team->rating_sum / $team->weight_sum);
			}
		}
	}

	protected function calculateRating($team_score, $opponent_score, $opponent_rating) {
		if ($team_score == $opponent_score) {
			return $opponent_rating;
		}
		$losing_score = min($team_score, $opponent_score);
		$winning_score = max($team_score, $opponent_score);
		$x = max(0.66, (2.5 * pow($losing_score / $winning_score, 2)));
		if ($team_score > $opponent_score) {
			return $opponent_rating + (400 / $x);
		} else {
			return $opponent_rating - (400 / $x);
		}
	}

}
