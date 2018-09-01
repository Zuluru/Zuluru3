<?php
/**
 * Derived class for implementing functionality for the wager ratings calculator.
 */
namespace App\Module;

class RatingsWager extends Ratings {

	public function calculateRatingsChange($home_score, $away_score, $expected_win) {
		// Total wager value varies based on score
		// High scoring games increase the wager value
		$wager = max($home_score, $away_score) * 2 + 10;

		$winner_wager = ceil($wager * $expected_win);

		if ($home_score == $away_score) {
			$winner_gain = $wager / 2;
		} else if ($home_score > $away_score) {
			$winner_gain = $wager - $away_score;
		} else {
			$winner_gain = $wager - $home_score;
		}

		return $winner_gain - $winner_wager;
	}

}
