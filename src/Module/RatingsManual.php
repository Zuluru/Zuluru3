<?php
/**
 * Derived class for implementing functionality for the manual ratings calculator.
 */
namespace App\Module;

class RatingsManual extends Ratings {

	public function calculateRatingsChange($home_score, $away_score, $expected_win) {
		// The manually-calculated game rating is entered as the score
		return $home_score;
	}

}
