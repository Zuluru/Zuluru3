<?php
/**
 * Class for Soccer sport-specific functionality.
 */
namespace App\Module;

use App\Model\Table\StatsTable;

class SportSoccer extends Sport {
	protected $sport = 'soccer';

	// In soccer, a win is worth 3 points, not 2.
	public function winValue() {
		return 3;
	}

	public function points_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to points_game', E_USER_ERROR);
		}
		$this->initRostersFromGame($game);

		$g_id = $this->statTypeId('Goals');
		$a_id = $this->statTypeId('Assists');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($g_id, $person_id, $game->stats) * 2 + $this->value($a_id, $person_id, $game->stats);

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$game->stats[] = $this->Stats->newEntity([
						'game_id' => $game->id,
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type->id,
						'value' => $value,
					]);
				}
			}
		}
	}

	public function shot_percent_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to shot_percent_game', E_USER_ERROR);
		}
		$this->gamePercent($stat_type, $game, $this->statTypeId('Goals'), $this->statTypeId('Shots'));
	}

	public function shot_percent_season($stat_type, $calculated) {
		$this->seasonPercent($stat_type, $calculated, $this->statTypeId('Goals'), $this->statTypeId('Shots'));
	}

	public function save_percent_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to save_percent_game', E_USER_ERROR);
		}
		$this->initRostersFromGame($game);

		$s_id = $this->statTypeId('Shots Against');
		$g_id = $this->statTypeId('Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$shots = $this->value($s_id, $person_id, $game->stats);
				if ($shots) {
					$value = round(($shots - $this->value($g_id, $person_id, $game->stats)) / $shots, 3);
				} else {
					$value = 0;
				}

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$game->stats[] = $this->Stats->newEntity([
						'game_id' => $game->id,
						'team_id' => $team_id,
						'person_id' => $person_id,
						'stat_type_id' => $stat_type->id,
						'value' => $value,
					]);
				}
			}
		}
	}

	public function save_percent_season($stat_type, $calculated) {
		$s_id = $this->statTypeId('Shots Against');
		$g_id = $this->statTypeId('Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$shots = $this->valueSum($s_id, $person_id);
				if ($shots) {
					$value = round(($shots - $this->valueSum($g_id, $person_id)) / $shots, 3);
				} else {
					$value = 0;
				}

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function gaa_season($stat_type, $calculated) {
		$m_id = $this->statTypeId('Minutes Played');
		$g_id = $this->statTypeId('Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$minutes = $this->valueSum($m_id, $person_id);
				if ($minutes) {
					$value = round(($this->valueSum($g_id, $person_id) * 90) / $minutes, 2);
				} else {
					$value = 0;
				}

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

}
