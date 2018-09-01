<?php
/**
 * Class for Rugby sport-specific functionality.
 */
namespace App\Module;

use App\Model\Table\StatsTable;

class SportRugby extends Sport {
	protected $sport = 'rugby';

	public function points_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to points_game', E_USER_ERROR);
		}
		$this->initRostersFromGame($game);

		$t_id = $this->statTypeId('Tries');
		$c_id = $this->statTypeId('Conversions');
		$pk_id = $this->statTypeId('Penalty Kicks');
		$dg_id = $this->statTypeId('Drop Goals');

		foreach ($this->rosters as $team_id => $roster) {
			foreach ($roster as $person_id => $position) {
				$value = $this->value($t_id, $person_id, $game->stats) * 5 + $this->value($c_id, $person_id, $game->stats) * 2 + $this->value($pk_id, $person_id, $game->stats) * 3 + $this->value($dg_id, $person_id, $game->stats) * 3;

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
