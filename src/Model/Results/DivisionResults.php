<?php
/**
 * Entity-like class for managing a division's results
 */

namespace App\Model\Results;

use Cake\Datasource\EntityTrait;
use Cake\ORM\Entity;
use App\Model\Entity\Game;

/**
 * Class DivisionResults
 * @property array $pools
 * @property array $brackets
 */
class DivisionResults {

	use EntityTrait;

	private $bracket_games = [];

	public function addGame($game) {
		switch ($game->type) {
			case POOL_PLAY_GAME:
				if (!$this->has('pools')) {
					$this->pools = [];
				}
				if (empty($this->pools[$game->home_pool_team->pool->stage][$game->pool_id])) {
					$this->pools[$game->home_pool_team->pool->stage][$game->pool_id] = new Entity(['games' => []]);
					ksort($this->pools);
				}
				$this->pools[$game->home_pool_team->pool->stage][$game->pool_id]->games[] = $game;
				break;

			case BRACKET_GAME:
				$this->bracket_games[$game->id] = $game;
				break;
		}
	}

	public function finalize() {
		if (empty($this->bracket_games)) {
			return;
		}

		$this->brackets = [];
		ksort($this->bracket_games);

		while (!empty($this->bracket_games)) {
			$bracket = $this->extractBracket();

			// Find the bracket's pool id
			$pool_id = null;
			foreach ($bracket[0] as $game) {
				if (!empty($game->pool_id)) {
					$pool_id = $game->pool_id;
					break;
				}
			}
			$this->brackets[] = compact('pool_id', 'bracket');
		}
	}

	/**
	 * We know that when we create tournament schedules, we create the most
	 * important games first. So, when generating the bracket, we start with
	 * the lowest-id game in the last round and work backwards, finding all
	 * games that it depends on. As we place games in the bracket, we remove
	 * them from the list. Repeat until there are no games left in that round,
	 * and repeat that whole process until there are no rounds left.
	 * This assumes that $bracket_games is indexed by game id.
	 */
	public function extractBracket() {
		$bracket = [];

		// Find the "most important" remaining game to start the bracket
		// TODO: Add some kind of "bracket sort" field and use that instead
		$pools = array_unique(collection($this->bracket_games)->extract('pool_id')->toList());
		sort($pools);
		$pool = reset($pools);
		if ($pool === null) {
			// TODO: Figure out how to migrate this old tournament_pool data to the new format, and eliminate the field.
			$pools = array_unique(collection($this->bracket_games)->extract('tournament_pool')->toList());
			sort($pools);
			$pool = reset($pools);
			$pool_field = 'tournament_pool';
		} else {
			$pool_field = 'pool_id';
		}

		$pool_games = collection($this->bracket_games)->match([$pool_field => $pool])->toArray();
		usort($pool_games, [$this, 'compareGameName']);
		$final = current($pool_games);
		$bracket[$final->round] = [$final];
		unset($this->bracket_games[$final->id]);
		$round = $final->round;

		// Work backwards through previous rounds
		while ($round > 1) {
			$round_games = [];
			$empty = true;

			foreach ($bracket[$round] as $game) {
				if (in_array($game->home_dependency_type, ['game_winner', 'game_loser'])) {
					if (array_key_exists($game->home_dependency_id, $this->bracket_games)) {
						$round_games[] = $this->bracket_games[$game->home_dependency_id];
						$empty = false;
						unset($this->bracket_games[$game->home_dependency_id]);
					} else {
						$round_games[] = new Game();
					}
				} else {
					$round_games[] = new Game();
				}

				if (in_array($game->away_dependency_type, ['game_winner', 'game_loser'])) {
					if (array_key_exists($game->away_dependency_id, $this->bracket_games)) {
						$round_games[] = $this->bracket_games[$game->away_dependency_id];
						$empty = false;
						unset($this->bracket_games[$game->away_dependency_id]);
					} else {
						$round_games[] = new Game();
					}
				} else {
					$round_games[] = new Game();
				}
			}

			if ($empty) {
				break;
			}
			$bracket[--$round] = $round_games;
		}

		ksort($bracket);
		// For the class names to format this correctly, we need the rounds in
		// this bracket to be numbered from 0, regardless of what their real
		// round number is.
		return array_values($bracket);
	}

	public static function compareGameName($a, $b) {
		// First, check pool names, if they exist
		if (strpos($a->name, '-') !== false) {
			list($a_pool, ) = explode('-', $a->name);
			list($b_pool, ) = explode('-', $b->name);
			if ($a_pool < $b_pool) {
				return -1;
			} else if ($a_pool > $b_pool) {
				return 1;
			}
		}

		if ($a->placement && $b->placement) {
			if ($a->placement < $b->placement) {
				return -1;
			} else if ($a->placement > $b->placement) {
				return 1;
			}
		} else if ($a->placement && !$b->placement) {
			return -1;
		} else if (!$a->placement && $b->placement) {
			return 1;
		}

		if ($a->name < $b->name) {
			return -1;
		} else if ($a->name > $b->name) {
			return 1;
		}

		return 0;
	}

}
