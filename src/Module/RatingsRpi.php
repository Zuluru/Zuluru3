<?php
/**
 * Derived class for implementing functionality for the RPI ratings calculator.
 */
namespace App\Module;

use Cake\Core\Configure;
use App\Model\Entity\Division;

class RatingsRpi extends Ratings {
	protected $per_game_ratings = false;
	protected $iterative = false;

	protected $results = [];
	protected $vs = [];

	// This method is a little awkward, but greatly simplifies the RRI implementation.
	protected function odds($home_score, $away_score) {
		if ($home_score > $away_score) {
			return 1;
		} else if ($home_score < $away_score) {
			return 0;
		} else {
			return 0.5;
		}
	}

	protected function initializeRatings(Division $division) {
		if (parent::initializeRatings($division)) {
			// Build some counters to make the later calculations trivial
			foreach ($this->games as $game) {
				if ($game->type == SEASON_GAME) {
					if (!array_key_exists($game->home_team_id, $this->results)) {
						$this->results[$game->home_team_id] = ['games' => 0, 'wins' => 0];
						$this->vs[$game->home_team_id] = [];
					}
					if (!array_key_exists($game->away_team_id, $this->results)) {
						$this->results[$game->away_team_id] = ['games' => 0, 'wins' => 0];
						$this->vs[$game->away_team_id] = [];
					}

					if (!array_key_exists($game->home_team_id, $this->vs[$game->away_team_id])) {
						$this->vs[$game->away_team_id][$game->home_team_id] = ['games' => 0, 'wins' => 0];
					}
					if (!array_key_exists($game->away_team_id, $this->vs[$game->home_team_id])) {
						$this->vs[$game->home_team_id][$game->away_team_id] = ['games' => 0, 'wins' => 0];
					}

					if (strpos($game->status, 'default') !== false && !Configure::read('default_transfer_ratings')) {
						// We might just ignore defaults
					} else {
						++$this->results[$game->home_team_id]['games'];
						++$this->results[$game->away_team_id]['games'];
						++$this->vs[$game->home_team_id][$game->away_team_id]['games'];
						++$this->vs[$game->away_team_id][$game->home_team_id]['games'];

						$home_odds = $this->odds($game->home_score, $game->away_score);
						$this->results[$game->home_team_id]['wins'] += $home_odds;
						$this->results[$game->away_team_id]['wins'] += (1 - $home_odds);
						$this->vs[$game->home_team_id][$game->away_team_id]['wins'] += $home_odds;
						$this->vs[$game->away_team_id][$game->home_team_id]['wins'] += (1 - $home_odds);
					}
				}
			}

			return true;
		}
	}

	protected function recalculateGameRatings() {
		foreach ($this->teams as $team) {
			if (!array_key_exists($team->id, $this->results)) {
				// If they haven't played yet, give them a neutral win percentage
				$team->current_rating = 1500;
			} else {
				// This will put teams in the range from 1000-2000, similar to other calculators
				$team->current_rating = intval(1000 * (
					0.25 * $this->wp($team->id) +
					0.50 * $this->owp($team->id) +
					0.25 * $this->oowp($team->id)
				)) + 1000;
			}
		}
	}

	protected function opponents($team_id) {
		$opponents = array_merge(
			collection($this->games)->filter(function ($game) use ($team_id) {
				return ($game->home_team_id == $team_id && $game->type == SEASON_GAME);
			})->extract('away_team_id')->toArray(),
			collection($this->games)->filter(function ($game) use ($team_id) {
				return ($game->away_team_id == $team_id && $game->type == SEASON_GAME);
			})->extract('home_team_id')->toArray()
		);

		return $opponents;
	}

	protected function wp($team_id, $ignore_id = false) {
		$wins = $this->results[$team_id]['wins'];
		$games = $this->results[$team_id]['games'];
		if ($ignore_id) {
			// Ignore results from games between these two teams
			if (!empty($this->vs[$team_id][$ignore_id]['games'])) {
				$wins -= $this->vs[$team_id][$ignore_id]['wins'];
				$games -= $this->vs[$team_id][$ignore_id]['games'];
				if ($games == 0) {
					// If they haven't played anyone else yet, give them a neutral win percentage
					return 0.5;
				}
			}
		}

		return $wins / $games;
	}

	protected function owp($team_id) {
		$sum = 0;
		$opponents = $this->opponents($team_id);
		foreach ($opponents as $opponent) {
			$sum += $this->wp($opponent, $team_id);
		}

		return $sum / count($opponents);
	}

	protected function oowp($team_id) {
		$sum = 0;
		$opponents = $this->opponents($team_id);
		foreach ($opponents as $opponent) {
			$sum += $this->owp($opponent);
		}

		return $sum / count($opponents);
	}

}
