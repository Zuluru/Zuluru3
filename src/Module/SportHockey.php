<?php
/**
 * Class for Hockey sport-specific functionality.
 */
namespace App\Module;

use App\Model\Table\StatsTable;

class SportHockey extends Sport {
	protected $sport = 'hockey';

	public function points_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to points_game', E_USER_ERROR);
		}
		$this->gameSum($stat_type, $game, ['Goals', 'Assists']);
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

	public function faceoff_percent_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to faceoff_percent_game', E_USER_ERROR);
		}
		$this->gamePercent($stat_type, $game, $this->statTypeId('Faceoffs Won'), $this->statTypeId('Faceoffs'));
	}

	public function faceoff_percent_season($stat_type, $calculated) {
		$this->seasonPercent($stat_type, $calculated, $this->statTypeId('Faceoffs Won'), $this->statTypeId('Faceoffs'));
	}

	public function shutouts_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to shutouts_game', E_USER_ERROR);
		}
		$this->initRostersFromGame($game);

		$m_id = $this->statTypeId('Minutes Played');
		$evg_id = $this->statTypeId('Even Strength Goals Against');
		$ppg_id = $this->statTypeId('Power Play Goals Against');
		$shg_id = $this->statTypeId('Shorthanded Goals Against');
		$eng_id = $this->statTypeId('Empty Net Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$minutes = $this->value($m_id, $person_id, $game->stats);
				if ($minutes) {
					$goals = $this->value($evg_id, $person_id, $game->stats) + $this->value($ppg_id, $person_id, $game->stats) + $this->value($shg_id, $person_id, $game->stats) + $this->value($eng_id, $person_id, $game->stats);
					$value = ($goals == 0 ? 1 : 0);
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

	public function goals_against_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to goals_against_game', E_USER_ERROR);
		}
		$this->initRostersFromGame($game);

		$evg_id = $this->statTypeId('Even Strength Goals Against');
		$ppg_id = $this->statTypeId('Power Play Goals Against');
		$shg_id = $this->statTypeId('Shorthanded Goals Against');
		$eng_id = $this->statTypeId('Empty Net Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($evg_id, $person_id, $game->stats) + $this->value($ppg_id, $person_id, $game->stats) + $this->value($shg_id, $person_id, $game->stats) + $this->value($eng_id, $person_id, $game->stats);

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

	public function save_percent_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to save_percent_game', E_USER_ERROR);
		}
		$this->initRostersFromGame($game);

		$s_id = $this->statTypeId('Shots Against');
		$evg_id = $this->statTypeId('Even Strength Goals Against');
		$ppg_id = $this->statTypeId('Power Play Goals Against');
		$shg_id = $this->statTypeId('Shorthanded Goals Against');
		$eng_id = $this->statTypeId('Empty Net Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$shots = $this->value($s_id, $person_id, $game->stats);
				if ($shots) {
					$goals = $this->value($evg_id, $person_id, $game->stats) + $this->value($ppg_id, $person_id, $game->stats) + $this->value($shg_id, $person_id, $game->stats) + $this->value($eng_id, $person_id, $game->stats);
					$value = round(($shots - $goals) / $shots, 3);
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
					$value = round(($this->valueSum($g_id, $person_id) * 60) / $minutes, 2);
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
