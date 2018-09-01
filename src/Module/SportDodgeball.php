<?php
/**
 * Class for Dodgeball sport-specific functionality.
 */
namespace App\Module;

use App\Model\Table\StatsTable;

class SportDodgeball extends Sport {
	protected $sport = 'dodgeball';

	public function points_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to points_game', E_USER_ERROR);
		}
		$this->initRostersFromGame($game);

		$kp_id = $this->statTypeId('Kills');
		$km_id = $this->statTypeId('Killed');
		$cp_id = $this->statTypeId('Catches');
		$cm_id = $this->statTypeId('Caught');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($kp_id, $person_id, $game->stats) - $this->value($km_id, $person_id, $game->stats)
					+ ($this->value($cp_id, $person_id, $game->stats) - $this->value($cm_id, $person_id, $game->stats)) * 2;

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
