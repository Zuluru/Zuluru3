<?php
/**
 * Derived class for implementing functionality for the RRI ratings calculator.
 */
namespace App\Module;

class RatingsRri extends RatingsKrach {

	// The sole difference between KRACH and RRI is that KRACH gives a
	// 1 for a win, 0.5 for a tie, and 0 for a loss, while RRI uses
	// statistics to take into account the final score.
	protected function odds($home_score, $away_score) {
		$winning_score = max($home_score, $away_score);
		$losing_score = min($home_score, $away_score);

		$sum = 0;
		// TODOLATER: Double check results; outcomes look weird
		$p = $winning_score / ($winning_score + $losing_score);
		for ($i = 0; $i < $winning_score; ++ $i) {
			$sum += $this->binomial_coeff($winning_score - 1 + $i, $i) * pow($p, $winning_score) * pow(1-$p, $i);
		}

		if ($home_score >= $away_score) {
			return $sum;
		} else {
			return 1 - $sum;
		}
	}

	protected function binomial_coeff($n, $k) {
		$j = $res = 1;

		if ($k < 0 || $k > $n)
			return 0;
		if (($n - $k) < $k)
			$k = $n - $k;

		while ($j <= $k) {
			$res *= $n--;
			$res /= $j++;
		}

		return $res;
	}

}
