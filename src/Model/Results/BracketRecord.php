<?php
/**
 * Entity-like class for managing a team's bracket record
 */

namespace App\Model\Results;

class BracketRecord {
	public $pool_id = 0;
	public $results = [];
	public $placement = null;

	public function addResult($score_for, $score_against, $pool_id, $round, $placement) {
		$this->pool_id = $pool_id;

		// Check if this was a placement game
		if ($placement) {
			$final_win = $placement;
			$final_lose = $placement + 1;
		} else {
			$final_win = $final_lose = null;
		}

		// What type of result was this?
		if ($score_for > $score_against) {
			$this->results[$round] = 1;
			$this->placement = $final_win;
		} else if ($score_for < $score_against) {
			$this->results[$round] = -1;
			$this->placement = $final_lose;
		} else {
			$this->results[$round] = 0;
			$this->placement = $final_win;
		}
	}
}
