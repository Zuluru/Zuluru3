<?php
/**
 * Class for Hockey sport-specific functionality.
 */
namespace App\Module;

use App\Model\Entity\Game;
use App\Model\Entity\StatType;
use App\Model\Table\StatsTable;

class SportHockey extends Sport {
	protected $sport = 'hockey';

	public function points_game(StatType $stat_type, Game $game): void {
		$this->gameSum($stat_type, $game, ['Goals', 'Assists']);
	}

	public function shot_percent_game(StatType $stat_type, Game $game): void {
		$this->gamePercent($stat_type, $game, $this->statType('Goals'), $this->statType('Shots'));
	}

	public function shot_percent_season(StatType $stat_type, \ArrayObject $calculated): void {
		$this->seasonPercent($stat_type, $calculated, $this->statType('Goals'), $this->statType('Shots'));
	}

	public function faceoff_percent_game(StatType $stat_type, Game $game): void {
		$this->gamePercent($stat_type, $game, $this->statType('Faceoffs Won'), $this->statType('Faceoffs'));
	}

	public function faceoff_percent_season(StatType $stat_type, \ArrayObject $calculated): void {
		$this->seasonPercent($stat_type, $calculated, $this->statType('Faceoffs Won'), $this->statType('Faceoffs'));
	}

	public function shutouts_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);

		$m_type = $this->statType('Minutes Played');
		$evg_type = $this->statType('Even Strength Goals Against');
		$ppg_type = $this->statType('Power Play Goals Against');
		$shg_type = $this->statType('Shorthanded Goals Against');
		$eng_type = $this->statType('Empty Net Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$minutes = $this->value($m_type, $person_id, $game->stats);
				if ($minutes) {
					$goals = $this->value($evg_type, $person_id, $game->stats) + $this->value($ppg_type, $person_id, $game->stats) + $this->value($shg_type, $person_id, $game->stats) + $this->value($eng_type, $person_id, $game->stats);
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

	public function goals_against_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);

		$evg_type = $this->statType('Even Strength Goals Against');
		$ppg_type = $this->statType('Power Play Goals Against');
		$shg_type = $this->statType('Shorthanded Goals Against');
		$eng_type = $this->statType('Empty Net Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($evg_type, $person_id, $game->stats) + $this->value($ppg_type, $person_id, $game->stats) + $this->value($shg_type, $person_id, $game->stats) + $this->value($eng_type, $person_id, $game->stats);

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

	public function save_percent_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);

		$s_type = $this->statType('Shots Against');
		$evg_type = $this->statType('Even Strength Goals Against');
		$ppg_type = $this->statType('Power Play Goals Against');
		$shg_type = $this->statType('Shorthanded Goals Against');
		$eng_type = $this->statType('Empty Net Goals Against');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$shots = $this->value($s_type, $person_id, $game->stats);
				if ($shots) {
					$goals = $this->value($evg_type, $person_id, $game->stats) + $this->value($ppg_type, $person_id, $game->stats) + $this->value($shg_type, $person_id, $game->stats) + $this->value($eng_type, $person_id, $game->stats);
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
					$value = round(($this->valueSum($g_type, $person_id) * 60) / $minutes, 2);
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
