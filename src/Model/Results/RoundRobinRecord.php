<?php
/**
 * Entity-like class for managing a team's round-robin record
 */

namespace App\Model\Results;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use App\Model\Entity\Team;

class RoundRobinRecord {
	public $wins = 0;
	public $losses = 0;
	public $ties = 0;
	public $defaults = 0;
	public $points = 0;
	public $games = 0;
	public $spirit_games = 0;

	public $carbon_flip_wins = 0;
	public $carbon_flip_losses = 0;
	public $carbon_flip_ties = 0;
	public $carbon_flip_points = 0;
	public $carbon_flip_games = 0;

	public $goals_for = 0;
	public $goals_against = 0;
	public $streak = 0;
	public $streak_type = '';

	public $spirit = 0;
	public $vs = [];
	public $vspm = [];

	public $rounds = [];

	private $team_id;

	public function __construct($team_id) {
		$this->team_id = $team_id;
	}

	public function addResult($opp_id, $score_for, $score_against, $cf_for, $spirit_for, $league, $spirit_obj, $sport_obj, $default, $played) {
		// What type of result was this?
		if ($score_for > $score_against) {
			++ $this->wins;
			$streak_type = __x('standings', 'W');
			$points = $sport_obj->winValue();
		} else if ($score_for < $score_against) {
			++ $this->losses;
			$streak_type = __x('standings', 'L');
			$points = $sport_obj->lossValue();
		} else {
			++ $this->ties;
			$streak_type = __x('standings', 'T');
			$points = $sport_obj->tieValue();
		}

		if ($default) {
			++ $this->defaults;
			-- $points;
		}
		if ($played) {
			++ $this->carbon_flip_games;
			$this->carbon_flip_points += $cf_for;
			if ($cf_for == 2) {
				++ $this->carbon_flip_wins;
			} else if ($cf_for == 1) {
				++ $this->carbon_flip_ties;
			} else {
				++ $this->carbon_flip_losses;
			}
		}

		// Add the current game
		++ $this->games;
		$this->points += $points;
		$this->goals_for += $score_for;
		$this->goals_against += $score_against;

		// TODO: drop high and low spirit?
		if ($spirit_obj) {
			if ($spirit_for) {
				++ $this->spirit_games;
				if (!$league->numeric_sotg) {
					$this->spirit += $spirit_obj->calculate($spirit_for);
				} else {
					$this->spirit += $spirit_for->entered_sotg;
				}
			}
		}

		$this->vs[$opp_id] += $points;
		$this->vspm[$opp_id] += $score_for - $score_against;

		// Add to the current streak, or reset it
		if ($streak_type == $this->streak_type) {
			++ $this->streak;
		} else {
			$this->streak_type = $streak_type;
			$this->streak = 1;
		}
	}

	public static function compare(RoundRobinRecord $a, RoundRobinRecord $b, $tie_breakers = []) {
		if ($a->points < $b->points) {
			return 1;
		} else if ($a->points > $b->points) {
			return -1;
		}

		foreach ($tie_breakers as $option) {
			switch ($option) {
				case 'hth':
					if (array_key_exists($b->team_id, $a->vs)) {
						// if b is in a's results, a must also exist in b's results, no point checking that
						if ($a->vs[$b->team_id] < $b->vs[$a->team_id]) {
							return 1;
						} else if ($a->vs[$b->team_id] > $b->vs[$a->team_id]) {
							return -1;
						}
					}
					break;

				case 'hthpm':
					if (array_key_exists($b->team_id, $a->vspm)) {
						// if b is in a's results, a must also exist in b's results, no point checking that
						if ($a->vspm[$b->team_id] < $b->vspm[$a->team_id]) {
							return 1;
						} else if ($a->vspm[$b->team_id] > $b->vspm[$a->team_id]) {
							return -1;
						}
					}
					break;

				case 'pm':
					if ($a->goals_for - $a->goals_against < $b->goals_for - $b->goals_against) {
						return 1;
					} else if ($a->goals_for - $a->goals_against > $b->goals_for - $b->goals_against) {
						return -1;
					}
					break;

				case 'gf':
					if ($a->goals_for < $b->goals_for) {
						return 1;
					} else if ($a->goals_for > $b->goals_for) {
						return -1;
					}
					break;

				case 'win':
					if ($a->wins < $b->wins) {
						return 1;
					} else if ($a->wins > $b->wins) {
						return -1;
					}
					break;

				case 'loss':
					if ($a->losses > $b->losses) {
						return 1;
					} else if ($a->losses < $b->losses) {
						return -1;
					}
					break;

				case 'spirit':
					if (!empty($a->spirit_games) && !empty($b->spirit_games)) {
						if ($a->spirit / $a->spirit_games < $b->spirit / $b->spirit_games) {
							return 1;
						} else if ($a->spirit / $a->spirit_games > $b->spirit / $b->spirit_games) {
							return -1;
						}
					}
					break;

				case 'cf':
					if (!empty($a->carbon_flip_games) && !empty($b->carbon_flip_games)) {
						if ($a->carbon_flip_points / $a->carbon_flip_games < $b->carbon_flip_points / $b->carbon_flip_games) {
							return 1;
						} else if ($a->carbon_flip_points / $a->carbon_flip_games > $b->carbon_flip_points / $b->carbon_flip_games) {
							return -1;
						}
					}
					break;
			}
		}

		return 0;
	}

	/**
	 * Sort based on round-robin results, when teams are coming from different pools,
	 * possibly with unequal numbers of teams. Putting a 5-0 team ahead of a 4-0 team
	 * isn't fair!
	 */
	public static function compareTournament(RoundRobinRecord $a, RoundRobinRecord $b) {
		$ret = self::compare($a, $b);
		if ($ret != 0) {
			return $ret;
		}

		if ($a->losses > $b->losses) {
			return 1;
		} else if ($a->losses < $b->losses) {
			return -1;
		}

		if ($a->games && $b->games) {
			if (($a->goals_for - $a->goals_against) / $a->games < ($b->goals_for - $b->goals_against) / $b->games) {
				return 1;
			} else if (($a->goals_for - $a->goals_against) / $a->games > ($b->goals_for - $b->goals_against) / $b->games) {
				return -1;
			}

			if ($a->goals_for / $a->games < $b->goals_for / $b->games) {
				return 1;
			} else if ($a->goals_for / $a->games > $b->goals_for / $b->games) {
				return -1;
			}
		}

		if ($a->spirit_games && $b->spirit_games) {
			if ($a->spirit / $a->spirit_games < $b->spirit / $b->spirit_games) {
				return 1;
			} else if ($a->spirit / $a->spirit_games > $b->spirit / $b->spirit_games) {
				return -1;
			}
		}

		// For lack of a better idea, we'll use initial seed as the final tie breaker
		if ($a->initial_seed < $b->initial_seed) {
			return -1;
		} else if ($a->initial_seed > $b->initial_seed) {
			return 1;
		}

		return 0;
	}

	/**
	 * @param $team Team entity with game results parsed into it.
	 * @param $context array describing the desired record to retrieve.
	 *         - 'results' must always be set, to 'season', 'pools' or 'brackets'.
	 *         - For the 'season' record, if 'current_round' is set, it will try that round
	 *           but fall back to the season-to-date results if not found. If 'round' is set,
	 *           it will *only* try that.
	 *         - For the 'pools' record, 'stage' and 'round' must both be set.
	 *         - If the requested record is not found, it will return a default record (all
	 *           zeroes) unless 'default' is set to false, in which case null will be returned;
	 *           this should be used only when requesting a record for display, not sorting.
	 * @return RoundRobinRecord|null
	 */
	public static function record(Team $team, array $context) {
		if (empty($context['results'])) {
			trigger_error('TODOTESTING', E_USER_WARNING);
			exit;
		}

		switch ($context['results']) {
			case 'season':
				if ($team->has('_results') && $team->_results->has('season')) {
					$record = $team->_results->season;
					if (array_key_exists('current_round', $context)) {
						if (array_key_exists($context['current_round'], $record->rounds)) {
							return $record->rounds[$context['current_round']];
						} else {
							return $record;
						}
					} else if (array_key_exists('round', $context)) {
						if (array_key_exists($context['round'], $record->rounds)) {
							return $record->rounds[$context['round']];
						}
					} else {
						return $record;
					}
				}
				break;

			case 'stage':
				if ($team->has('_results') && $team->_results->has('pools') &&
					array_key_exists($context['stage'], $team->_results->pools)
				) {
					if (count($team->_results->pools[$context['stage']]) > 1) {
						trigger_error('TODOTESTING', E_USER_WARNING);
						exit;
					}
					return current($team->_results->pools[$context['stage']]);
				}
				break;

			case 'pool':
				if ($team->has('_results') && $team->_results->has('pools') &&
					array_key_exists($context['stage'], $team->_results->pools) &&
					array_key_exists($context['pool'], $team->_results->pools[$context['stage']])
				) {
					return $team->_results->pools[$context['stage']][$context['pool']];
				}
				break;

			case 'brackets':
				// Nothing needs this, yet...
				trigger_error('Sorting based on tournament bracket results is not yet implemented', E_USER_ERROR);
				exit;
		}

		if (!array_key_exists('default', $context) || $context['default']) {
			$record = new RoundRobinRecord($team->id);
			return $record;
		} else {
			return null;
		}
	}

}
