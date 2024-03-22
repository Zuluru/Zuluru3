<?php

/**
 * Derived class for implementing functionality for leagues based on group competitions rather than head-to-head games.
 */
namespace App\Module;

use App\Exception\ScheduleException;
use App\Model\Entity\Division;
use App\Model\Entity\Team;
use Authorization\IdentityInterface;

class LeagueTypeCompetition extends LeagueType {
	/**
	 * Define the element to use for rendering various views
	 */
	public $render_element = 'competition';

	public function newTeam() {
		return [
			'initial_rating' => 0,
			'rating' => 0,
		];
	}

	public function links(Division $division, IdentityInterface $identity = null, $controller, $action) {
		$links = parent::links($division, $identity, $controller, $action);
		if (($controller !== 'Divisions' || $action !== 'ratings') && $identity && $identity->can('edit_schedule', $division)) {
			$links[__('Adjust Ratings')] = [
				'url' => ['controller' => 'Divisions', 'action' => 'ratings', '?' => ['division' => $division->id]],
			];
		}
		return $links;
	}

	/**
	 * Sort a competition division by ratings (lower total is better), then base stuff.
	 * We don't use compareTeamsTieBreakers here, because it looks at things like wins,
	 * which are meaningless here.
	 */
	public static function compareTeams(Team $a, Team $b, array $context) {
		if ($a->rating < $b->rating) {
			return 1;
		} else if ($a->rating > $b->rating) {
			return -1;
		}

		return parent::compareTeams($a, $b, $context);
	}

	public function scheduleOptions($num_teams, $stage, $sport) {
		$types = [
			'single' => __('Single blank, unscheduled game'),
			'blankset' => __('Set of blank unscheduled games for all teams in a division ({0} teams, {1} games, one day)', $num_teams, $num_teams),
			'oneset' => __('Set of randomly scheduled games for all teams in a division ({0} teams, {1} games, one day)', $num_teams, $num_teams),
		];

		return $types;
	}

	public function scheduleRequirements($type, $num_teams) {
		switch($type) {
			case 'single':
				return [1];
			case 'blankset':
			case 'oneset':
				return [$num_teams];
		}
	}

	public function createSchedule(Division $division, $pool) {
		if ($pool) {
			throw new ScheduleException('Unexpected pool information in competition scheduler!');
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
		}

		$this->finishSchedule($division, $games);
	}

	/*
	 * Create an empty set of games for this division
	 */
	public function createEmptySet(Division $division, $date) {
		$num_teams = count($division->teams);

		// Now, create our games.  Don't add any teams, or set a round,
		// or anything, just randomly allocate a game slot.
		$games = [];
		for ($i = 0; $i < $num_teams; ++$i) {
			$games[] = $this->createEmptyGame($division, $date);
		}
		return $games;
	}

	/*
	 * Create a scheduled set of games for this division
	 */
	public function createScheduledSet(Division $division, $date) {
		// We build a temporary array of games, and add them to the completed list when they're ready
		$games = [];

		// Create a game for each team
		foreach ($division->teams as $team) {
			$games[] = ['home_team_id' => $team->id];
		}

		// Iterate over all newly-created games, and assign fields based on region preference.
		return $this->assignFieldsByPreferences($division, $date, $games);
	}
}
