<?php
/**
 * Class for Baseball sport-specific functionality.
 */
namespace App\Module;

use App\Model\Entity\Game;
use App\Model\Entity\StatType;
use App\Model\Table\StatsTable;
use Cake\Utility\Hash;

class SportBaseball extends Sport {
	protected $sport = 'baseball';

	public function hits_game(StatType $stat_type, Game $game): void {
		$this->gameSum($stat_type, $game, ['Singles', 'Doubles', 'Triples', 'Home Runs']);
	}

	public function innings_season(StatType $stat_type, \ArrayObject $calculated): void {
		$ip_type = $this->statType('Innings Pitched');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				if (StatsTable::applicable($stat_type, $position) && array_key_exists($person_id, $this->stats) && array_key_exists($ip_type->id, $this->stats[$person_id]['stats'])) {
					$innings = $this->stats[$person_id]['stats'][$ip_type->id];
					$calculated[$person_id][$stat_type['id']] = empty($innings) ? 'N/A' : $this->innings_sum($innings);
				}
			}
		}
	}

	public function era_season(StatType $stat_type, \ArrayObject $calculated): void {
		$er_type = $this->statType('Earned Runs');
		$ip_type = $this->statType('Innings Pitched');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				if (StatsTable::applicable($stat_type, $position) && array_key_exists($person_id, $this->stats) && array_key_exists($ip_type->id, $this->stats[$person_id]['stats'])) {
					$innings = $this->stats[$person_id]['stats'][$ip_type->id];
					if (empty($innings)) {
						$calculated[$person_id][$stat_type['id']] = 'N/A';
					} else {
						$outs = $this->outs($innings);
						$ip = $outs / 3;
						$calculated[$person_id][$stat_type['id']] = sprintf('%.02f', $this->valueSum($er_type, $person_id) * 9 / $ip);
					}
				}
			}
		}
	}

	// Handle the baseball standard of "6.2" meaning "six full innings plus two outs"
	public function outs(array $innings): int {
		$outs = 0;
		foreach ($innings as $i) {
			if (strpos($i, '.') !== false) {
				[$i,$o] = explode('.', $i);
			} else {
				$o = 0;
			}
			$outs += $i * 3 + $o;
		}
		return $outs;
	}

	public function innings_sum(array $innings): float {
		$outs = $this->outs($innings);
		$innings = floor($outs / 3);
		$outs %= 3;
		if ($outs > 0) {
			$innings .= ".$outs";
		}
		return $innings;
	}

	public function ba_season(StatType $stat_type, \ArrayObject $calculated): void {
		$h_type = $this->statType('Hits');
		$ab_type = $this->statType('At Bats');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$d = $this->valueSum($ab_type, $person_id);
				if ($d > 0) {
					$value = sprintf('%.03f', $this->valueSum($h_type, $person_id) / $d);
				} else {
					$value = sprintf('%.03f', 0);
				}
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function obp_season(StatType $stat_type, \ArrayObject $calculated): void {
		$h_type = $this->statType('Hits');
		$bb_type = $this->statType('Walks');
		$hbp_type = $this->statType('Hit By Pitch');
		$sf_type = $this->statType('Sacrifice Flies');
		$ab_type = $this->statType('At Bats');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$reached = $this->valueSum($h_type, $person_id) + $this->valueSum($bb_type, $person_id) + $this->valueSum($hbp_type, $person_id);
				$appearances = $this->valueSum($ab_type, $person_id) + $this->valueSum($bb_type, $person_id) + $this->valueSum($sf_type, $person_id) + $this->valueSum($hbp_type, $person_id);
				if ($appearances > 0) {
					$value = sprintf('%.03f', $reached / $appearances);
				} else {
					$value = sprintf('%.03f', 0);
				}
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function slg_season(StatType $stat_type, \ArrayObject $calculated): void {
		$b1_type = $this->statType('Singles');
		$b2_type = $this->statType('Doubles');
		$b3_type = $this->statType('Triples');
		$b4_type = $this->statType('Home Runs');
		$ab_type = $this->statType('At Bats');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$bases = $this->valueSum($b1_type, $person_id) +
					($this->valueSum($b2_type, $person_id) * 2) +
					($this->valueSum($b3_type, $person_id) * 3) +
					($this->valueSum($b4_type, $person_id) * 4);
				$d = $this->valueSum($ab_type, $person_id);
				if ($d > 0) {
					$value = sprintf('%.03f', $bases / $d);
				} else {
					$value = sprintf('%.03f', 0);
				}
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

	public function ops_season(StatType $stat_type, \ArrayObject $calculated): void {
		$h_type = $this->statType('Hits');
		$b1_type = $this->statType('Singles');
		$b2_type = $this->statType('Doubles');
		$b3_type = $this->statType('Triples');
		$b4_type = $this->statType('Home Runs');
		$bb_type = $this->statType('Walks');
		$hbp_type = $this->statType('Hit By Pitch');
		$sf_type = $this->statType('Sacrifice Flies');
		$ab_type = $this->statType('At Bats');

		foreach ($this->rosters as $roster) {
			foreach ($roster as $person_id => $position) {
				$bases = $this->valueSum($b1_type, $person_id) +
					($this->valueSum($b2_type, $person_id) * 2) +
					($this->valueSum($b3_type, $person_id) * 3) +
					($this->valueSum($b4_type, $person_id) * 4);
				$reached = $this->valueSum($h_type, $person_id) + $this->valueSum($bb_type, $person_id) + $this->valueSum($hbp_type, $person_id);
				$appearances = $this->valueSum($ab_type, $person_id) + $this->valueSum($bb_type, $person_id) + $this->valueSum($sf_type, $person_id) + $this->valueSum($hbp_type, $person_id);
				$at_bats = $this->valueSum($ab_type, $person_id);
				if ($appearances > 0) {
					if ($at_bats > 0) {
						$value = sprintf('%.03f', $reached / $appearances + $bases / $at_bats);
					} else {
						$value = sprintf('%.03f', $reached / $appearances);
					}
				} else {
					$value = sprintf('%.03f', 0);
				}
				if (StatsTable::applicable($stat_type, $position) || $value != 0) {
					$calculated[$person_id][$stat_type['id']] = $value;
				}
			}
		}
	}

}
