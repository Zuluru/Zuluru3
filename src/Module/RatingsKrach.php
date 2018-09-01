<?php
/**
 * Derived class for implementing functionality for the KRACH ratings calculator.
 */
namespace App\Module;

use Cake\Core\Configure;
use App\Model\Table\GamesTable;

class RatingsKrach extends RatingsRpi {
	protected $iterative = true;

	public function calculateExpectedWin ($rating1, $rating2) {
		return $rating1 / ($rating1 + $rating2);
	}

	protected function recalculateGameRatings() {
		if (empty($this->results)) {
			return;
		}

		// Save the current ratings as the initial guess for this round
		foreach ($this->teams as $team) {
			$team->rating_guess = $team->current_rating;
		}

		// Recalculate based on initial guesses
		// TODOLATER: Double check results; outcomes look weird for Krach and RRI; less weird for RPI
		foreach ($this->teams as $team) {
			if (array_key_exists($team->id, $this->results)) {
				$team->current_rating = max(1, $this->results[$team->id]['wins'] * $this->sos($team->id));
			} else {
				// TODOLATER: Is this the right default if no results are present?
				$team->current_rating = 1;
			}
		}
	}

	protected function sos($team_id) {
		$sum = 0;
		$opponents = $this->opponents($team_id);
		foreach ($opponents as $opponent) {
			$sum += $this->vs[$team_id][$opponent]['games'] / ($this->teams[$team_id]->rating_guess + $this->teams[$opponent]->rating_guess);
		}

		return $sum;
	}

	// This will normalize ratings so the average is 1500, similar to other calculators
	protected function finalizeRatings() {
		$ratings = collection($this->teams)->extract('current_rating')->toArray();
		$factor = 1500 / (array_sum($ratings) / count($ratings));
		foreach ($this->teams as $team) {
			$team->current_rating = intval($team->current_rating * $factor);
		}
		parent::finalizeRatings();
	}

}
