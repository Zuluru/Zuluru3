<?php
/**
 * Functions possibly required for ensuring compatibility across various PHP installations.
 */

if (!function_exists ('stats_standard_deviation')) {

	// function to calculate square of value - mean
	function sd_square($x, $mean) { return pow($x - $mean,2); }

	// function to calculate standard deviation (uses sd_square)
	function stats_standard_deviation($array) {
		// square root of sum of squares devided by N-1
		return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
	}

}
