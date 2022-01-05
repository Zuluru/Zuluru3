<?php
/**
 * Base class for ratings calculators.
 */
namespace App\Module;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Model\Entity\Division;

abstract class Ratings {
	/**
	 * Indicates whether ratings are calculated on a per-game basis, vs strength-of-schedule calculators
	 *
	 * @var bool
	 */
	protected $per_game_ratings = true;
	public function perGameRatings() {
		return $this->per_game_ratings;
	}

	/**
	 * These settings are only used if per_game_ratings is false.
	 * These defaults are good for the majority of such calculators.
	 */

	/**
	 * Indicates whether the calculation needs to be run multiple times to converge to an answer.
	 *
	 * @var bool
	 */
	protected $iterative = true;

	/**
	 * Indicates how may iterations should be run, if it's iterative.
	 *
	 * @var int
	 */
	protected $iterations = 20;

	/**
	 * Schedule type of the division in question. Needed for some calculations.
	 *
	 * @var string
	 *
	 * TODO: Bad things may happen if there is ever a "competition" division in the same league as a head-to-head division,
	 * but that's a bigger problem than just recalculating some ratings.
	 */
	protected $scheduleType;

	/**
	 * The ID of the league that we have data for. This process is often run for multiple divisions, but the
	 * calculations must be done league-wide to account for teams that move in mid-season. No sense re-doing
	 * all that math.
	 *
	 * @var int
	 */
	protected $leagueId = null;

	/**
	 * @var \App\Model\Entity\Game[]
	 */
	protected $games;

	/**
	 * By default, we don't track ratings. This will apply to round-robin leagues, for example.
	 */
	public function calculateRatingsChange($home_score, $away_score, $expected_win) {
		return 0;
	}

	/**
	 * Calculate the expected win ratio.  Answer
	 * is always 0 <= x <= 1
	 */
	public function calculateExpectedWin ($rating1, $rating2) {
		$difference = $rating1 - $rating2;
		$power = pow(10, (0 - $difference) / 400);
		return ( 1 / ($power + 1) );
	}

	public function recalculateRatings(Division $division) {
		if ($division->league_id != $this->leagueId) {
			$this->leagueId = $division->league_id;

			// Do the actual calculations
			if (!$this->initializeRatings($division)) {
				// We still need to save any changes to the division details
				// TODO: Eliminate code duplication between here and in saveRatings.
				if ($division->isDirty()) {
					$divisions_table = TableRegistry::getTableLocator()->get('Divisions');
					if (!$divisions_table->save($division, ['update_badges' => false])) {
						return false;
					}
				}

				return true;
			}
			if ($this->per_game_ratings) {
				$this->recalculateGameRatings();
			} else {
				if ($this->iterative) {
					for ($it = 0; $it < $this->iterations; ++$it) {
						$this->recalculateGameRatings();
					}
				} else {
					$this->recalculateGameRatings();
				}
			}
			$this->finalizeRatings();
		}

		return $this->saveRatings($division);
	}

	/**
	 * Initialize for ratings recalculation.
	 * The default implementation reads required data and sets up an empty array for tracking.
	 */
	protected function initializeRatings(Division $division) {
		$this->scheduleType = $division->schedule_type;

		$divisions_table = TableRegistry::getTableLocator()->get('Divisions');

		// Find all finalized games played by teams that are anywhere in this division's league.
		// Results of games from other divisions can still affect ratings in this division.
		$divisions = $divisions_table->Leagues->divisions($division->league_id);
		if ($division->has('teams')) {
			// When we already have a team list, it might have updated initial ratings that
			// we need to preserve for all these calculations.
			$this->teams = $divisions_table->Teams->find()
				->where(['division_id IN' => $divisions, 'division_id !=' => $division->id])
				->append($division->teams)
				->indexBy('id')
				->toArray();
		} else {
			$this->teams = $divisions_table->Teams->find()
				->where(['division_id IN' => $divisions])
				->indexBy('id')
				->toArray();
		}
		if (empty($this->teams)) {
			return false;
		}

		foreach ($this->teams as $team) {
			$team->current_rating = $team->initial_rating;
		}

		$this->games = $divisions_table->Games->find()
			->contain(['GameSlots'])
			->where([
				'OR' => [
					'home_team_id IN' => array_keys($this->teams),
					'away_team_id IN' => array_keys($this->teams),
				],
				'home_score IS NOT' => null,
				'status NOT IN' => ['rescheduled', 'cancelled'],
			])
			->toArray();
		if (empty($this->games)) {
			$this->saveRatings($division);
			return false;
		}

		// Sort games by date, time and field
		usort($this->games, ['App\Model\Table\GamesTable', 'compareDateAndField']);

		return true;
	}

	/**
	 * Recalculate ratings for all teams in a division. This default
	 * implementation works for all calculators where rating points are
	 * transferred on a per-game basis (i.e. per_game_ratings = true).
	 */
	protected function recalculateGameRatings() {
		foreach ($this->games as $game) {
			if ($game->type != SEASON_GAME) {
				// Playoff games don't adjust ratings
				$change = $home_change = 0;
			} else if (strpos($game->status, 'default') !== false && !Configure::read('scoring.default_transfer_ratings')) {
				// Defaulted games might not adjust ratings
				$change = $home_change = 0;
			} else if ($this->scheduleType == 'competition') {
				$change = $home_change = $this->calculateRatingsChange($game->home_score, 0, 0);
			} else {
				if (!array_key_exists($game->away_team_id, $this->teams) || !array_key_exists($game->home_team_id, $this->teams)) {
					// TODO: Can we do better in the case of an unknown opponent?
					$change = $home_change = 0;
				} else if ($game->home_score >= $game->away_score) {
					$expected = $this->calculateExpectedWin($this->teams[$game->home_team_id]->current_rating, $this->teams[$game->away_team_id]->current_rating);
					$change = $home_change = $this->calculateRatingsChange($game->home_score, $game->away_score, $expected);
				} else {
					$expected = $this->calculateExpectedWin($this->teams[$game->away_team_id]->current_rating, $this->teams[$game->home_team_id]->current_rating);
					$change = $this->calculateRatingsChange($game->home_score, $game->away_score, $expected);
					$home_change = -$change;
				}
			}

			if ($home_change != 0) {
				$this->teams[$game->home_team_id]->current_rating += $home_change;
				if ($this->scheduleType != 'competition') {
					$this->teams[$game->away_team_id]->current_rating -= $home_change;
				}
			}
			if ($game->rating_points != $change) {
				$game->rating_points = $change;
			}
		}
	}

	/**
	 * Finalize ratings recalculation. Might be used to normalize ratings, for example.
	 */
	protected function finalizeRatings() {
	}

	/**
	 * Copy any ratings changes into the team and game records and saves them.
	 */
	protected function saveRatings(Division $division) {
		$team_count = 0;

		// When editing the division, we don't load the team list.
		if (!$division->has('teams')) {
			$division->teams = collection($this->teams)->match(['division_id' => $division->id])->toArray();
		}

		foreach ($division->teams as $team) {
			if ($this->teams[$team->id]->has('current_rating') && $this->teams[$team->id]->current_rating != $team->rating) {
				$team->rating = $this->teams[$team->id]->current_rating;
				$division->setDirty('teams', true);
				++$team_count;
			}
		}

		// If any team rating is dirty, reset ALL team seeds
		if ($division->isDirty('teams')) {
			foreach ($division->teams as $team) {
				$team->seed = 0;
			}
		}

		if (!empty($this->games)) {
			$division->games = [];
			foreach ($this->games as $game) {
				if ($game->division_id == $division->id && $game->isDirty()) {
					$division->games[] = $game;
				}
			}
		}

		// If nothing is dirty, no need to save or clear cache
		if ($division->isDirty()) {
			$divisions_table = TableRegistry::getTableLocator()->get('Divisions');
			if (!$divisions_table->save($division, ['update_badges' => false])) {
				return false;
			}
		}

		return true;
	}

}
