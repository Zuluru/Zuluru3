<?php
/**
 * Class for Baseball sport-specific functionality.
 */
namespace App\Module;

use App\Model\Table\StatsTable;

class SportBaseball extends Sport {
	protected $sport = 'baseball';

	public function hits_game($stat_type, $game, $todotesting = null) {
		if ($todotesting !== null) {
			trigger_error('stats passed to hits_game', E_USER_ERROR);
		}
		$this->gameSum($stat_type, $game, ['Singles', 'Doubles', 'Triples', 'Home Runs']);
	}

	public function TODOSECOND_innings_season($stat_type, $calculated) {
		$ip_id = $this->statTypeId('Innings Pitched');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$innings = Hash::extract($calculated, "/Stat[stat_type_id=$ip_id][person_id=$person_id]/value");
				if (empty($innings)) {
					$value = 'N/A';
				} else {
					$value = $this->innings($innings);
				}
				if (StatsTable::applicable($stat_type, $position) || $value != 'N/A') {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function TODOSECOND_era_season($stat_type, $calculated) {
		$er_id = $this->statTypeId('Earned Runs');
		$ip_id = $this->statTypeId('Innings Pitched');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$innings = Hash::extract($calculated, "/Stat[stat_type_id=$ip_id][person_id=$person_id]/value");
				if (empty($innings)) {
					$value = 'N/A';
				} else {
					$outs = $this->outs($innings);
					$ip = $outs / 3;
					$value = sprintf('%.02f', $this->valueSum($er_id, $person_id) * 9 / $ip);
				}
				if (StatsTable::applicable($stat_type, $position) || $value != 'N/A') {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	// Handle the baseball standard of "6.2" meaning "six full innings plus two outs"
	public function outs($innings) {
		$outs = 0;
		foreach ($innings as $i) {
			if (strpos($i, '.') !== false) {
				list($i,$o) = explode('.', $i);
			} else {
				$o = 0;
			}
			$outs += $i * 3 + $o;
		}
		return $outs;
	}

	public function innings_sum($innings) {
		$outs = $this->outs($innings);
		$innings = floor($outs / 3);
		$outs %= 3;
		if ($outs > 0) {
			$innings .= ".$outs";
		}
		return $innings;
	}

	public function ba_season($stat_type, $calculated) {
		$h_id = $this->statTypeId('Hits');
		$ab_id = $this->statTypeId('At Bats');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$value = sprintf('%.03f', $this->valueSum($h_id, $person_id) / $this->valueSum($ab_id, $person_id));
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function obp_season($stat_type, $calculated) {
		$h_id = $this->statTypeId('Hits');
		$bb_id = $this->statTypeId('Walks');
		$hbp_id = $this->statTypeId('Hit By Pitch');
		$sf_id = $this->statTypeId('Sacrifice Flies');
		$ab_id = $this->statTypeId('At Bats');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$reached = $this->valueSum($h_id, $person_id) + $this->valueSum($bb_id, $person_id) + $this->valueSum($hbp_id, $person_id);
				$appearances = $this->valueSum($ab_id, $person_id) + $this->valueSum($bb_id, $person_id) + $this->valueSum($sf_id, $person_id) + $this->valueSum($hbp_id, $person_id);
				$value = sprintf('%.03f', $reached / $appearances);
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function slg_season($stat_type, $calculated) {
		$b1_id = $this->statTypeId('Singles');
		$b2_id = $this->statTypeId('Doubles');
		$b3_id = $this->statTypeId('Triples');
		$b4_id = $this->statTypeId('Home Runs');
		$ab_id = $this->statTypeId('At Bats');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$bases = $this->valueSum($b1_id, $person_id) +
					($this->valueSum($b2_id, $person_id) * 2) +
					($this->valueSum($b3_id, $person_id) * 3) +
					($this->valueSum($b4_id, $person_id) * 4);
				$value = sprintf('%.03f', $bases / $this->valueSum($ab_id, $person_id));
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function ops_season($stat_type, $calculated) {
		$h_id = $this->statTypeId('Hits');
		$b1_id = $this->statTypeId('Singles');
		$b2_id = $this->statTypeId('Doubles');
		$b3_id = $this->statTypeId('Triples');
		$b4_id = $this->statTypeId('Home Runs');
		$bb_id = $this->statTypeId('Walks');
		$hbp_id = $this->statTypeId('Hit By Pitch');
		$sf_id = $this->statTypeId('Sacrifice Flies');
		$ab_id = $this->statTypeId('At Bats');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$bases = $this->valueSum($b1_id, $person_id) +
					($this->valueSum($b2_id, $person_id) * 2) +
					($this->valueSum($b3_id, $person_id) * 3) +
					($this->valueSum($b4_id, $person_id) * 4);
				$reached = $this->valueSum($h_id, $person_id) + $this->valueSum($bb_id, $person_id) + $this->valueSum($hbp_id, $person_id);
				$appearances = $this->valueSum($ab_id, $person_id) + $this->valueSum($bb_id, $person_id) + $this->valueSum($sf_id, $person_id) + $this->valueSum($hbp_id, $person_id);
				$value = sprintf('%.03f', $reached / $appearances + $bases / $this->valueSum($ab_id, $person_id));
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

}
