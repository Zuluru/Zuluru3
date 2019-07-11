<?php

/**
 * Derived class for implementing functionality for the ratings ladder.
 */
namespace App\Module;

use App\Exception\ScheduleException;
use App\Model\Entity\Division;
use App\Model\Entity\Team;
use App\Model\Results\Comparison;
use App\Model\Rule\InConfigRule;
use Authorization\IdentityInterface;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event as CakeEvent;
use Cake\Event\EventManager;

class LeagueTypeRatingsLadder extends LeagueType {
	/**
	 * Define the element to use for rendering various views
	 */
	public $render_element = 'ladder';

	public function links(Division $division, IdentityInterface $identity = null, $controller, $action) {
		$links = parent::links($division, $identity, $controller, $action);
		if (($controller != 'Divisions' || $action != 'ratings') && $identity && $identity->can('edit_schedule', $division)) {
			$links[__('Adjust Ratings')] = [
				'url' => ['controller' => 'Divisions', 'action' => 'ratings', 'division' => $division->id],
			];
		}
		return $links;
	}

	/**
	 * Sort a ladder division by rating first, then all the usual stuff.
	 */
	public static function compareTeams(Team $a, Team $b, array $context) {
		if ($a->rating < $b->rating) {
			return 1;
		} else if ($a->rating > $b->rating) {
			return -1;
		}

		$ret = Comparison::compareTeamsTieBreakers($a, $b, array_merge($context, ['results' => 'season']));
		if ($ret == 0) {
			$ret = parent::compareTeams($a, $b, $context);
		}
		return $ret;
	}

	public function schedulingFields($administrative) {
		if ($administrative) {
			return [
				'games_before_repeat' => [
					'label' => __('Games Before Repeat'),
					'options' => Configure::read('options.games_before_repeat'),
					'empty' => '---',
					'help' => __('The number of games before two teams can be scheduled to play each other again.'),
					'required' => true,	// Since this is not in the model validation list, we must force this
				],
			];
		} else {
			return [];
		}
	}

	public function schedulingFieldsRules(EntityInterface $entity) {
		$ret = parent::schedulingFieldsRules($entity);

		$rule = new InConfigRule('options.games_before_repeat');
		if (!$rule($entity, ['errorField' => 'games_before_repeat'])) {
			$entity->errors('current_round', ['validGamesBeforeRepeat' => __('You must select a valid number of games before repeat.')]);
			$ret = false;
		}

		return $ret;
	}

	public function scheduleOptions($num_teams, $stage, $sport) {
		$types = [
			'single' => __('Single blank, unscheduled game (2 teams, one {0})', __(Configure::read("sports.{$sport}.field"))),
			'oneset_ratings_ladder' => __('Set of ratings-scheduled games for all teams ({0} teams, {1} games, one day)', $num_teams, $num_teams / 2),
		];

		return $types;
	}

	public function scheduleRequirements($type, $num_teams) {
		switch($type) {
			case 'single':
				return [1];
			case 'oneset_ratings_ladder':
				return [$num_teams / 2];
		}
	}

	public function createSchedule(Division $division, $pool) {
		if ($pool) {
			throw new ScheduleException('Unexpected pool information in ratings ladder scheduler!');
		}

		$this->startSchedule($division, $division->_options->start_date);

		switch($division->_options->type) {
			case 'single':
				// Create single game
				$games = [$this->createEmptyGame($division, $division->_options->start_date)];
				break;
			case 'oneset_ratings_ladder':
				// Create game for all teams in division
				$games = $this->createScheduledSet($division, $division->_options->start_date);
				break;
		}

		$this->finishSchedule($division, $games);
	}

	/*
	 * Create a scheduled set of games for this division
	 */
	public function createScheduledSet(Division $division, $date) {
		$num_teams = count($division->teams);

		if ($num_teams < 2) {
			throw new ScheduleException(__('Must have two teams.'));
		}

		if ($num_teams % 2) {
			throw new ScheduleException(__('Must have even number of teams.'));
		}

		// Sort teams so ratings scheduling works properly
		$this->sort($division, $division->league, $division->games, null, false);

		return $this->scheduleOneSet($division, $date, $division->teams);
	}

	/**
	 * Schedule one set of games using the ratings_ladder scheme!
	 */
	protected function scheduleOneSet(Division $division, $date, $teams) {
		$games_before_repeat = $division->games_before_repeat;
		$max_retries = $division->league->schedule_attempts;
		$ret = false;

		$versus_teams = [];
		$gbr_diff = [];
		$seed_closeness = [];
		$ratings_closeness = [];

		for ($j = 0; $j < $max_retries; $j++) {
			list($ret, $versus_teams_try, $gbr_diff_try, $seed_closeness_try, $ratings_closeness_try) =
				$this->scheduleOneSetTry($division, $teams, $games_before_repeat, $j%2);

			if ($ret == false) {
				continue;
			}

			// Keep the best schedule by checking how many times we had to decrement
			// the games_before_repeat restriction in order to be able to generate
			// this schedule...

			// The best possible schedule will first have the smallest seed_closeness,
			// then will have the smallest ratings_closeness,
			// and then will have the smallest games before repeat sum
			if ((count($gbr_diff) == 0 || array_sum($seed_closeness) > array_sum($seed_closeness_try)) ||
				(array_sum($seed_closeness) == array_sum($seed_closeness_try) && array_sum($ratings_closeness) > array_sum($ratings_closeness_try)) ||
				(array_sum($seed_closeness) == array_sum($seed_closeness_try) && array_sum($ratings_closeness) == array_sum($ratings_closeness_try) && array_sum($gbr_diff) > array_sum($gbr_diff_try))
			) {
				$versus_teams = $versus_teams_try;
				$gbr_diff = $gbr_diff_try;
				$seed_closeness = $seed_closeness_try;
				$ratings_closeness = $ratings_closeness_try;
			}

			if (array_sum($seed_closeness) == sizeof($teams)/2) {
				// that's enough - don't bother getting any more, you have a perfect schedule (ie: 1 vs 2, 3 vs 4, etc).
				break;
			}
		}

		$games = $this->createGamesForTeams($division, $date, $versus_teams);

		$event = new CakeEvent('Controller.Schedules.ratings_ladder_scheduled', $this, [
			$seed_closeness, $gbr_diff,
			collection($versus_teams)->extract('name')->toList()
		]);
		EventManager::instance()->dispatch($event);

		return $games;
	}

	/**
	 * This does the actual work of scheduling a one set ratings_ladder set of games.
	 * However it has some problems where it may not properly schedule all
	 * the games.  If it runs into problems then we use the wrapper
	 * function that calls this one to retry it.
	 * If any problems are found then this function rolls back it's changes.
	 *
	 * The algorithm is as follows...
	 * - start at either top or bottom of ordered ladder
	 * - grab a "group" of teams, starting with a group size of 1 (and increasing to a per-division-defined MAX)
	 * - take the first team in the group, and find a random opponent within the group that meets the GBR criteria
	 * - remove those 2 teams from the ordered ladder and repeat
	 *
	 */
	protected function scheduleOneSetTry(Division $division, $teams, $games_before_repeat, $down) {
		$ratings_closeness = [];
		$seed_closeness = [];
		$gbr_diff = [];
		$versus_teams = [];

		// TODO: make this maximum a per-division variable, and enforce it in the caller function?
		// maximum standings difference of matched teams:
		$MAX_STANDINGS_DIFF = 8;
		// NOTE: that's not REALLY the max standings diff...
		// it's more like the max grouping of teams to use as possible opponents, and they
		// may be well over 8 seeds apart...

		// current standings diff (starts at 1, counts up to MAX_STANDINGS_DIFF)
		$CURRENT_STANDINGS_DIFF = 1;

		$NUM_TIMES_TO_TRY_CURRENT = 10;

		if ($down) {
			$teams = array_reverse($teams);  // go up instead
		}

		// copy the games before repeat variable
		$gbr = $games_before_repeat;
		// copy the teams array
		$workingteams = $teams;

		// main loop - go through all of the teams
		while(sizeof($workingteams) > 0) {
			// start with the first team (remove from array)
			$current_team = array_shift($workingteams);

			// get the group of teams that are possible opponents
			$possible_opponents = array_slice ($workingteams, 0, $CURRENT_STANDINGS_DIFF);

			// now, loop through the possible opponents and save only the ones who have not been in recent games
			$recent_opponents = $this->getRecentOpponents($division, $current_team->id, $gbr);
			foreach ($possible_opponents as $key => $po) {
				if (in_array($po->id, $recent_opponents)) {
					unset($possible_opponents[$key]);
				}
			}

			// if at this point there are no possible opponents, then you have to relax one of the restrictions:
			if (sizeof($possible_opponents) == 0 ) {
				if ($NUM_TIMES_TO_TRY_CURRENT > 0) {
					$NUM_TIMES_TO_TRY_CURRENT--;
				} else if ($CURRENT_STANDINGS_DIFF < $MAX_STANDINGS_DIFF) {
					$NUM_TIMES_TO_TRY_CURRENT = 10;
					// try increasing the current standings diff...
					$CURRENT_STANDINGS_DIFF++;
				} else {
					$NUM_TIMES_TO_TRY_CURRENT = 10;
					$CURRENT_STANDINGS_DIFF = 1;
					// try to decrease games before repeat:
					$gbr--;
				}

				// but, if games before repeat goes negative, you're screwed!
				if ($gbr < 0) {
					return false;
				}

				// now, before starting over, put back some stuff...

				// put back the teams:
				$workingteams = $teams;

				// reset these arrays
				$ratings_closeness = [];
				$seed_closeness = [];
				$gbr_diff = [];
				$versus_teams = [];

				// start over:
				continue;

			} // end if sizeof possible opponents

			// now find them an opponent by randomly choosing one of the remaining possible opponents
			shuffle($possible_opponents);
			$opponent = $possible_opponents[0];

			// remove the opponent from the remaining list of teams
			foreach ($workingteams as $key => $team) {
				if ($team->id == $opponent->id) {
					unset($workingteams[$key]);
					break;
				}
			}

			// Create the matchup
			$versus_teams[] = $current_team;
			$versus_teams[] = $opponent;
			$recent_opponents = array_reverse($this->getRecentOpponents($division, $current_team->id));
			$ago = 0;
			foreach ($recent_opponents as $key => $id) {
				if ($opponent->id == $id) {
					$ago = $key + 1;
					break;
				}
			}
			$gbr_diff[] = $ago;

			$counter = 0;
			$seed1 = 0;
			$seed2 = 0;
			$rating1 = $current_team->rating;
			$rating2 = $opponent->rating;
			foreach ($teams as $t) {
				$counter++;
				if ($t->id == $current_team->id) {
					$seed1 = $counter;
				}
				if ($t->id == $opponent->id) {
					$seed2 = $counter;
				}
				if ($seed1 != 0 && $seed2 != 0) {
					break;
				}
			}
			$seed_closeness[] = abs($seed2-$seed1);
			$ratings_closeness[] = pow($rating1-$rating2, 2);
		} // main loop

		return [true, $versus_teams, $gbr_diff, $seed_closeness, $ratings_closeness];
	}

	protected function getRecentOpponents(Division $division, $team_id, $gbr = null) {
		$recent_opponents = [];
		foreach ($division->games as $game) {
			if (in_array($game->status, ['cancelled', 'rescheduled'])) {
				continue;
			}
			if ($game->home_team_id == $team_id) {
				$recent_opponents[] = $game->away_team_id;
			}
			if ($game->away_team_id == $team_id) {
				$recent_opponents[] = $game->home_team_id;
			}
		}

		// Perhaps extract the last few
		if ($gbr !== null) {
			$recent_opponents = array_slice ($recent_opponents, -$gbr);
		}

		return $recent_opponents;
	}
}
