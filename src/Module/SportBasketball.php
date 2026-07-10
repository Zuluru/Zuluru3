<?php
/**
 * Class for Basketball sport-specific functionality.
 */
namespace App\Module;

use App\Model\Entity\Game;
use App\Model\Entity\StatType;
use App\Model\Table\StatsTable;

class SportBasketball extends Sport {
	protected $sport = 'basketball';

	public function points_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);

		$fg_type = $this->statType('Field Goals Made');
		$ft_type = $this->statType('Free Throws Made');
		$tpfg_type = $this->statType('Three-point Field Goals Made');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($fg_type, $person_id, $game->stats) * 2 + $this->value($ft_type, $person_id, $game->stats) + $this->value($tpfg_type, $person_id, $game->stats) * 3;

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

	public function rebounds_game(StatType $stat_type, Game $game): void {
		$this->gameSum($stat_type, $game, ['Offensive Rebounds', 'Defensive Rebounds']);
	}

	public function fg_percent_game(StatType $stat_type, Game $game): void {
		$this->gamePercent($stat_type, $game, $this->statType('Field Goals Made'), $this->statType('Field Goals Attempted'));
	}

	public function fg_percent_season(StatType $stat_type, \ArrayObject $calculated): void {
		$this->seasonPercent($stat_type, $calculated, $this->statType('Field Goals Made'), $this->statType('Field Goals Attempted'));
	}

	public function ft_percent_game(StatType $stat_type, Game $game): void {
		$this->gamePercent($stat_type, $game, $this->statType('Free Throws Made'), $this->statType('Free Throws Attempted'));
	}

	public function ft_percent_season(StatType $stat_type, \ArrayObject $calculated): void {
		$this->seasonPercent($stat_type, $calculated, $this->statType('Free Throws Made'), $this->statType('Free Throws Attempted'));
	}

	public function tpfg_percent_game(StatType $stat_type, Game $game): void {
		$this->gamePercent($stat_type, $game, $this->statType('Three-point Field Goals Made'), $this->statType('Three-point Field Goals Attempted'));
	}

	public function tpfg_percent_season(StatType $stat_type, \ArrayObject $calculated): void {
		$this->seasonPercent($stat_type, $calculated, $this->statType('Three-point Field Goals Made'), $this->statType('Three-point Field Goals Attempted'));
	}

	public function astto_game(StatType $stat_type, Game $game): void {
		$this->gameRatio($stat_type, $game, $this->statType('Assists'), $this->statType('Turnovers'));
	}

	public function astto_season(StatType $stat_type, \ArrayObject $calculated): void {
		$this->seasonRatio($stat_type, $calculated, $this->statType('Assists'), $this->statType('Turnovers'));
	}

	public function efficiency_game(StatType $stat_type, Game $game): void {
		$p_type = $this->statType('Points');
		$r_type = $this->statType('Rebounds');
		$a_type = $this->statType('Assists');
		$s_type = $this->statType('Steals');
		$b_type = $this->statType('Blocks');
		$fgm_type = $this->statType('Field Goals Made');
		$fga_type = $this->statType('Field Goals Attempted');
		$ftm_type = $this->statType('Free Throws Made');
		$fta_type = $this->statType('Free Throws Attempted');
		$tpfgm_type = $this->statType('Three-point Field Goals Made');
		$tpfga_type = $this->statType('Three-point Field Goals Attempted');
		$t_type = $this->statType('Turnovers');

		$this->initRostersFromGame($game);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($p_type, $person_id, $game->stats)
					+ $this->value($r_type, $person_id, $game->stats)
					+ $this->value($a_type, $person_id, $game->stats)
					+ $this->value($s_type, $person_id, $game->stats)
					+ $this->value($b_type, $person_id, $game->stats)
					+ $this->value($fgm_type, $person_id, $game->stats) - $this->value($fga_type, $person_id, $game->stats)
					+ $this->value($ftm_type, $person_id, $game->stats) - $this->value($fta_type, $person_id, $game->stats)
					+ $this->value($tpfgm_type, $person_id, $game->stats) - $this->value($tpfga_type, $person_id, $game->stats)
					- $this->value($t_type, $person_id, $game->stats);
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

	public function efficiency_season(StatType $stat_type, \ArrayObject $calculated): void {
		$p_type = $this->statType('Points');
		$r_type = $this->statType('Rebounds');
		$a_type = $this->statType('Assists');
		$s_type = $this->statType('Steals');
		$b_type = $this->statType('Blocks');
		$fgm_type = $this->statType('Field Goals Made');
		$fga_type = $this->statType('Field Goals Attempted');
		$ftm_type = $this->statType('Free Throws Made');
		$fta_type = $this->statType('Free Throws Attempted');
		$tpfgm_type = $this->statType('Three-point Field Goals Made');
		$tpfga_type = $this->statType('Three-point Field Goals Attempted');
		$t_type = $this->statType('Turnovers');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->valueSum($p_type, $person_id)
					+ $this->valueSum($r_type, $person_id)
					+ $this->valueSum($a_type, $person_id)
					+ $this->valueSum($s_type, $person_id)
					+ $this->valueSum($b_type, $person_id)
					+ $this->valueSum($fgm_type, $person_id) - $this->valueSum($fga_type, $person_id)
					+ $this->valueSum($ftm_type, $person_id) - $this->valueSum($fta_type, $person_id)
					+ $this->valueSum($tpfgm_type, $person_id) - $this->valueSum($tpfga_type, $person_id)
					- $this->valueSum($t_type, $person_id);
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function pir_game(StatType $stat_type, Game $game): void {
		$p_type = $this->statType('Points');
		$r_type = $this->statType('Rebounds');
		$a_type = $this->statType('Assists');
		$s_type = $this->statType('Steals');
		$b_type = $this->statType('Blocks');
		$fd_type = $this->statType('Fouls Drawn');
		$fgm_type = $this->statType('Field Goals Made');
		$fga_type = $this->statType('Field Goals Attempted');
		$ftm_type = $this->statType('Free Throws Made');
		$fta_type = $this->statType('Free Throws Attempted');
		$tpfgm_type = $this->statType('Three-point Field Goals Made');
		$tpfga_type = $this->statType('Three-point Field Goals Attempted');
		$t_type = $this->statType('Turnovers');
		$pf_type = $this->statType('Personal Fouls');
		$sr_type = $this->statType('Shots Rejected');

		$this->initRostersFromGame($game);
		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($p_type, $person_id, $game->stats)
					+ $this->value($r_type, $person_id, $game->stats)
					+ $this->value($a_type, $person_id, $game->stats)
					+ $this->value($s_type, $person_id, $game->stats)
					+ $this->value($b_type, $person_id, $game->stats)
					+ $this->value($fd_type, $person_id, $game->stats)
					+ $this->value($fgm_type, $person_id, $game->stats) - $this->value($fga_type, $person_id, $game->stats)
					+ $this->value($ftm_type, $person_id, $game->stats) - $this->value($fta_type, $person_id, $game->stats)
					+ $this->value($tpfgm_type, $person_id, $game->stats) - $this->value($tpfga_type, $person_id, $game->stats)
					- $this->value($t_type, $person_id, $game->stats)
					- $this->value($pf_type, $person_id, $game->stats)
					- $this->value($sr_type, $person_id, $game->stats);
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

	public function pir_season(StatType $stat_type, \ArrayObject $calculated): void {
		$p_type = $this->statType('Points');
		$r_type = $this->statType('Rebounds');
		$a_type = $this->statType('Assists');
		$s_type = $this->statType('Steals');
		$b_type = $this->statType('Blocks');
		$fd_type = $this->statType('Fouls Drawn');
		$fgm_type = $this->statType('Field Goals Made');
		$fga_type = $this->statType('Field Goals Attempted');
		$ftm_type = $this->statType('Free Throws Made');
		$fta_type = $this->statType('Free Throws Attempted');
		$tpfgm_type = $this->statType('Three-point Field Goals Made');
		$tpfga_type = $this->statType('Three-point Field Goals Attempted');
		$t_type = $this->statType('Turnovers');
		$pf_type = $this->statType('Personal Fouls');
		$sr_type = $this->statType('Shots Rejected');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->valueSum($p_type, $person_id)
					+ $this->valueSum($r_type, $person_id)
					+ $this->valueSum($a_type, $person_id)
					+ $this->valueSum($s_type, $person_id)
					+ $this->valueSum($b_type, $person_id)
					+ $this->valueSum($fd_type, $person_id)
					+ $this->valueSum($fgm_type, $person_id) - $this->valueSum($fga_type, $person_id)
					+ $this->valueSum($ftm_type, $person_id) - $this->valueSum($fta_type, $person_id)
					+ $this->valueSum($tpfgm_type, $person_id) - $this->valueSum($tpfga_type, $person_id)
					- $this->valueSum($t_type, $person_id)
					- $this->valueSum($pf_type, $person_id)
					- $this->valueSum($sr_type, $person_id);
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

}
