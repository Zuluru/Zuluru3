<?php
/**
 * Class for Rugby sport-specific functionality.
 */
namespace App\Module;

use App\Model\Entity\Game;
use App\Model\Entity\StatType;
use App\Model\Table\StatsTable;

class SportRugby extends Sport {
	protected $sport = 'rugby';

	public function points_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);

		$t_type = $this->statType('Tries');
		$c_type = $this->statType('Conversions');
		$pk_type = $this->statType('Penalty Kicks');
		$dg_type = $this->statType('Drop Goals');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($t_type, $person_id, $game->stats) * 5 + $this->value($c_type, $person_id, $game->stats) * 2 + $this->value($pk_type, $person_id, $game->stats) * 3 + $this->value($dg_type, $person_id, $game->stats) * 3;

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

}
