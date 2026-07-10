<?php
/**
 * Class for Soccer sport-specific functionality.
 */
namespace App\Module;

use App\Model\Entity\Game;
use App\Model\Entity\StatType;
use App\Model\Table\StatsTable;

class SportSoccer extends Sport {
	protected $sport = 'soccer';

	// In soccer, a win is worth 3 points, not 2.
	public function winValue(): int {
		return 3;
	}

	public function points_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);

		$g_type = $this->statType('Goals');
		$a_type = $this->statType('Assists');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($g_type, $person_id, $game->stats) * 2 + $this->value($a_type, $person_id, $game->stats);

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

	public function shot_percent_game(StatType $stat_type, Game $game): void {
		$this->gamePercent($stat_type, $game, $this->statType('Goals'), $this->statType('Shots'));
	}

	public function shot_percent_season(StatType $stat_type, \ArrayObject $calculated): void {
		$this->seasonPercent($stat_type, $calculated, $this->statType('Goals'), $this->statType('Shots'));
	}

	public function save_percent_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);

		$s_type = $this->statType('Shots Against');
		$g_type = $this->statType('Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$shots = $this->value($s_type, $person_id, $game->stats);
				if ($shots) {
					$value = round(($shots - $this->value($g_type, $person_id, $game->stats)) / $shots, 3);
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

	public function save_percent_season(StatType $stat_type, \ArrayObject $calculated): void {
		$s_type = $this->statType('Shots Against');
		$g_type = $this->statType('Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$shots = $this->valueSum($s_type, $person_id);
				if ($shots) {
					$value = round(($shots - $this->valueSum($g_type, $person_id)) / $shots, 3);
				} else {
					$value = 0;
				}

				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function gaa_season(StatType $stat_type, \ArrayObject $calculated): void {
		$m_type = $this->statType('Minutes Played');
		$g_type = $this->statType('Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$minutes = $this->valueSum($m_type, $person_id);
				if ($minutes) {
					$value = round(($this->valueSum($g_type, $person_id) * 90) / $minutes, 2);
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
