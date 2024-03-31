<?php

/**
 * Derived class for implementing functionality for divisions with tournament scheduling.
 */
namespace App\Module;

use App\Model\Entity\Division;
use App\Model\Entity\Pool;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use App\Exception\ScheduleException;

class LeagueTypeTournament extends LeagueType {
	/**
	 * Define the element to use for rendering various views
	 */
	public $render_element = 'tournament';

	/**
	 * Remember details about the block of games currently being scheduled,
	 * for use when we're scheduling several blocks at once.
	 */
	private $first_team = 0;

	public function poolOptions($num_teams, $stage) {
		$types = [];

		if ($stage == 1) {
			// Add options, depending on the number of teams.
			$min_pools = ceil($num_teams / 12);
			$max_pools = floor($num_teams / 2);
			for ($i = $min_pools; $i <= $max_pools; ++ $i) {
				if ($i == 1) {
					$types["seeded_$i"] = __('single pool with all teams');
				} else {
					$types["seeded_$i"] = __('seeded split into {0} pools', $i);
				}
			}

			if ($num_teams >= 6) {
				// Add some snake seeding options for round-robins to lead into re-seeding
				$max_snake_size = 8;
				$min_snake_size = 3;
				$min_pools = max(2, ceil($num_teams / $max_snake_size));
				$max_pools = floor($num_teams / $min_snake_size);

				for ($pools = $min_pools; $pools <= $max_pools; ++ $pools) {
					$teams = floor($num_teams / $pools);
					$remainder = $num_teams - ($teams * $pools);

					if ($remainder == 0) {
						$types["snake_$pools"] = __('snake seeded split into {0} pools of {1} teams', $pools, $teams);
					} else if ($pools == 2) {
						$types["snake_$pools"] = __('snake seeded split into {0} pools of {1} and {2} teams', $pools, $teams + 1, $teams);
					} else {
						$types["snake_$pools"] = __('snake seeded split into {0} pools ({1} with {2} teams and {3} with {4})', $pools, $remainder, $teams + 1, $pools - $remainder, $teams);
					}
				}
			}
		} else {
			// Add options, depending on the number of teams.
			$min_pools = ceil($num_teams / 12);
			$max_pools = floor($num_teams / 2);
			for ($i = $min_pools; $i <= $max_pools; ++ $i) {
				$types["reseed_$i"] = __('{0} re-seeded power pools', $i);
			}
			$types['crossover'] = __('group of crossover games');
		}

		return $types;
	}

	public function scheduleOptions($num_teams, $stage, $sport) {
		$types = [
			'single' => __('Single blank, unscheduled game (2 teams, one {0})', __(Configure::read("sports.{$sport}.field"))),
		];

		if ($num_teams % 2 == 0) {
			$types['blankset'] = __('Set of blank unscheduled games for all teams in the division ({0} teams, {1} games)', $num_teams, $num_teams / 2);
		} else {
			$types['blankset_bye'] = __('Set of blank unscheduled games for all but one team in the division ({0} teams, {1} games)', $num_teams, ($num_teams - 1) / 2);
			$types['blankset_doubleheader'] = __('Set of blank unscheduled games for all teams in the division, one team will have a double-header ({0} teams, {1} games)', $num_teams, ($num_teams + 1) / 2);
		}

		if ($num_teams >= 3 && $num_teams <= 10) {
			$types['round_robin'] = 'round-robin';
			if ($stage > 1) {
				$types['round_robin_carry_forward'] = __('Round-robin with results from prior-stage matchups carried forward');
			}
		}

		// Add more types, depending on the number of teams
		switch ($num_teams) {
			case 2:
				$types['winner_take_all'] = __('Single game, winner take all');
				$types['home_and_home'] = __('"Home and home" series');
				break;

			case 3:
				$types['playin_three'] = __('Play-in game for 2nd and 3rd; 1st gets a bye to the finals');
				break;

			case 4:
				$types['semis_consolation'] = __('Bracket with semi-finals, finals and 3rd place');
				$types['semis_elimination'] = __('Bracket with semi-finals and finals, no 3rd place');
				break;

			case 5:
				$types['semis_consolation_five'] = __('Bracket with semi-finals and finals, plus a 5th place play-in');
				$types['semis_double_elimination_five'] = __('Bracket with semi-finals and finals, 1st place has double-elimination option, everyone gets 3 games');
				$types['semis_minimal_five'] = __('1st gets a bye to the finals, 4th and 5th place play-in for the bronze');
				break;

			case 6:
				$types['semis_consolation_six'] = __('Bracket with semi-finals and finals, plus 5th and 6th place play-ins');
				$types['semis_double_elimination_six'] = __('Bracket with semi-finals and finals, 1st and 2nd place have double-elimination option, everyone gets 3 games');
				$types['semis_complete_six'] = __('Bracket with semi-finals and finals, plus 5th and 6th place play-ins, everyone gets 3 games');
				$types['semis_minimal_six'] = __('Bracket with semi-finals and finals, 5th and 6th have consolation games, everyone gets 2 games');
				break;

			case 7:
				$types['quarters_consolation_seven'] = __('Bracket with quarter-finals, semi-finals, finals, and all placement games, with a bye every round for whoever should be playing the missing 8th seed');
				$types['quarters_round_robin_seven'] = __('Bracket with play-in quarter-finals for all but the top seed, semi-finals, finals and 3rd place, and a round-robin for the losers of the quarters');
				break;

			case 8:
				$types['quarters_consolation'] = __('Bracket with quarter-finals, semi-finals, finals, and all placement games');
				$types['quarters_bronze'] = __('Bracket with quarter-finals, semi-finals, finals and 3rd place, but no consolation bracket');
				$types['quarters_elimination'] = __('Bracket with quarter-finals, semi-finals and finals, no placement games');
				break;

			case 9:
				$types['quarters_consolation_nine'] = __('Bracket with quarter-finals, semi-finals and finals, plus a 9th place play-in');
				break;

			case 10:
				$types['quarters_consolation_ten'] = __('Bracket with quarter-finals, semi-finals and finals, plus 9th and 10th place play-ins');
				$types['quarters_consolation_ten_plus'] = __('Bracket with quarter-finals, semi-finals and finals, plus 9th and 10th place play-ins, everyone gets at least 3 games');
				$types['presemis_consolation_ten'] = __('Bracket with pre-semi-finals, semi-finals and finals, everyone gets 3 games');
				$types['quarters_shuffle_ten'] = __('Bracket with quarter-finals, semi-finals and finals, bottom 6 get shuffled to minimize duplicates, everyone gets 3 games');
				$types['prequarters_shuffle_ten'] = __('Bracket with quarter-finals, semi-finals and finals, plus 9th and 10th place play-ins, bottom 6 get shuffled to minimize duplicates, everyone gets 4 games');
				break;

			case 11:
				$types['quarters_consolation_eleven'] = __('Bracket with quarter-finals, semi-finals and finals, plus 9th, 10th and 11th place play-ins');
				break;

			case 12:
				$types['quarters_consolation_twelve'] = __('Bracket with quarter-finals, semi-finals and finals, plus 9th-12th place play-ins');
				break;
		}

		return $types;
	}

	public function scheduleRequirements($type, $num_teams) {
		switch($type) {
			case 'single':
				return [1];
			case 'blankset':
				return [$num_teams / 2];
			case 'blankset_bye':
				return [($num_teams - 1) / 2];
			case 'blankset_doubleheader':
				return [($num_teams + 1) / 2];
			case 'round_robin':
				$games = $num_teams * ($num_teams - 1) / 2;
				$games_per_round = floor($num_teams / 2);
				return array_fill(1, $games / $games_per_round, $games_per_round);
			case 'round_robin_carry_forward':
				// TODO: Assumption here is that each team will already have
				// played exactly one other team in the new pool.
				$games = $num_teams * ($num_teams - 1) / 2 - floor($num_teams / 2);
				$games_per_round = floor($num_teams / 2);
				return array_fill(1, $games / $games_per_round, $games_per_round);
			case 'crossover':
			case 'winner_take_all':
				return [1];
			case 'home_and_home':
				return [1, 1];
			case 'playin_three':
				return [1, 1];
			case 'semis_consolation':
				return [2, 2];
			case 'semis_elimination':
				return [2, 1];
			case 'semis_consolation_five':
				return [2, 2, 2];
			case 'semis_double_elimination_five':
				return [2, 1, 2, 1, 1, 2];
			case 'semis_minimal_five':
				return [2, 2];
			case 'semis_double_elimination_six':
			case 'semis_complete_six':
				return [3, 3, 3];
			case 'semis_consolation_six':
				return [2, 2, 3];
			case 'semis_minimal_six':
				return [3, 3];
			case 'quarters_consolation_seven':
				return [3, 3, 3];
			case 'quarters_round_robin_seven':
				return [3, 3, 3, 1];
			case 'quarters_consolation':
				return [4, 4, 4];
			case 'quarters_bronze':
				return [4, 4, 2];
			case 'quarters_elimination':
				return [4, 2, 1];
			case 'quarters_consolation_nine':
				return [1, 4, 4, 4, 1];
			case 'quarters_consolation_ten':
				return [2, 5, 4, 4];
			case 'quarters_consolation_ten_plus':
				return [2, 4, 4, 2, 5];
			case 'presemis_consolation_ten':
			case 'quarters_shuffle_ten':
				return [5, 5, 5];
			case 'prequarters_shuffle_ten':
				return [5, 5, 5, 5];
			case 'quarters_consolation_eleven':
				return [3, 5, 5, 5];
			case 'quarters_consolation_twelve':
				return [6, 6, 6, 6];
		}
	}

	public function schedulePreview(Division $division, Pool $pool = null, $type, $num_teams) {
		// Schedules with only a single round don't warrant a preview
		$requirements = $this->scheduleRequirements($type, $num_teams);
		if (count($requirements) < 2) {
			return null;
		}

		$games = $this->createScheduleBlock($division, $pool);

		$rounds = [];
		foreach ($games as $game) {
			if ($game->home_dependency_type == 'copy') {
				continue;
			}

			switch ($game->home_dependency_type) {
				case 'pool':
				case 'seed':
					$home = collection($pool->pools_teams)->firstMatch(['id' => $game->home_pool_team_id])->alias;
					break;
				case 'game_winner':
					$home = "W{$this->games[$game->home_dependency_id]->display_name}";
					break;
				case 'game_loser':
					$home = "L{$this->games[$game->home_dependency_id]->display_name}";
					break;
			}

			switch ($game->away_dependency_type) {
				case 'pool':
				case 'seed':
					$away = collection($pool->pools_teams)->firstMatch(['id' => $game->away_pool_team_id])->alias;
					break;
				case 'game_winner':
					$away = "W{$this->games[$game->away_dependency_id]->display_name}";
					break;
				case 'game_loser':
					$away = "L{$this->games[$game->away_dependency_id]->display_name}";
					break;
			}

			if (!empty($game->display_name)) {
				$rounds[$game->round][] = "{$game->display_name}: {$home}v{$away}";
			} else {
				$rounds[$game->round][] = "{$home}v{$away}";
			}
		}
		$ret = [];
		foreach ($rounds as $round => $games) {
			$ret[$round] = __('Round') . " $round: " . implode(', ', $games);
		}
		return $ret;
	}

	public function createSchedule(Division $division, Pool $pool) {
		if (!$pool) {
			throw new ScheduleException('Missing expected pool information in tournament scheduler!');
		}

		if (is_array($division->_options->start_date)) {
			$start_date = new FrozenDate(min($division->_options->start_date));
		} else {
			$start_date = $division->_options->start_date;
		}

		$prior_teams = collection($division->pools)->filter(function ($p) use ($pool) {
			return $p->stage == $pool->stage && $p->name < $pool->name;
		})->extract('pools_teams.{*}.alias')->toList();
		$this->first_team = count($prior_teams);

		$this->startSchedule($division, $start_date);
		$games = $this->createScheduleBlock($division, $pool);
		// TODO: Something like the round robin field assignment, where it catches any exception and re-throws with additional information.
		$games = $this->assignFieldsByRound($division, $pool, $games);
		$this->finishSchedule($division, $games);
	}

	public function createScheduleBlock(Division $division, Pool $pool) {
		$this->games = [];
		switch($division->_options->type) {
			case 'single':
				// Create single game
				return [$this->createEmptyGame($division)];
			case 'blankset':
				// Create game for all teams in division
				return $this->createEmptySet($division, $pool);
			case 'blankset_bye':
				// Create game for all teams in division
				return $this->createEmptySet($division, $pool, -1);
			case 'blankset_doubleheader':
				// Create game for all teams in division
				return $this->createEmptySet($division, $pool, 1);
			case 'round_robin':
				$this->createRoundRobin($division, $pool);
				break;
			case 'round_robin_carry_forward':
				$this->createRoundRobin($division, $pool, true);
				break;
			case 'crossover':
				$this->createCrossover($division, $pool);
				break;
			case 'winner_take_all':
				$this->createWinnerTakeAll($division, $pool);
				break;
			case 'home_and_home':
				$this->createHomeAndHome($division, $pool);
				break;
			case 'playin_three':
				$this->createPlayinThree($division, $pool);
				break;
			case 'semis_consolation':
				$this->createSemis($division, $pool, true);
				break;
			case 'semis_elimination':
				$this->createSemis($division, $pool, false);
				break;
			case 'semis_consolation_five':
				$this->createSemisFive($division, $pool, true);
				break;
			case 'semis_double_elimination_five':
				$this->createDoubleEliminationFive($division, $pool, true);
				break;
			case 'semis_minimal_five':
				$this->createSemisFiveMinimal($division, $pool);
				break;
			case 'semis_double_elimination_six':
				$this->createDoubleEliminationSix($division, $pool, true);
				break;
			case 'semis_complete_six':
				$this->createCompleteSix($division, $pool, true);
				break;
			case 'semis_consolation_six':
				$this->createSemisSix($division, $pool, true);
				break;
			case 'semis_minimal_six':
				$this->createMinimalSix($division, $pool, true);
				break;
			case 'quarters_consolation_seven':
				$this->createQuartersSeven($division, $pool, true, true);
				break;
			case 'quarters_round_robin_seven':
				$this->createQuartersRoundRobinSeven($division, $pool, true, true);
				break;
			case 'quarters_consolation':
				$this->createQuarters($division, $pool, true, true);
				break;
			case 'quarters_bronze':
				$this->createQuarters($division, $pool, true, false);
				break;
			case 'quarters_elimination':
				$this->createQuarters($division, $pool, false, false);
				break;
			case 'quarters_consolation_nine':
				$this->createQuartersNine($division, $pool, true, true);
				break;
			case 'quarters_consolation_ten':
				$this->createQuartersTen($division, $pool, true, true);
				break;
			case 'quarters_consolation_ten_plus':
				$this->createQuartersTenPlus($division, $pool, true, true);
				break;
			case 'presemis_consolation_ten':
				$this->createPresemisTen($division, $pool, true, true);
				break;
			case 'quarters_shuffle_ten':
				$this->createQuartersShuffleTen($division, $pool);
				break;
			case 'prequarters_shuffle_ten':
				$this->createPrequartersShuffleTen($division, $pool);
				break;
			case 'quarters_consolation_eleven':
				$this->createQuartersEleven($division, $pool, true, true);
				break;
			case 'quarters_consolation_twelve':
				$this->createQuartersTwelve($division, $pool, true, true);
				break;
		}
		return $this->games;
	}

	/*
	 * Create an empty set of games for this division
	 */
	public function createEmptySet(Division $division, Pool $pool, $team_adjustment = 0) {
		$num_teams = count($division->teams) + $team_adjustment;

		if ($num_teams < 2) {
			throw new ScheduleException(__('Must have two teams.'));
		}

		if ($num_teams % 2) {
			throw new ScheduleException(__('Must have even number of teams.'));
		}

		// Now, create our games.  Don't add any teams, or set a round,
		// or anything, just randomly allocate a game slot.
		$games = [];
		$num_games = $num_teams / 2;
		for ($i = 0; $i < $num_games; ++$i) {
			$games[] = $this->createEmptyGame($division);
		}
		return $games;
	}

	public function createRoundRobin(Division $division, Pool $pool, $carry_forward = false) {
		$teams = range(1, count($pool->pools_teams));
		$num_teams = count($teams);

		if ($num_teams % 2) {
			$teams[] = -1;
			++ $num_teams;
		}

		// For general algorithm details, see the round robin module
		$id = 1;

		// If we are carrying forward results, create those games first
		// This cannot work if there was a round robin, then crossover games, then
		// another round robin with results carried forward, because we don't know
		// what teams will win the crossovers and hence don't know what matchups
		// have already happened. TODO: Detect this situation and abort.
		if ($carry_forward) {
			for ($round = 1; $round < $num_teams; ++ $round) {
				for ($k = 0; $k < ($num_teams / 2); ++ $k) {
					if ($round % 2) {
						$home = $teams[$k];
						$away = $teams[$num_teams - $k - 1];
					} else {
						$home = $teams[$num_teams - $k - 1];
						$away = $teams[$k];
					}
					if ($home != -1 && $away != -1) {
						if ($pool->pools_teams[$home - 1]->dependency_pool_id == $pool->pools_teams[$away - 1]->dependency_pool_id) {
							$this->createTournamentGame($division, $pool, $id, $round, $id, null, POOL_PLAY_GAME, 'copy', $home, 'copy', $away);
							++ $id;
						}
					}
				}
				$teams = $this->rotateAllExceptFirst($teams);
			}
		}

		for ($round = 1; $round < $num_teams; ++ $round) {
			for ($k = 0; $k < ($num_teams / 2); ++ $k) {
				if ($round % 2) {
					$home = $teams[$k];
					$away = $teams[$num_teams - $k - 1];
				} else {
					$home = $teams[$num_teams - $k - 1];
					$away = $teams[$k];
				}
				if ($home != -1 && $away != -1) {
					if (!$carry_forward || $pool->pools_teams[$home - 1]->dependency_pool_id != $pool->pools_teams[$away - 1]->dependency_pool_id) {
						$this->createTournamentGame($division, $pool, $id, $round, $id, null, POOL_PLAY_GAME, 'pool', $home, 'pool', $away);
						++ $id;
					}
				}
			}
			$teams = $this->rotateAllExceptFirst($teams);
		}
	}

	protected function rotateAllExceptFirst($ary) {
		$new_first = array_shift($ary);
		$new_last = array_shift($ary);
		array_push ($ary, $new_last);
		array_unshift ($ary, $new_first);
		return $ary;
	}

	public function createCrossover(Division $division, Pool $pool) {
		$this->createTournamentGame($division, $pool, 1, 1, true, null, POOL_PLAY_GAME, 'pool', 1, 'pool', 2);
	}

	public function createWinnerTakeAll(Division $division, Pool $pool) {
		// Round 1: 1v2
		$this->createTournamentGame($division, $pool, 1, 1, null, $this->first_team + 1, BRACKET_GAME, 'pool', 1, 'pool', 2);
	}

	public function createHomeAndHome(Division $division, Pool $pool) {
		// Round 1: 1v2
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 1, 'pool', 2);

		// Round 2: 2v1
		$this->createTournamentGame($division, $pool, 2, 2, '2', null, BRACKET_GAME, 'pool', 2, 'pool', 1);
	}

	public function createPlayinThree(Division $division, Pool $pool) {
		// Round 1: 2v3
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 2, 'pool', 3);

		// Round 2: 1 v winner
		$this->createTournamentGame($division, $pool, 2, 2, null, $this->first_team + 1, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
	}

	public function createSemis(Division $division, Pool $pool, $consolation) {
		// Round 1: 1v4, 2v3
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 1, 'pool', 4);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 2, 'pool', 3);

		// Round 2: winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 3, 2, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 1, 'game_winner', 2);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 4, 2, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}
	}

	public function createSemisFive(Division $division, Pool $pool, $consolation) {
		// Round 1: 4 vs 5, 2 vs 3
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 2, 'pool', 3);

		// Round 2: 1 vs Winner 1, optional Loser 1 vs Loser 3 - Loser 5th Place
		$this->createTournamentGame($division, $pool, 3, 2, '3', null, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 5, 2, '4', null, BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: Winner 2 vs Winner 3 1st/2nd Place, optional Winner 4 vs Loser 2 3rd/4th Place
		$this->createTournamentGame($division, $pool, 4, 3, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 3, 'game_winner', 2);

		if ($consolation) {
			$this->createTournamentGame($division, $pool, 6, 3, null, $this->first_team + 3, BRACKET_GAME, 'game_winner', 5, 'game_loser', 3);
		}
	}

	public function createDoubleEliminationFive(Division $division, Pool $pool, $consolation) {
		// Round 1: 2 vs 5, 3 vs 4
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 2, 'pool', 5);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 3, 'pool', 4);

		// Round 2: 1 vs Winner 2
		$this->createTournamentGame($division, $pool, 3, 2, '3', null, BRACKET_GAME, 'pool', 1, 'game_winner', 2);

		// Round 3: Winner 1 vs Winner 3, Loser 1 vs Loser 2
		$this->createTournamentGame($division, $pool, 4, 3, '4', null, BRACKET_GAME, 'game_winner', 3, 'game_winner', 1);
		$this->createTournamentGame($division, $pool, 5, 3, '5', null, BRACKET_GAME, 'game_loser', 2, 'game_loser', 1);

		// Round 4: Loser 3 vs Winner 5
		$this->createTournamentGame($division, $pool, 6, 4, '6', null, BRACKET_GAME, 'game_loser', 3, 'game_winner', 5);

		// Round 5: Loser 4 vs Winner 6
		$this->createTournamentGame($division, $pool, 7, 5, '7', $this->first_team + 2, BRACKET_GAME, 'game_loser', 4, 'game_winner', 6);

		// Round 6: Winner 4 vs Winner 7 1st/2nd Place, optional consolation game
		$this->createTournamentGame($division, $pool, 8, 6, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 4, 'game_winner', 7);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 9, 6, null, $this->first_team + 4, BRACKET_GAME, 'game_loser', 5, 'game_loser', 6);
		}
	}

	public function createSemisFiveMinimal(Division $division, Pool $pool) {
		// Round 1: 2v3, 4v5
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 2, 'pool', 3);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 4, 'pool', 5);

		// Round 2: 1st vs winner 1, loser 1 vs winner 2
		$this->createTournamentGame($division, $pool, 3, 2, null, $this->first_team + 1, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$this->createTournamentGame($division, $pool, 4, 2, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 1, 'game_winner', 2);
	}

	public function createCompleteSix(Division $division, Pool $pool, $consolation) {
		// Round 1: 1 vs 6, 2 vs 5, 3 vs 4
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 1, 'pool', 6);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 2, 'pool', 5);
		$this->createTournamentGame($division, $pool, 3, 1, '3', null, BRACKET_GAME, 'pool', 3, 'pool', 4);

		// Round 2: Winner 1 vs Loser 3, Winner 2 vs Winner 3, Loser 1 vs Loser 2
		$this->createTournamentGame($division, $pool, 4, 2, '4', null, BRACKET_GAME, 'game_winner', 1, 'game_loser', 3);
		$this->createTournamentGame($division, $pool, 5, 2, '5', null, BRACKET_GAME, 'game_winner', 2, 'game_winner', 3);
		$this->createTournamentGame($division, $pool, 6, 2, '6', null, BRACKET_GAME, 'game_loser', 2, 'game_loser', 1);

		// Round 3: Winner 4 vs Winner 5 1st/2nd Place, optional consolation games
		$this->createTournamentGame($division, $pool, 7, 3, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 8, 3, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 5, 'game_winner', 6);
			$this->createTournamentGame($division, $pool, 9, 3, null, $this->first_team + 5, BRACKET_GAME, 'game_loser', 4, 'game_loser', 6);
		}
	}

	public function createDoubleEliminationSix(Division $division, Pool $pool, $consolation) {
		// Round 1: 1 vs 2, 4 vs 5, 3 vs 6
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 1, 'pool', 2);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 3, 1, '3', null, BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: Winner 1 vs Winner 2, Loser 1 vs Winner 3, Loser 2 vs Loser 3
		$this->createTournamentGame($division, $pool, 4, 2, '4', null, BRACKET_GAME, 'game_winner', 1, 'game_winner', 2);
		$this->createTournamentGame($division, $pool, 5, 2, '5', null, BRACKET_GAME, 'game_winner', 3, 'game_loser', 1);
		$this->createTournamentGame($division, $pool, 6, 2, '6', null, BRACKET_GAME, 'game_loser', 2, 'game_loser', 3);

		// Round 3: Winner 4 vs Winner 5 1st/2nd Place, optional consolation games
		$this->createTournamentGame($division, $pool, 7, 3, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 8, 3, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 5, 'game_winner', 6);
			$this->createTournamentGame($division, $pool, 9, 3, null, $this->first_team + 5, BRACKET_GAME, 'game_loser', 4, 'game_loser', 6);
		}
	}

	public function createSemisSix(Division $division, Pool $pool, $consolation) {
		// Round 1: 4 vs 5, 3 vs 6
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: 1 vs Winner 1, 2 vs Winner 2
		$this->createTournamentGame($division, $pool, 3, 2, '3', null, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$this->createTournamentGame($division, $pool, 4, 2, '4', null, BRACKET_GAME, 'pool', 2, 'game_winner', 2);

		// Round 3: Winner 3 vs Winner 4 1st/2nd Place, optional Loser 3 vs Loser 4 3rd/4th Place and Loser 1 vs Loser 2 5th/6th Place
		$this->createTournamentGame($division, $pool, 5, 3, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 3, 'game_winner', 4);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 6, 3, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 3, 'game_loser', 4);
			$this->createTournamentGame($division, $pool, 7, 3, null, $this->first_team + 5, BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}
	}

	public function createMinimalSix(Division $division, Pool $pool, $consolation) {
		// Round 1: 1 vs 4, 2 vs 3, 5 vs 6
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 1, 'pool', 4);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 2, 'pool', 3);
		$this->createTournamentGame($division, $pool, 3, 1, '3', null, BRACKET_GAME, 'pool', 5, 'pool', 6);

		// Round 2: Winner 1 vs Winner 2, optional Loser 2 vs Winner 3, Loser 1 vs Loser 3
		$this->createTournamentGame($division, $pool, 4, 2, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 1, 'game_winner', 2);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 5, 2, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 2, 'game_winner', 3);
			$this->createTournamentGame($division, $pool, 6, 2, null, $this->first_team + 5, BRACKET_GAME, 'game_loser', 1, 'game_loser', 3);
		}
	}

	public function createQuartersSeven(Division $division, Pool $pool, $bronze, $consolation) {
		// Round 1: 4 vs 5, 2 vs 7, 3 vs 6
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 2, 'pool', 7);
		$this->createTournamentGame($division, $pool, 3, 1, '3', null, BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: 1 vs Winner 1, other winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 4, 2, '4', null, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$this->createTournamentGame($division, $pool, 5, 2, '5', null, BRACKET_GAME, 'game_winner', 2, 'game_winner', 3);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 6, 2, '6', null, BRACKET_GAME, 'game_loser', 2, 'game_loser', 3);
		}

		// Round 3: more winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 7, 3, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($bronze) {
			$this->createTournamentGame($division, $pool, 8, 3, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
		}
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 9, 3, null, $this->first_team + 5, BRACKET_GAME, 'game_loser', 1, 'game_winner', 6);
		}
	}

	public function createQuartersRoundRobinSeven(Division $division, Pool $pool, $bronze, $consolation) {
		// Round 1: 4 vs 5, 2 vs 7, 3 vs 6
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 2, 'pool', 7);
		$this->createTournamentGame($division, $pool, 3, 1, '3', null, BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: 1 vs Winner 1, Winner 2 vs Winner 3, optional Loser 1 vs Loser 2 - game 1 of round robin for 5th/6th/7th
		$this->createTournamentGame($division, $pool, 4, 2, '4', null, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$this->createTournamentGame($division, $pool, 5, 2, '5', null, BRACKET_GAME, 'game_winner', 2, 'game_winner', 3);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 6, 2, '6', null, BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: Winner 4 vs Winner 5 1st/2nd Place, optional Loser 4 vs Loser 5 3rd/4th Place, optional Loser 1 vs Loser 3 - game 2 of round robin for 5th/6th/7th
		$this->createTournamentGame($division, $pool, 7, 3, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($bronze) {
			$this->createTournamentGame($division, $pool, 8, 3, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
		}
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 9, 3, '7', null, BRACKET_GAME, 'game_loser', 3, 'game_loser', 1);

			// Round 4: Loser 3 vs Loser 2 - game 3 of round robin for 5th/6th/7th
			$this->createTournamentGame($division, $pool, 10, 4, '8', null, BRACKET_GAME, 'game_loser', 2, 'game_loser', 3);
		}
	}

	public function createQuarters(Division $division, Pool $pool, $bronze, $consolation) {
		// Round 1: 1v8, 2v7, etc.
		$this->createTournamentGame($division, $pool, 1, 1, '1', null, BRACKET_GAME, 'pool', 1, 'pool', 8);
		$this->createTournamentGame($division, $pool, 2, 1, '2', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 3, 1, '3', null, BRACKET_GAME, 'pool', 2, 'pool', 7);
		$this->createTournamentGame($division, $pool, 4, 1, '4', null, BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 2: winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 5, 2, '5', null, BRACKET_GAME, 'game_winner', 1, 'game_winner', 2);
		$this->createTournamentGame($division, $pool, 6, 2, '6', null, BRACKET_GAME, 'game_winner', 3, 'game_winner', 4);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 7, 2, '7', null, BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
			$this->createTournamentGame($division, $pool, 8, 2, '8', null, BRACKET_GAME, 'game_loser', 3, 'game_loser', 4);
		}

		// Round 3: more winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 9, 3, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 5, 'game_winner', 6);
		if ($bronze) {
			$this->createTournamentGame($division, $pool, 10, 3, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 5, 'game_loser', 6);
		}
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 11, 3, null, $this->first_team + 5, BRACKET_GAME, 'game_winner', 7, 'game_winner', 8);
			$this->createTournamentGame($division, $pool, 12, 3, null, $this->first_team + 7, BRACKET_GAME, 'game_loser', 7, 'game_loser', 8);
		}
	}

	public function createQuartersNine(Division $division, Pool $pool, $bronze, $consolation) {
		// Round 1: 8v9
		$this->createTournamentGame($division, $pool, 1, 1, '01', null, BRACKET_GAME, 'pool', 8, 'pool', 9);

		// Round 2: 1 vs Winner 1, 2v7, 3v6, 4v5
		$this->createTournamentGame($division, $pool, 2, 2, '02', null, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$this->createTournamentGame($division, $pool, 3, 2, '03', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 4, 2, '04', null, BRACKET_GAME, 'pool', 2, 'pool', 7);
		$this->createTournamentGame($division, $pool, 5, 2, '05', null, BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 3: winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 6, 3, '06', null, BRACKET_GAME, 'game_winner', 2, 'game_winner', 3);
		$this->createTournamentGame($division, $pool, 7, 3, '07', null, BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 8, 3, '08', null, BRACKET_GAME, 'game_loser', 1, 'game_loser', 4);
			$this->createTournamentGame($division, $pool, 9, 3, '09', null, BRACKET_GAME, 'game_loser', 2, 'game_loser', 5);
		}

		// Round 4: more winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 10, 4, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 6, 'game_winner', 7);
		if ($bronze) {
			$this->createTournamentGame($division, $pool, 11, 4, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 6, 'game_loser', 7);
		}
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 12, 4, '10', null, BRACKET_GAME, 'game_winner', 8, 'game_loser', 3);
			$this->createTournamentGame($division, $pool, 13, 4, null, $this->first_team + 8, BRACKET_GAME, 'game_loser', 8, 'game_loser', 9);

			// Round 5: optional Winner J vs Winner I - 5th/6th Place
			$this->createTournamentGame($division, $pool, 14, 5, null, $this->first_team + 5, BRACKET_GAME, 'game_winner', 12, 'game_winner', 9);
		}
	}

	public function createQuartersTen(Division $division, Pool $pool, $bronze, $consolation) {
		// Round 1: 8v9, 7v10
		$this->createTournamentGame($division, $pool, 1, 1, '01', null, BRACKET_GAME, 'pool', 8, 'pool', 9);
		$this->createTournamentGame($division, $pool, 2, 1, '02', null, BRACKET_GAME, 'pool', 7, 'pool', 10);

		// Round 2: 1 vs Winner 1, 2 vs Winner 2, 3v6, 4v5, optional Loser 1 vs Loser 2 - 9th/10th Place
		$this->createTournamentGame($division, $pool, 3, 2, '03', null, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$this->createTournamentGame($division, $pool, 4, 2, '04', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 5, 2, '05', null, BRACKET_GAME, 'pool', 2, 'game_winner', 2);
		$this->createTournamentGame($division, $pool, 6, 2, '06', null, BRACKET_GAME, 'pool', 3, 'pool', 6);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 7, 2, '07', null, BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 8, 3, '08', null, BRACKET_GAME, 'game_winner', 3, 'game_winner', 4);
		$this->createTournamentGame($division, $pool, 9, 3, '09', null, BRACKET_GAME, 'game_winner', 5, 'game_winner', 6);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 10, 3, '10', null, BRACKET_GAME, 'game_loser', 3, 'game_loser', 4);
			$this->createTournamentGame($division, $pool, 11, 3, '11', null, BRACKET_GAME, 'game_loser', 5, 'game_loser', 6);
		}

		// Round 4: more winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 12, 4, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 8, 'game_winner', 9);
		if ($bronze) {
			$this->createTournamentGame($division, $pool, 13, 4, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 8, 'game_loser', 9);
		}
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 14, 4, null, $this->first_team + 5, BRACKET_GAME, 'game_winner', 10, 'game_winner', 11);
			$this->createTournamentGame($division, $pool, 15, 4, null, $this->first_team + 7, BRACKET_GAME, 'game_loser', 10, 'game_loser', 11);
		}
	}

	public function createQuartersTenPlus(Division $division, Pool $pool, $bronze, $consolation) {
		// Round 1: 8v9, 7v10
		$this->createTournamentGame($division, $pool, 1, 1, '01', null, BRACKET_GAME, 'pool', 8, 'pool', 9);
		$this->createTournamentGame($division, $pool, 2, 1, '02', null, BRACKET_GAME, 'pool', 7, 'pool', 10);

		// Round 2: 1 vs Winner 1, 2 vs Winner 2, 3v6, 4v5
		$this->createTournamentGame($division, $pool, 3, 2, '03', null, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$this->createTournamentGame($division, $pool, 4, 2, '04', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 5, 2, '05', null, BRACKET_GAME, 'pool', 2, 'game_winner', 2);
		$this->createTournamentGame($division, $pool, 6, 2, '06', null, BRACKET_GAME, 'pool', 3, 'pool', 6);

		// Round 3: winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 7, 3, '07', null, BRACKET_GAME, 'game_winner', 3, 'game_winner', 4);
		$this->createTournamentGame($division, $pool, 8, 3, '08', null, BRACKET_GAME, 'game_winner', 5, 'game_winner', 6);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 9, 3, '09', null, BRACKET_GAME, 'game_loser', 3, 'game_loser', 2);
			$this->createTournamentGame($division, $pool, 10, 3, '10', null, BRACKET_GAME, 'game_loser', 5, 'game_loser', 1);
		}

		// Round 4: Consolation round semis
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 11, 4, '11', null, BRACKET_GAME, 'game_loser', 4, 'game_winner', 9);
			$this->createTournamentGame($division, $pool, 12, 4, '12', null, BRACKET_GAME, 'game_loser', 6, 'game_winner', 10);
		}

		// Round 5: more winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 13, 5, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 7, 'game_winner', 8);
		if ($bronze) {
			$this->createTournamentGame($division, $pool, 14, 5, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 7, 'game_loser', 8);
		}
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 15, 5, null, $this->first_team + 5, BRACKET_GAME, 'game_winner', 11, 'game_winner', 12);
			$this->createTournamentGame($division, $pool, 16, 5, null, $this->first_team + 7, BRACKET_GAME, 'game_loser', 12, 'game_loser', 11);
			$this->createTournamentGame($division, $pool, 17, 5, null, $this->first_team + 9, BRACKET_GAME, 'game_loser', 10, 'game_loser', 9);
		}
	}

	public function createPresemisTen(Division $division, Pool $pool, $bronze, $consolation) {
		// Round 1: 1v2, 3v6, 4v5, 7v10, 8v9
		$this->createTournamentGame($division, $pool, 1, 1, '01', null, BRACKET_GAME, 'pool', 1, 'pool', 2);
		$this->createTournamentGame($division, $pool, 2, 1, '02', null, BRACKET_GAME, 'pool', 3, 'pool', 6);
		$this->createTournamentGame($division, $pool, 3, 1, '03', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 4, 1, '04', null, BRACKET_GAME, 'pool', 7, 'pool', 10);
		$this->createTournamentGame($division, $pool, 5, 1, '05', null, BRACKET_GAME, 'pool', 8, 'pool', 9);

		// Round 2: Winner 1 vs Winner 3, Loser 1 vs Winner 2, optional Loser 2 vs Winner 4, Loser 3 vs Winner 5, optional Loser 4 vs Loser 5 - 9th/10th Place game 1
		$this->createTournamentGame($division, $pool, 6, 2, '06', null, BRACKET_GAME, 'game_winner', 1, 'game_winner', 3);
		$this->createTournamentGame($division, $pool, 7, 2, '07', null, BRACKET_GAME, 'game_loser', 1, 'game_winner', 2);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 8, 2, '08', null, BRACKET_GAME, 'game_loser', 2, 'game_winner', 4);
			$this->createTournamentGame($division, $pool, 9, 2, '09', null, BRACKET_GAME, 'game_loser', 3, 'game_winner', 5);
			$this->createTournamentGame($division, $pool, 10, 2, '10', null, BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
		}

		// Round 3: winners vs winners, optional losers vs losers
		$this->createTournamentGame($division, $pool, 11, 3, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 6, 'game_winner', 7);
		if ($bronze) {
			$this->createTournamentGame($division, $pool, 12, 3, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 6, 'game_loser', 7);
		}
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 13, 3, null, $this->first_team + 5, BRACKET_GAME, 'game_winner', 8, 'game_winner', 9);
			// TODO: Consider swapping L9 with W10 and make game 11 into 9th with L9 v L10, to prevent back-to-back rematch
			$this->createTournamentGame($division, $pool, 14, 3, null, $this->first_team + 7, BRACKET_GAME, 'game_loser', 8, 'game_loser', 9);
			$this->createTournamentGame($division, $pool, 15, 3, '11', null, BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
		}
	}

	public function createQuartersShuffleTen(Division $division, Pool $pool) {
		// Round 1: 1v8, 2v7, 3v6, 4v5, 9v10.
		$this->createTournamentGame($division, $pool, 1, 1, '01', null, BRACKET_GAME, 'pool', 1, 'pool', 8);
		$this->createTournamentGame($division, $pool, 2, 1, '02', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 3, 1, '03', null, BRACKET_GAME, 'pool', 2, 'pool', 7);
		$this->createTournamentGame($division, $pool, 4, 1, '04', null, BRACKET_GAME, 'pool', 3, 'pool', 6);
		$this->createTournamentGame($division, $pool, 5, 1, '05', null, BRACKET_GAME, 'pool', 9, 'pool', 10);

		// Round 2: winners vs winners, bottom 6 get a shake-up
		$this->createTournamentGame($division, $pool, 6, 2, '06', null, BRACKET_GAME, 'game_winner', 1, 'game_winner', 2);
		$this->createTournamentGame($division, $pool, 7, 2, '07', null, BRACKET_GAME, 'game_winner', 3, 'game_winner', 4);
		$this->createTournamentGame($division, $pool, 8, 2, '08', null, BRACKET_GAME, 'game_loser', 2, 'game_loser', 4);
		$this->createTournamentGame($division, $pool, 9, 2, '09', null, BRACKET_GAME, 'game_loser', 3, 'game_winner', 5);
		$this->createTournamentGame($division, $pool, 10, 2, '10', null, BRACKET_GAME, 'game_loser', 1, 'game_loser', 5);

		// Round 3: more winners vs winners
		$this->createTournamentGame($division, $pool, 11, 3, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 6, 'game_winner', 7);
		$this->createTournamentGame($division, $pool, 12, 3, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 6, 'game_loser', 7);
		$this->createTournamentGame($division, $pool, 13, 3, null, $this->first_team + 5, BRACKET_GAME, 'game_winner', 8, 'game_winner', 9);
		$this->createTournamentGame($division, $pool, 14, 3, null, $this->first_team + 7, BRACKET_GAME, 'game_loser', 8, 'game_winner', 10);
		$this->createTournamentGame($division, $pool, 15, 3, null, $this->first_team + 9, BRACKET_GAME, 'game_loser', 9, 'game_loser', 10);
	}

	public function createPrequartersShuffleTen(Division $division, Pool $pool) {
		// Round 1: 8v9, 7v10 to get into top 8, top 6 play for starting position in the bracket
		$this->createTournamentGame($division, $pool, 1, 1, '01', null, BRACKET_GAME, 'pool', 1, 'pool', 3);
		$this->createTournamentGame($division, $pool, 2, 1, '02', null, BRACKET_GAME, 'pool', 2, 'pool', 4);
		$this->createTournamentGame($division, $pool, 3, 1, '03', null, BRACKET_GAME, 'pool', 5, 'pool', 6);
		$this->createTournamentGame($division, $pool, 4, 1, '04', null, BRACKET_GAME, 'pool', 8, 'pool', 9);
		$this->createTournamentGame($division, $pool, 5, 1, '05', null, BRACKET_GAME, 'pool', 7, 'pool', 10);

		// Round 2: mostly winners vs winners, losers vs losers
		$this->createTournamentGame($division, $pool, 6, 2, '06', null, BRACKET_GAME, 'game_winner', 1, 'game_winner', 4);
		$this->createTournamentGame($division, $pool, 7, 2, '07', null, BRACKET_GAME, 'game_winner', 3, 'game_loser', 2);
		$this->createTournamentGame($division, $pool, 8, 2, '08', null, BRACKET_GAME, 'game_loser', 1, 'game_loser', 3);
		$this->createTournamentGame($division, $pool, 9, 2, '09', null, BRACKET_GAME, 'game_winner', 2, 'game_winner', 5);
		$this->createTournamentGame($division, $pool, 10, 2, '10', null, BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);

		// Round 3: winners vs winners, losers vs losers
		$this->createTournamentGame($division, $pool, 11, 3, '11', null, BRACKET_GAME, 'game_winner', 6, 'game_winner', 7);
		$this->createTournamentGame($division, $pool, 12, 3, '12', null, BRACKET_GAME, 'game_winner', 8, 'game_winner', 9);
		$this->createTournamentGame($division, $pool, 13, 3, '13', null, BRACKET_GAME, 'game_loser', 7, 'game_winner', 10);
		$this->createTournamentGame($division, $pool, 14, 3, '14', null, BRACKET_GAME, 'game_loser', 9, 'game_loser', 8);
		$this->createTournamentGame($division, $pool, 15, 3, '15', null, BRACKET_GAME, 'game_loser', 6, 'game_loser', 10);

		// Round 4: more winners vs winners, losers vs losers
		$this->createTournamentGame($division, $pool, 16, 4, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 11, 'game_winner', 12);
		$this->createTournamentGame($division, $pool, 17, 4, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 11, 'game_loser', 12);
		$this->createTournamentGame($division, $pool, 18, 4, null, $this->first_team + 5, BRACKET_GAME, 'game_winner', 13, 'game_winner', 14);
		$this->createTournamentGame($division, $pool, 19, 4, null, $this->first_team + 7, BRACKET_GAME, 'game_loser', 14, 'game_winner', 15);
		$this->createTournamentGame($division, $pool, 20, 4, null, $this->first_team + 9, BRACKET_GAME, 'game_loser', 13, 'game_loser', 15);
	}

	public function createQuartersEleven(Division $division, Pool $pool, $bronze, $consolation) {
		// Round 1: 8v9, 7v10, 6v11
		$this->createTournamentGame($division, $pool, 1, 1, '01', null, BRACKET_GAME, 'pool', 8, 'pool', 9);
		$this->createTournamentGame($division, $pool, 2, 1, '02', null, BRACKET_GAME, 'pool', 7, 'pool', 10);
		$this->createTournamentGame($division, $pool, 3, 1, '03', null, BRACKET_GAME, 'pool', 6, 'pool', 11);

		// Round 2: 1 vs Winner 1, 2 vs Winner 2, 3 vs Winner 3, 4v5, optional Loser 1 vs Loser 2 - game 1 of round robin for 9th/10th/11th Place
		$this->createTournamentGame($division, $pool, 4, 2, '04', null, BRACKET_GAME, 'pool', 1, 'game_winner', 1);
		$this->createTournamentGame($division, $pool, 5, 2, '05', null, BRACKET_GAME, 'pool', 4, 'pool', 5);
		$this->createTournamentGame($division, $pool, 6, 2, '06', null, BRACKET_GAME, 'pool', 2, 'game_winner', 2);
		$this->createTournamentGame($division, $pool, 7, 2, '07', null, BRACKET_GAME, 'pool', 3, 'game_winner', 3);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 8, 2, '08', null, BRACKET_GAME, 'game_loser', 1, 'game_loser', 2);
		}

		// Round 3: winners vs winners, optional losers vs losers, optional Loser 1 vs Loser 3 - game 2 of round robin for 9th/10th/11th Place
		$this->createTournamentGame($division, $pool, 9, 3, '09', null, BRACKET_GAME, 'game_winner', 4, 'game_winner', 5);
		$this->createTournamentGame($division, $pool, 10, 3, '10', null, BRACKET_GAME, 'game_winner', 6, 'game_winner', 7);
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 11, 3, '11', null, BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
			$this->createTournamentGame($division, $pool, 12, 3, '12', null, BRACKET_GAME, 'game_loser', 7, 'game_loser', 6);
			$this->createTournamentGame($division, $pool, 13, 3, '13', null, BRACKET_GAME, 'game_loser', 1, 'game_loser', 3);
		}

		// Round 4: more winners vs winners, optional losers vs losers, optional Loser 3 vs Loser 2 - game 3 of round robin for 9th/10th/11th Place
		$this->createTournamentGame($division, $pool, 14, 4, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 9, 'game_winner', 10);
		if ($bronze) {
			$this->createTournamentGame($division, $pool, 15, 4, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 9, 'game_loser', 10);
		}
		if ($consolation) {
			$this->createTournamentGame($division, $pool, 16, 4, null, $this->first_team + 5, BRACKET_GAME, 'game_winner', 11, 'game_loser', 12);
			$this->createTournamentGame($division, $pool, 17, 4, null, $this->first_team + 7, BRACKET_GAME, 'game_loser', 11, 'game_loser', 12);
			$this->createTournamentGame($division, $pool, 18, 4, '14', null, BRACKET_GAME, 'game_loser', 3, 'game_loser', 2);
		}
	}

	function createQuartersTwelve(Division $division, Pool $pool, $bronze, $consolation) {
		// Round 1: Top four play for quarterfinal seeding, bottom eight play to make it into the quarters
		$success = $this->createTournamentGame($division, $pool, 1, 1, '01', null, BRACKET_GAME, 'pool', 1, 'pool', 2);
		$success &= $this->createTournamentGame($division, $pool, 2, 1, '02', null, BRACKET_GAME, 'pool', 3, 'pool', 4);
		$success &= $this->createTournamentGame($division, $pool, 3, 1, '03', null, BRACKET_GAME, 'pool', 5, 'pool', 12);
		$success &= $this->createTournamentGame($division, $pool, 4, 1, '04', null, BRACKET_GAME, 'pool', 6, 'pool', 11);
		$success &= $this->createTournamentGame($division, $pool, 5, 1, '05', null, BRACKET_GAME, 'pool', 7, 'pool', 10);
		$success &= $this->createTournamentGame($division, $pool, 6, 1, '06', null, BRACKET_GAME, 'pool', 8, 'pool', 9);

		// Round 2: Quarterfinals, game 1 of round robin for 9th-12th Place
		$success &= $this->createTournamentGame($division, $pool, 7, 2, '07', null, BRACKET_GAME, 'game_winner', 1, 'game_winner', 6);
		$success &= $this->createTournamentGame($division, $pool, 8, 2, '08', null, BRACKET_GAME, 'game_loser', 1, 'game_winner', 5);
		$success &= $this->createTournamentGame($division, $pool, 9, 2, '09', null, BRACKET_GAME, 'game_winner', 2, 'game_winner', 4);
		$success &= $this->createTournamentGame($division, $pool, 10, 2, '10', null, BRACKET_GAME, 'game_loser', 2, 'game_winner', 3);
		if ($consolation) {
			$success &= $this->createTournamentGame($division, $pool, 11, 2, '11', null, BRACKET_GAME, 'game_loser', 3, 'game_loser', 4);
			$success &= $this->createTournamentGame($division, $pool, 12, 2, '12', null, BRACKET_GAME, 'game_loser', 5, 'game_loser', 6);
		}

		// Round 3: winners vs winners, optional losers vs losers, game 2 of round robin for 9th-12th Place
		$success &= $this->createTournamentGame($division, $pool, 13, 3, '13', null, BRACKET_GAME, 'game_winner', 7, 'game_winner', 10);
		$success &= $this->createTournamentGame($division, $pool, 14, 3, '14', null, BRACKET_GAME, 'game_winner', 8, 'game_winner', 9);
		if ($consolation) {
			$success &= $this->createTournamentGame($division, $pool, 15, 3, '15', null, BRACKET_GAME, 'game_loser', 7, 'game_loser', 10);
			$success &= $this->createTournamentGame($division, $pool, 16, 3, '16', null, BRACKET_GAME, 'game_loser', 8, 'game_loser', 9);
			$success &= $this->createTournamentGame($division, $pool, 17, 3, '17', null, BRACKET_GAME, 'game_loser', 3, 'game_loser', 5);
			$success &= $this->createTournamentGame($division, $pool, 18, 3, '18', null, BRACKET_GAME, 'game_loser', 6, 'game_loser', 4);
		}

		// Round 4: more winners vs winners, optional losers vs losers, game 3 of round robin for 9th-12th Place
		$success &= $this->createTournamentGame($division, $pool, 19, 4, null, $this->first_team + 1, BRACKET_GAME, 'game_winner', 13, 'game_winner', 14);
		if ($bronze) {
			$success &= $this->createTournamentGame($division, $pool, 20, 4, null, $this->first_team + 3, BRACKET_GAME, 'game_loser', 13, 'game_loser', 14);
		}
		if ($consolation) {
			$success &= $this->createTournamentGame($division, $pool, 21, 4, null, $this->first_team + 5, BRACKET_GAME, 'game_winner', 15, 'game_winner', 16);
			$success &= $this->createTournamentGame($division, $pool, 22, 4, null, $this->first_team + 7, BRACKET_GAME, 'game_loser', 15, 'game_loser', 16);
			$success &= $this->createTournamentGame($division, $pool, 23, 4, '19', null, BRACKET_GAME, 'game_loser', 3, 'game_loser', 6);
			$success &= $this->createTournamentGame($division, $pool, 24, 4, '20', null, BRACKET_GAME, 'game_loser', 4, 'game_loser', 5);
		}

		return $success;
	}

	/**
	 * Create a single tournament game
	 */
	public function createTournamentGame(Division $division, Pool $pool, $id, $round, $name, $placement, $type,
		$home_dependency_type, $home_dependency_id, $away_dependency_type, $away_dependency_id)
	{
		if (array_key_exists($id, $this->games)) {
			throw new ScheduleException(__('Duplicate game id, check the scheduling algorithm.'));
		}

		if ($name) {
			$long_name = $pool->name;
			if ($name !== true) {
				if (!empty($long_name)) {
					$long_name .= '-';
				}
				$long_name .= $name;
			}
		} else {
			$long_name = null;
		}

		$save = [
			'round' => $round,
			'type' => $type,
			'name' => $long_name,
			'placement' => $placement,
			'home_dependency_type' => $home_dependency_type,
			'away_dependency_type' => $away_dependency_type,
			'division_id' => $division->id,
			'pool_id' => $pool->id,
		];

		if (substr($home_dependency_type, 0, 5) == 'game_') {
			// Game-type dependencies need to be resolved by the save process
			$save['home_dependency_resolved'] = false;
			$save['home_dependency_id'] = $home_dependency_id;
		} else if ($home_dependency_type == 'pool' || $home_dependency_type == 'copy') {
			$save['home_dependency_resolved'] = true;
			$save['home_pool_team_id'] = $pool->pools_teams[$home_dependency_id - 1]->id;
		} else {
			throw new ScheduleException(__('Unknown home dependency type "{0}".', $home_dependency_type));
		}

		if (substr($away_dependency_type, 0, 5) == 'game_') {
			// Game-type dependencies need to be resolved by the save process
			$save['away_dependency_resolved'] = false;
			$save['away_dependency_id'] = $away_dependency_id;
		} else if ($away_dependency_type == 'pool' || $away_dependency_type == 'copy') {
			$save['away_dependency_resolved'] = true;
			$save['away_pool_team_id'] = $pool->pools_teams[$away_dependency_id - 1]->id;
		} else {
			throw new ScheduleException(__('Unknown away dependency type "{0}".', $away_dependency_type));
		}

		$this->games[$id] = TableRegistry::getTableLocator()->get('Games')->newEntity($save,
			array_merge($division->_options->toArray(), [
				'validate' => 'scheduleAdd',
				'games' => $this->games,
				'division' => $division,
				'accessibleFields' => ['home_dependency_resolved' => true, 'away_dependency_resolved' => true],
			])
		);

		return true;
	}

	public function canSchedule($required_field_counts, $available_field_counts) {
		$pool_times = [];
		foreach ($required_field_counts as $round => $required) {
			while ($required--) {
				if (empty($available_field_counts)) {
					return false;
				}

				// If this pool has already had games scheduled, but not in this
				// round, ignore any unused slots in the same time as games
				// in the last round of this pool.
				if (!empty($pool_times) && empty($pool_times[$round])) {
					$max_round = max(array_keys($pool_times));
					$available_slots = array_diff(array_keys($available_field_counts), $pool_times[$max_round]);
					if (empty($available_slots)) {
						return false;
					}
					$slot_list = min($available_slots);
				} else {
					$slot_list = min(array_keys($available_field_counts));
				}

				-- $available_field_counts[$slot_list];
				if ($available_field_counts[$slot_list] == 0) {
					unset($available_field_counts[$slot_list]);
				}
				if (empty($pool_times[$round])) {
					$pool_times[$round] = [];
				}
				$pool_times[$round][] = $slot_list;
			}
		}

		return true;
	}

	protected function assignFieldsByRound(Division $division, Pool $pool, $games) {
		uasort($games, [$this, 'compareRound']);
		if (is_array($division->_options->start_date)) {
			$separate_days = false;
		} else {
			$rounds = count(array_unique(collection($games)->extract('round')->toList()));
			$dates = count(array_unique(collection($division->game_slots)->extract('game_date')->toList()));
			$separate_days = ($division->schedule_type != 'tournament') && ($rounds <= $dates);
		}

		// If this division has already had games scheduled in earlier
		// stages, get rid of any unused slots up to the end of the last stage.
		$prior_pools = collection($division->pools)->filter(function ($p) use ($pool) {
			return $p->stage < $pool->stage;
		})->extract('id')->toList();
		if ($separate_days) {
			$initial = new FrozenDate('0001-01-01');
		} else {
			$initial = new FrozenTime('0001-01-01 00:00:00');
		}
		$last_game = $initial;
		foreach ($prior_pools as $prior_pool) {
			$pool_slots = collection($division->games)->match(['pool_id' => $prior_pool]);
			if ($separate_days) {
				$last_pool_game = $pool_slots->max('game_slot.game_date')->game_slot->game_date;
			} else {
				$last_pool_game = $pool_slots->max('game_slot.start_time')->game_slot->start_time;
			}
			$last_game = max($last_game, $last_pool_game);
		}
		if ($last_game != $initial) {
			foreach ($division->game_slots as $key => $slot) {
				if ($separate_days) {
					$slot_key = $slot->game_date->toDateString();
				} else {
					$slot_key = $slot->start_time->toDateTimeString();
				}
				if ($slot_key <= $last_game) {
					$division->used_slots[] = $slot;
					unset($division->game_slots[$key]);
				}
			}
		}

		$division->game_slots = collection($division->game_slots)->sortBy('start_time', SORT_ASC)->toList();

		foreach ($games as $game) {
			if (empty($game->home_dependency_type) || $game->home_dependency_type != 'copy') {
				if (is_array($division->_options->start_date)) {
					// TODO: See discussion of CakePHP bug in templates/Schedules/date.php
					$date = new FrozenDate($division->_options->start_date["round{$game->round}"]);
					$time = new FrozenTime($division->_options->start_date["round{$game->round}"]);
					$game_slot = $this->selectRoundGameslot($division, $date, $time->i18nFormat('HH:mm'), $game->round, false);
				} else {
					// '0' is a non-blank string which collection::filter can compare to, but will always be less than any actual time
					$game_slot = $this->selectRoundGameslot($division, $division->_options->start_date, '0', $game->round, $separate_days);
				}

				$game->game_slot_id = $game_slot->id;
				$game->game_slot = $game_slot;
				$game_slot->assigned = true;
			}
		}

		return $games;
	}

	protected function selectRoundGameslot($division, $date, $time, $round, $separate_days) {
		// Remember details about the games already scheduled, so that when
		//we get to the next round we make sure to advance the time.
		static $pool_times = [];

		// If this pool has already had games scheduled, but not in this
		// round, get rid of any unused slots in the same time as games
		// in the last round of this pool. If we have at least as many
		// days as rounds, get rid of everything on the same day.
		if (!empty($pool_times) && empty($pool_times[$round])) {
			$max_round = max(array_keys($pool_times));
			$used = $pool_times[$max_round];
			foreach ($division->game_slots as $key => $slot) {
				if ($separate_days) {
					$slot_key = $slot->game_date->toDateString();
				} else {
					$slot_key = $slot->start_time->toDateTimeString();
				}
				if (in_array($slot_key, $used)) {
					$division->used_slots[] = $slot;
					unset($division->game_slots[$key]);
				}
			}
		}

		// TODO: If we ever want to schedule tournaments for competition divisions, we'll likely need to change this.
		if (empty($division->game_slots)) {
			if ($time == '0') {
				throw new ScheduleException(__('Couldn\'t get a slot ID: date {0}, round {1}', $date, $round));
			} else {
				throw new ScheduleException(__('Couldn\'t get a slot ID: date {0}, time {1}, round {2}', $date, $time, $round));
			}
		}

		$possible_slots = collection($division->game_slots)->filter(function ($slot) use ($date, $time) {
			// Time fields still have dates in them; format as HH:mm to get rid of that.
			return $slot->game_date == $date && $slot->game_start->i18nFormat('HH:mm') >= $time;
		})->toList();
		if (empty($possible_slots)) {
			// No slots at the requested time, or later on the same day. Try any later date.
			$possible_slots = collection($division->game_slots)->filter(function ($slot) use ($date) {
				return $slot->game_date > $date;
			})->toList();
		}
		if (empty($possible_slots)) {
			// No slots on later date either. Take the last available slot instead.
			$possible_slots = array_reverse($division->game_slots);
		}
		$slot = reset($possible_slots);
		$this->removeGameslot($division, $slot);
		if (empty($pool_times[$round])) {
			$pool_times[$round] = [];
		}
		if ($separate_days) {
			$pool_times[$round][] = $slot->game_date;
		} else {
			$pool_times[$round][] = $slot->start_time;
		}

		return $slot;
	}

	protected static function compareRound($a, $b) {
		// When creating a blank schedule, games have no extra details
		if (!isset($a->round)) {
			return 0;
		}

		if ($a->round > $b->round) {
			return 1;
		} else if ($a->round < $b->round) {
			return -1;
		}

		if ($a->placement > $b->placement) {
			return 1;
		} else if ($a->placement < $b->placement) {
			return -1;
		}

		if ($a->name > $b->name) {
			return 1;
		} else if ($a->name < $b->name) {
			return -1;
		}

		return 0;
	}
}
