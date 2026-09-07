<?php
/**
 * Class for Dodgeball sport-specific functionality.
 */
namespace App\Module;

use App\Model\Entity\Game;
use App\Model\Entity\StatType;
use App\Model\Table\StatsTable;

class SportDodgeball extends Sport {
	protected $sport = 'dodgeball';

	public function points_game(StatType $stat_type, Game $game): void {
		$this->initRostersFromGame($game);

		$kp_type = $this->statType('Kills');
		$km_type = $this->statType('Killed');
		$cp_type = $this->statType('Catches');
		$cm_type = $this->statType('Caught');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($kp_type, $person_id, $game->stats) - $this->value($km_type, $person_id, $game->stats)
					+ ($this->value($cp_type, $person_id, $game->stats) - $this->value($cm_type, $person_id, $game->stats)) * 2;

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
