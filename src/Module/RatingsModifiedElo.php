<?php
/**
 * Derived class for implementing functionality for the modified Elo ratings calculator.
 */
namespace App\Module;

class RatingsModifiedElo extends Ratings {

	public function calculateRatingsChange($home_score, $away_score, $expected_win) {
		$weight_constant = 40; // All games weighted equally
		$score_weight = 1; // Games start with a weight of 1

		$game_value = 1; // Game value is always 1 or 0.5 as we're calculating the elo change for the winning team

		// Find winning/losing scores.  In the case of a tie,
		// the home team is considered the winner for purposes of
		// rating calculation.  This has nothing to do with the
		// tiebreakers used for standings purposes as in tie cases,
		// the $elo_change will work out the same regardless of which team is
		// considered the 'winner'
		if ($home_score == $away_score) {
			// For a tie, we assume the home team wins, but give the game a
			// value of 0.5
			$game_value = 0.5;
		}

		// Calculate score differential bonus.
		// If the difference is greater than 1/3 the winning score, the bonus
		// added is the ratio of score difference over winning score.
		$score_diff = abs($home_score - $away_score);
		$score_max = max($home_score, $away_score);
		if ($score_max && (($score_diff / $score_max) > (1/3))) {
			$score_weight += $score_diff / $score_max;
		}

		$elo_change = ceil($weight_constant * $score_weight * ($game_value - $expected_win));
		return $elo_change;
	}

}
