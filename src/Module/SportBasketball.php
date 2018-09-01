<?php
/**
 * Class for Basketball sport-specific functionality.
 */
namespace App\Module;

use App\Model\Table\StatsTable;

class SportBasketball extends Sport {
	protected $sport = 'basketball';

	public function points_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to points_game', E_USER_ERROR);
		}
		$this->initRostersFromGame($game);

		$fg_id = $this->statTypeId('Field Goals Made');
		$ft_id = $this->statTypeId('Free Throws Made');
		$tpfg_id = $this->statTypeId('Three-point Field Goals Made');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($fg_id, $person_id, $game->stats) * 2 + $this->value($ft_id, $person_id, $game->stats) + $this->value($tpfg_id, $person_id, $game->stats) * 3;

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

	public function rebounds_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to rebounds_game', E_USER_ERROR);
		}
		$this->gameSum($stat_type, $game, ['Offensive Rebounds', 'Defensive Rebounds']);
	}

	public function fg_percent_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to fg_percent_game', E_USER_ERROR);
		}
		$this->gamePercent($stat_type, $game, $this->statTypeId('Field Goals Made'), $this->statTypeId('Field Goals Attempted'));
	}

	public function fg_percent_season($stat_type, $calculated) {
		$this->seasonPercent($stat_type, $calculated, $this->statTypeId('Field Goals Made'), $this->statTypeId('Field Goals Attempted'));
	}

	public function ft_percent_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to ft_percent_game', E_USER_ERROR);
		}
		$this->gamePercent($stat_type, $game, $this->statTypeId('Free Throws Made'), $this->statTypeId('Free Throws Attempted'));
	}

	public function ft_percent_season($stat_type, $calculated) {
		$this->seasonPercent($stat_type, $calculated, $this->statTypeId('Free Throws Made'), $this->statTypeId('Free Throws Attempted'));
	}

	public function tpfg_percent_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to tpfg_percent_game', E_USER_ERROR);
		}
		$this->gamePercent($stat_type, $game, $this->statTypeId('Three-point Field Goals Made'), $this->statTypeId('Three-point Field Goals Attempted'));
	}

	public function tpfg_percent_season($stat_type, $calculated) {
		$this->seasonPercent($stat_type, $calculated, $this->statTypeId('Three-point Field Goals Made'), $this->statTypeId('Three-point Field Goals Attempted'));
	}

	public function astto_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to astto_game', E_USER_ERROR);
		}
		$this->gameRatio($stat_type, $game, $this->statTypeId('Assists'), $this->statTypeId('Turnovers'));
	}

	public function astto_season($stat_type, $calculated) {
		$this->seasonRatio($stat_type, $calculated, $this->statTypeId('Assists'), $this->statTypeId('Turnovers'));
	}

	public function efficiency_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stas passed to efficiency_game', E_USER_ERROR);
		}
		$p_id = $this->statTypeId('Points');
		$r_id = $this->statTypeId('Rebounds');
		$a_id = $this->statTypeId('Assists');
		$s_id = $this->statTypeId('Steals');
		$b_id = $this->statTypeId('Blocks');
		$fgm_id = $this->statTypeId('Field Goals Made');
		$fga_id = $this->statTypeId('Field Goals Attempted');
		$ftm_id = $this->statTypeId('Free Throws Made');
		$fta_id = $this->statTypeId('Free Throws Attempted');
		$tpfgm_id = $this->statTypeId('Three-point Field Goals Made');
		$tpfga_id = $this->statTypeId('Three-point Field Goals Attempted');
		$t_id = $this->statTypeId('Turnovers');

		$this->initRostersFromGame($game);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($p_id, $person_id, $game->stats)
					+ $this->value($r_id, $person_id, $game->stats)
					+ $this->value($a_id, $person_id, $game->stats)
					+ $this->value($s_id, $person_id, $game->stats)
					+ $this->value($b_id, $person_id, $game->stats)
					+ $this->value($fgm_id, $person_id, $game->stats) - $this->value($fga_id, $person_id, $game->stats)
					+ $this->value($ftm_id, $person_id, $game->stats) - $this->value($fta_id, $person_id, $game->stats)
					+ $this->value($tpfgm_id, $person_id, $game->stats) - $this->value($tpfga_id, $person_id, $game->stats)
					- $this->value($t_id, $person_id, $game->stats);
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

	public function efficiency_season($stat_type, $calculated) {
		$p_id = $this->statTypeId('Points');
		$r_id = $this->statTypeId('Rebounds');
		$a_id = $this->statTypeId('Assists');
		$s_id = $this->statTypeId('Steals');
		$b_id = $this->statTypeId('Blocks');
		$fgm_id = $this->statTypeId('Field Goals Made');
		$fga_id = $this->statTypeId('Field Goals Attempted');
		$ftm_id = $this->statTypeId('Free Throws Made');
		$fta_id = $this->statTypeId('Free Throws Attempted');
		$tpfgm_id = $this->statTypeId('Three-point Field Goals Made');
		$tpfga_id = $this->statTypeId('Three-point Field Goals Attempted');
		$t_id = $this->statTypeId('Turnovers');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->valueSum($p_id, $person_id)
					+ $this->valueSum($r_id, $person_id)
					+ $this->valueSum($a_id, $person_id)
					+ $this->valueSum($s_id, $person_id)
					+ $this->valueSum($b_id, $person_id)
					+ $this->valueSum($fgm_id, $person_id) - $this->valueSum($fga_id, $person_id)
					+ $this->valueSum($ftm_id, $person_id) - $this->valueSum($fta_id, $person_id)
					+ $this->valueSum($tpfgm_id, $person_id) - $this->valueSum($tpfga_id, $person_id)
					- $this->valueSum($t_id, $person_id);
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function pir_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to pir_game', E_USER_ERROR);
		}
		$p_id = $this->statTypeId('Points');
		$r_id = $this->statTypeId('Rebounds');
		$a_id = $this->statTypeId('Assists');
		$s_id = $this->statTypeId('Steals');
		$b_id = $this->statTypeId('Blocks');
		$fd_id = $this->statTypeId('Fouls Drawn');
		$fgm_id = $this->statTypeId('Field Goals Made');
		$fga_id = $this->statTypeId('Field Goals Attempted');
		$ftm_id = $this->statTypeId('Free Throws Made');
		$fta_id = $this->statTypeId('Free Throws Attempted');
		$tpfgm_id = $this->statTypeId('Three-point Field Goals Made');
		$tpfga_id = $this->statTypeId('Three-point Field Goals Attempted');
		$t_id = $this->statTypeId('Turnovers');
		$pf_id = $this->statTypeId('Personal Fouls');
		$sr_id = $this->statTypeId('Shots Rejected');

		$this->initRostersFromGame($game);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($p_id, $person_id, $game->stats)
					+ $this->value($r_id, $person_id, $game->stats)
					+ $this->value($a_id, $person_id, $game->stats)
					+ $this->value($s_id, $person_id, $game->stats)
					+ $this->value($b_id, $person_id, $game->stats)
					+ $this->value($fd_id, $person_id, $game->stats)
					+ $this->value($fgm_id, $person_id, $game->stats) - $this->value($fga_id, $person_id, $game->stats)
					+ $this->value($ftm_id, $person_id, $game->stats) - $this->value($fta_id, $person_id, $game->stats)
					+ $this->value($tpfgm_id, $person_id, $game->stats) - $this->value($tpfga_id, $person_id, $game->stats)
					- $this->value($t_id, $person_id, $game->stats)
					- $this->value($pf_id, $person_id, $game->stats)
					- $this->value($sr_id, $person_id, $game->stats);
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

	public function pir_season($stat_type, $calculated) {
		$p_id = $this->statTypeId('Points');
		$r_id = $this->statTypeId('Rebounds');
		$a_id = $this->statTypeId('Assists');
		$s_id = $this->statTypeId('Steals');
		$b_id = $this->statTypeId('Blocks');
		$fd_id = $this->statTypeId('Fouls Drawn');
		$fgm_id = $this->statTypeId('Field Goals Made');
		$fga_id = $this->statTypeId('Field Goals Attempted');
		$ftm_id = $this->statTypeId('Free Throws Made');
		$fta_id = $this->statTypeId('Free Throws Attempted');
		$tpfgm_id = $this->statTypeId('Three-point Field Goals Made');
		$tpfga_id = $this->statTypeId('Three-point Field Goals Attempted');
		$t_id = $this->statTypeId('Turnovers');
		$pf_id = $this->statTypeId('Personal Fouls');
		$sr_id = $this->statTypeId('Shots Rejected');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->valueSum($p_id, $person_id)
					+ $this->valueSum($r_id, $person_id)
					+ $this->valueSum($a_id, $person_id)
					+ $this->valueSum($s_id, $person_id)
					+ $this->valueSum($b_id, $person_id)
					+ $this->valueSum($fd_id, $person_id)
					+ $this->valueSum($fgm_id, $person_id) - $this->valueSum($fga_id, $person_id)
					+ $this->valueSum($ftm_id, $person_id) - $this->valueSum($fta_id, $person_id)
					+ $this->valueSum($tpfgm_id, $person_id) - $this->valueSum($tpfga_id, $person_id)
					- $this->valueSum($t_id, $person_id)
					- $this->valueSum($pf_id, $person_id)
					- $this->valueSum($sr_id, $person_id);
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

}
