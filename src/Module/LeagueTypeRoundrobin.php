<?php

/**
 * Derived class for implementing functionality for round robin.
 */
namespace App\Module;

use App\Model\Entity\Division;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use App\Exception\ScheduleException;
use App\Model\Entity\Team;
use App\Model\Results\Comparison;
use App\Model\Rule\InConfigRule;

class LeagueTypeRoundrobin extends LeagueType {

	public static function compareTeams(Team $a, Team $b, array $context) {
		$ret = Comparison::compareTeamsTieBreakers($a, $b, array_merge($context, ['results' => 'season']));
		if ($ret == 0) {
			$ret = parent::compareTeams($a, $b, $context);
		}
		return $ret;
	}

	public function schedulingFields($administrative) {
		if ($administrative) {
			return [
				'current_round' => [
					'label' => __('Current Round'),
					'options' => Configure::read('options.round'),
					'empty' => '---',
					'help' => __('New games will be scheduled in this round by default.'),
					'required' => true,	// Since this is not in the model validation list, we must force this
				],
			];
		} else {
			return [];
		}
	}

	public function schedulingFieldsRules(EntityInterface $entity) {
		$ret = parent::schedulingFieldsRules($entity);

		$rule = new InConfigRule('options.round');
		if (!$rule($entity, ['errorField' => 'current_round'])) {
			$entity->errors('current_round', ['validRound' => __('You must select a valid round.')]);
			$ret = false;
		}

		return $ret;
	}

	public function scheduleOptions($num_teams, $stage, $sport) {
		$types = [
			'single' => __('Single blank, unscheduled game (2 teams, one {0})', __(Configure::read("sports.{$sport}.field"))),
			'blankset' => __('Set of blank unscheduled games for all teams in a division ({0} teams, {1} games, one day)', $num_teams, $num_teams / 2),
			'oneset' => __('Set of randomly scheduled games for all teams in a division ({0} teams, {1} games, one day)', $num_teams, $num_teams / 2),
			'fullround' => __('Full-division round-robin ({0} teams, {1} games over {2} weeks)', $num_teams, ($num_teams - 1) * ($num_teams / 2), $num_teams - 1),
			'halfroundstandings' => __('Half-division round-robin ({0} teams, {1} games over {2} weeks), with 2 pools (top, bottom) divided by team standings', $num_teams, (($num_teams / 2 ) - 1) * ($num_teams / 2), $num_teams / 2 - 1),
			'halfroundrating' => __('Half-division round-robin ({0} teams, {1} games over {2} weeks), with 2 pools (top/bottom) divided by rating', $num_teams, (($num_teams / 2 ) - 1) * ($num_teams / 2), $num_teams / 2 - 1),
			'halfroundmix' => __('Half-division round-robin ({0} teams, {1} games over {2} weeks), with 2 even (interleaved) pools divided by team standings', $num_teams, (($num_teams / 2 ) - 1) * ($num_teams / 2), $num_teams / 2 - 1),
		];
		if($num_teams % 4) {
			// Can't do a half-round without an even number of teams in
			// each half.
			unset($types['halfroundstandings']);
			unset($types['halfroundrating']);
			unset($types['halfroundmix']);
		}

		return $types;
	}

	public function scheduleRequirements($type, $num_teams) {
		switch($type) {
			case 'single':
				return [1];
			case 'blankset':
			case 'oneset':
				return [$num_teams / 2];
			case 'fullround':
				return array_fill(0, $num_teams - 1, $num_teams / 2);
			case 'halfroundstandings':
			case 'halfroundrating':
			case 'halfroundmix':
				return array_fill(0, ($num_teams / 2) - 1, $num_teams / 2);
		}
	}

	public function createSchedule(Division $division, $pool) {
		if ($pool) {
			throw new ScheduleException('Unexpected pool information in round robin scheduler!');
		}

		$this->startSchedule($division, $division->_options->start_date);

		switch($division->_options->type) {
			case 'single':
				// Create single game
				$games = [$this->createEmptyGame($division, $division->_options->start_date)];
				break;
			case 'blankset':
				// Create game for all teams in division
				$games = $this->createEmptySet($division, $division->_options->start_date);
				break;
			case 'oneset':
				// Create game for all teams in division
				$games = $this->createScheduledSet($division, $division->_options->start_date);
				break;
			case 'fullround':
				// Create full roundrobin
				$games = $this->createFullRoundrobin($division, $division->_options->start_date);
				break;
			case 'halfroundstandings':
				$games = $this->createHalfRoundrobin($division, $division->_options->start_date, 'standings');
				break;
			case 'halfroundrating':
				$games = $this->createHalfRoundrobin($division, $division->_options->start_date, 'rating');
				break;
			case 'halfroundmix':
				$games = $this->createHalfRoundrobin($division, $division->_options->start_date, 'mix');
				break;
		}

		$this->finishSchedule($division, $games);
	}

	/*
	 * Create an empty set of games for this division
	 */
	public function createEmptySet(Division $division, $date) {
		$num_teams = count($division->teams);

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
			$games[] = $this->createEmptyGame($division, $date);
		}
		return $games;
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

		// randomize team IDs
		shuffle($division->teams);

		return $this->createGamesForTeams($division, $date, $division->teams);
	}

	/*
	 * Create a half round-robin for this division.
	 */
	public function createHalfRoundrobin(Division $division, $date, $how_split = 'standings') {
		$num_teams = count($division->teams);

		if ($num_teams < 2) {
			throw new ScheduleException(__('Must have two teams.'));
		}

		if ($num_teams % 2) {
			throw new ScheduleException(__('Must have even number of teams.'));
		}

		// Split division teams into two groups
		switch($how_split) {
			case 'rating':
				uasort($division->teams, [$this, 'compareRating']);
				$top_half = array_slice($division->teams, 0, ($num_teams / 2));
				$bottom_half = array_slice($division->teams, ($num_teams / 2));
				break;

			case 'standings':
				// TODO: Why did this, and other uses, previously leave include_tournament as the default true?
				$this->sort($division, $division->league, $division->games, null, false);
				$top_half = array_slice($division->teams, 0, ($num_teams / 2));
				$bottom_half = array_slice($division->teams, ($num_teams / 2));
				break;

			// Sort by standings, then do a "snake" to split into two groups
			// $i will be 1,2,...,n, so $i%4 will be 1,2,3,0,...
			case 'mix':
				$this->sort($division, $division->league, $division->games, null, false);
				$top_half = $bottom_half = [];
				$i = 0;
				foreach ($division->teams as $team) {
					if (++$i % 4 < 2) {
						$top_half[] = $team;
					} else {
						$bottom_half[] = $team;
					}
				}
				break;
		}

		// Schedule both halves.
		// TODO: We should create the games for each half and combine them before allocating fields,
		// to be better at accommodating home field and regional requests. Otherwise, a team in the
		// "bottom half" might have their best option already allocated to a game in the "top half".
		// Low priority, as it's only an issue when scheduling half round robins when there are
		// home field or regional preferences.
		return array_merge(
			$this->createFullRoundrobin($division, $date, $top_half, 2),
			$this->createFullRoundrobin($division, $date, $bottom_half)
		);
	}

	/*
	 * Create a full round-robin for this division.
	 */
	public function createFullRoundrobin(Division $division, $date, $teams = null, $repeats = 1) {
		if (is_null($teams)) {
			$this->sort($division, $division->league, $division->games, null, false);
			$teams = array_values($division->teams);
		}

		$num_teams = count($teams);

		if ($num_teams < 2) {
			throw new ScheduleException(__('Must have two teams.'));
		}

		if ($num_teams % 2) {
			throw new ScheduleException(__('Must have even number of teams.'));
		}

		// For n-1 iterations, generate games by pairing up teams
		$iterations_remaining = $num_teams - 1;

		// and so we need n-1 days worth of game slots
		$day_count = $this->countAvailableGameslotDays($division, $date, $num_teams / 2 * $repeats);

		if ($day_count < $iterations_remaining) {
			throw new ScheduleException(__('Need {0} weeks of game slots, yet only {1} are available. Add more game slots.', $iterations_remaining, $day_count));
		}

		$games = [];
		while ($iterations_remaining--) {
			// Round-robin algorithm for n teams:
			// a. pair each team k up with its (n - k - 1) partner in the
			// list. createGamesForTeams() takes the array pairwise, so we do
			// it like this.
			$set_teams = [];
			for($k = 0; $k < ($num_teams / 2); $k++) {
				$set_teams[] = $teams[$k];
				$set_teams[] = $teams[($num_teams - $k - 1)];
			}

			// b. schedule them
			try {
				$games = array_merge($games, $this->createGamesForTeams($division, $date, $set_teams, $num_teams / 2 * ($repeats - 1)));
			} catch (ScheduleException $ex) {
				// Catch possible exceptions from createGamesForTeams and re-throw with additional information
				$errors = (array)$ex->getMessages();
				array_unshift($errors, __('Had to stop with {0} sets left to schedule: could not assign {1}.', $iterations_remaining, __(Configure::read("sports.{$division->league->sport}.fields"))));
				throw new ScheduleException($errors, $ex->getAttributes());
			}

			if ($iterations_remaining != 0) {
				// c. keep k=0 element in place, move k=1 element to end, and move
				// k=2 through n elements left one position.
				$teams = $this->rotateAllExceptFirst($teams);

				// Now, move the date forward to next available game date
				$date = $this->nextGameslotDay($division, $date, $num_teams / 2 * ($repeats - 1));
				if (!$date) {
					throw new ScheduleException(__('Had to stop with {0} sets left to schedule: no more game dates available.', $iterations_remaining));
				}
			}
		}

		return $games;
	}

	/**
	 * Given an array, keep the first element in place, but rotate the
	 * remaining elements by one.
	 */
	protected function rotateAllExceptFirst($ary) {
		$new_first = array_shift($ary);
		$new_last = array_shift($ary);
		array_push ($ary, $new_last);
		array_unshift ($ary, $new_first);
		return $ary;
	}

	protected static function compareRating(Team $a, Team $b) {
		if ($a->rating < $b->rating) {
			return 1;
		} else if ($a->rating > $b->rating) {
			return -1;
		}

		return parent::compareTeams($a, $b, []);
	}

}
