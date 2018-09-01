<?php
/**
 * Class for Ultimate sport-specific functionality.
 */
namespace App\Module;

class SportUltimate extends Sport {
	protected $sport = 'ultimate';

	public function TODOLATER_validatePlay($team, $play, $score_from, $details) {
		switch ($play) {
			case 'Half':
				$half = Hash::extract(['X' => $details], '/X[play=Half]/.');
				if (!empty($half)) {
					return __('Second half was already started.');
				}
				$start = Hash::extract(['X' => $details], '/X[play=Start]/.');
				if (empty($start)) {
					return __('This game apparently hasn\'t started yet.');
				}
				if ($start[0]['team_id'] == $team) {
					return __('The same team shouldn\'t pull to start both halves.');
				}
				break;
		}
		return parent::validatePlay($team, $play, $score_from, $details);
	}

	public function points_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to points_game', E_USER_ERROR);
		}
		$this->gameSum($stat_type, $game, ['Goals', 'Assists', 'Second Assists']);
	}

	public function turnovers_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to turnovers_game', E_USER_ERROR);
		}
		$this->gameSum($stat_type, $game, ['Throwaways', 'Drops']);
	}
}
