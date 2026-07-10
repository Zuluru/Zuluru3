<?php
/**
 * Class for Ultimate sport-specific functionality.
 */
namespace App\Module;

use App\Model\Entity\Game;
use App\Model\Entity\StatType;
use App\Model\Entity\Team;
use Cake\Utility\Hash;

class SportUltimate extends Sport {
	protected $sport = 'ultimate';

	public function TODOLATER_validatePlay(Team $team, string $play, int $score_from, array $details) {
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

	public function points_game(StatType $stat_type, Game $game): void {
		$this->gameSum($stat_type, $game, ['Goals', 'Assists', 'Second Assists']);
	}

	public function turnovers_game(StatType $stat_type, Game $game): void {
		$this->gameSum($stat_type, $game, ['Throwaways', 'Drops']);
	}
}
