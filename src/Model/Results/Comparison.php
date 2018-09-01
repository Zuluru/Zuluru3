<?php
namespace App\Model\Results;

use Cake\ORM\Entity;
use App\Model\Entity\Team;

class Comparison {
	/**
	 * Sort based on round-robin results
	 */
	public static function compareTeamsResults(Team $a, Team $b, array $context = []) {
		return RoundRobinRecord::compare(RoundRobinRecord::record($a, $context), RoundRobinRecord::record($b, $context), $context['tie_breaker']);
	}

	/**
	 * Sort based on one set of tournament results
	 */
	public static function compareTeamsTournamentResults(Team $a, Team $b, array $context = []) {
		return RoundRobinRecord::compareTournament(RoundRobinRecord::record($a, $context), RoundRobinRecord::record($b, $context));
	}

	/**
	 * Various league types might have tournaments.
	 */
	public static function compareTeamsTournament(Team $a, Team $b, array $context) {
		// If both teams have bracket results, we may be able to use that
		if (!empty($a->_results->bracket) && !empty($b->_results->bracket)) {
			// If both teams have final placements, we use that
			if ($a->_results->bracket->final !== null && $b->_results->bracket->final !== null) {
				if ($a->_results->bracket->final > $b->_results->bracket->final) {
					return 1;
				} else if ($a->_results->bracket->final < $b->_results->bracket->final) {
					return -1;
				}
			}

			// Go through each round in the bracket and compare the two teams' results in that round
			$rounds = array_unique(array_merge(array_keys($a->_results->bracket->results), array_keys($b->_results->bracket->results)));
			sort($rounds);
			foreach ($rounds as $round) {
				// If the first team had a bye in this round and the second team lost,
				// put the first team ahead
				if (!array_key_exists($round, $a->_results->bracket->results) && $b->_results->bracket->results[$round] < 0) {
					return -1;
				}

				// If the second team had a bye in this round and the first team lost,
				// put the second team ahead
				if (!array_key_exists($round, $a->_results->bracket->results) && $b->_results->bracket->results[$round] < 0) {
					return 1;
				}

				// If both teams played in this round and had different results,
				// use that result to determine who is ahead
				if (array_key_exists($round, $a->_results->bracket->results) && array_key_exists($round, $b->_results->bracket->results) &&
					$a->_results->bracket->results[$round] != $b->_results->bracket->results[$round])
				{
					return ($a->_results->bracket->results[$round] > $b->_results->bracket->results[$round] ? -1 : 1);
				}
			}
		}

		// If both teams have pool results, we may be able to use that
		if (!empty($a->_results->pools) && !empty($b->_results->pools)) {
			$max_stage = max(array_merge(array_keys($a->_results->pools), array_keys($b->_results->pools)));
			for ($stage = $max_stage; $stage > 0; -- $stage) {
				// If teams are not in the same pool, we use that
				if (array_key_exists($stage, $a->_results->pools) && array_key_exists($stage, $b->_results->pools)) {
					$a_pool = current(array_keys($a->_results->pools[$stage]));
					$b_pool = current(array_keys($b->_results->pools[$stage]));
					if ($a_pool < $b_pool) {
						return -1;
					} else if ($a_pool > $b_pool) {
						return 1;
					}

					$ret = RoundRobinRecord::compareTournament($a->_results->pools[$stage][$a_pool], $b->_results->pools[$stage][$b_pool]);
					if ($ret != 0) {
						return $ret;
					}
				}
			}
		}

		return $context['league_obj']->compareTeams($a, $b, array_merge(['tie_breaker' => []], $context));
	}

	/**
	 * Sort based on configured list of tie-breakers
	 */
	public static function compareTeamsTieBreakers(Team $a, Team $b, array $context) {
		// Teams with no season results are ranked lower than those with
		if (empty($a->_results->season) && empty($b->_results->season)) {
			return 0;
		} else if (empty($a->_results->season)) {
			return 1;
		} else if (empty($b->_results->season)) {
			return -1;
		}

		return self::compareTeamsResults($a, $b, $context);
	}

	public static function compareTeamsResultsCrossPool(Team $a, Team $b, array $context) {
		$a_record = RoundRobinRecord::record($a, $context);
		$b_record = RoundRobinRecord::record($b, $context);
		return RoundRobinRecord::compareTournament($a_record, $b_record);
	}

	/**
	 * Go through a list of teams with game results, detect any three (or more)
	 * way ties, and resolve them.
	 *
	 * @param mixed $teams Sorted list of teams, with zero-based array indices
	 */
	public static function detectAndResolveTies(&$teams, $comparison, array $context) {
		$teams = array_values($teams);
		for ($i = 0; $i < count($teams) - 1; ++ $i) {
			$tied = [];
			for ($j = $i + 1; $j < count($teams); ++ $j) {
				if ($comparison($teams[$i], $teams[$j], $context) == 1) {
					// Found two teams that are not in the expected order.
					// They must be tied with at least one other.
					$tied[] = $i;
					$tied[] = $j;
					for ($k = $i + 1; $k < count($teams); ++ $k) {
						if ($j != $k && $comparison($teams[$j], $teams[$k], $context) == 1) {
							$tied[] = $k;
							// We don't need to look for teams tied with the ones we've already found
							$i = max($j, $k) + 1;
						}
					}
					self::resolveTies($teams, $tied, $context);
					$j = max($j, $i + 1);
				}
			}
		}
	}

	private static function resolveTies(&$teams, $tied, array $context) {
		$compare = [];

		foreach ($tied as $i) {
			$compare[$i] = new Entity([
				'hthpm' => 0,
				'pm' => 0,
			]);
			switch ($context['results']) {
				case 'season':
					$record = $teams[$i]->_results->season->rounds[$context['current_round']];
					break;

				case 'pool':
					$record = $teams[$i]->_results->pools[$context['stage']][$context['pool']];
					break;

				case 'stage':
					// Shouldn't ever happen: We use initial seeding to break ties before we ever get here
					trigger_error('Unexpected tie breaker situation', E_USER_ERROR);
					exit;
			}
			if (!empty($record)) {
				foreach ($tied as $j) {
					// Not all teams will have played each other; if they didn't the plus-minus
					// is effectively zero, so no harm in skipping this increment.
					if ($i != $j && array_key_exists($teams[$j]->id, $record->vspm)) {
						$compare[$i]->hthpm += $record->vspm[$teams[$j]->id];
					}
				}
				$compare[$i]->pm = $record->goals_for - $record->goals_against;
				$compare[$i]->initial_seed = $teams[$i]->initial_seed;
			} else {
				// A huge seed will place a team with no results in last place
				$compare[$i]->initial_seed = 10000;
			}
		}
		uasort($compare, 'self::compareHTH');

		// Start the revised list with all teams that were ahead of the tied teams
		$new_teams = array_slice($teams, 0, min($tied));

		// When rounds are not complete, we can have multi-way ties where one team is clearly better
		// then the others, but got lumped into the middle based on overall +/- comparison with a
		// team that they haven't played. We need to deal with these leftovers.
		$leftovers = array_diff(range(min($tied), max($tied)), $tied);
		if (!empty($leftovers)) {
			$sorted = array_keys($compare);
			$best = $teams[array_shift($sorted)];
			foreach ($leftovers as $key => $team) {
				// Is the leftover team better than the best team among those tied?
				if (self::compareTeamsResults($teams[$team], $best, $context) < 1) {
					$new_teams[] = $teams[$team];
					unset($leftovers[$key]);
				}
			}
		}

		// Put the teams into the same order as this new comparison demands
		foreach (array_keys($compare) as $key) {
			$new_teams[] = $teams[$key];
		}

		// Any more leftover teams to deal with?
		foreach ($leftovers as $team) {
			$new_teams[] = $teams[$team];
		}

		// Finish up with all the teams there were behind all the tied teams
		$new_teams += array_slice($teams, max($tied) + 1, null, true);

		$teams = $new_teams;
	}

	private static function compareHTH($a, $b) {
		// First multi-way tie breaker is head-to-head plus minus in games between these teams
		if ($a->hthpm > $b->hthpm)
			return -1;
		if ($a->hthpm < $b->hthpm)
			return 1;

		// Second multi-way tie breaker is overall plus minus
		if ($a->pm > $b->pm)
			return -1;
		if ($a->pm < $b->pm)
			return 1;

		// For lack of a better idea, we'll use initial seed as the final tie breaker
		if ($a->initial_seed < $b->initial_seed)
			return -1;
		if ($a->initial_seed > $b->initial_seed)
			return 1;
	}

}
