<?php
/**
 * Entity-like class for managing a team's results
 */

namespace App\Model\Results;

use App\Model\Entity\Game;
use App\Model\Entity\League;
use App\Model\Entity\Team;
use App\Module\Spirit;
use App\Module\Sport;
use App\Service\Games\SpiritService;
use Cake\Datasource\EntityTrait;

/**
 * Class TeamResults
 * @property \App\Model\Results\RoundRobinRecord $season
 * @property array $pools
 * @property array $brackets
 */
class TeamResults {

	use EntityTrait;

	public function addGame(Game $game, Team $team, League $league, ?Spirit $spirit_obj, Sport $sport_obj) {
		$spirit_service = new SpiritService($game->spirit_entries ?? [], $spirit_obj);

		switch ($game->type) {
			case SEASON_GAME:
				if (!$this->has('season')) {
					$this->season = new RoundRobinRecord($team->id);
				}
				if ($game->home_team_id == $team->id) {
					$this->_addResultPlaceholder($this->season, $game->away_team_id);
					$this->_addRoundPlaceholder($this->season, $team->id, $game->away_team_id, $game->round);
					if ($game->isFinalized()) {
						$this->season->addResult(
							$game->away_team_id, $game->home_score, $game->away_score, $game->home_carbon_flip,
							$spirit_service->getScoreFor($game->home_team_id, $league), $sport_obj,
							$game->status == 'home_default', $game->status == 'normal'
						);
						$this->season->rounds[$game->round]->addResult(
							$game->away_team_id, $game->home_score, $game->away_score, $game->home_carbon_flip,
							$spirit_service->getScoreFor($game->home_team_id, $league), $sport_obj,
							$game->status == 'home_default', $game->status == 'normal'
						);
					}
				} else {
					$this->_addResultPlaceholder($this->season, $game->home_team_id);
					$this->_addRoundPlaceholder($this->season, $team->id, $game->home_team_id, $game->round);
					if ($game->isFinalized()) {
						$this->season->addResult(
							$game->home_team_id, $game->away_score, $game->home_score, 2 - $game->home_carbon_flip,
							$spirit_service->getScoreFor($game->away_team_id, $league), $sport_obj,
							$game->status == 'away_default', $game->status == 'normal'
						);
						$this->season->rounds[$game->round]->addResult(
							$game->home_team_id, $game->away_score, $game->home_score, 2 - $game->home_carbon_flip,
							$spirit_service->getScoreFor($game->away_team_id, $league), $sport_obj,
							$game->status == 'away_default', $game->status == 'normal'
						);
					}
				}
				break;

			case POOL_PLAY_GAME:
				if (!$this->has('pools')) {
					$this->pools = [];
				}
				if (empty($this->pools[$game->home_pool_team->pool->stage][$game->pool_id])) {
					$this->pools[$game->home_pool_team->pool->stage][$game->pool_id] = new RoundRobinRecord($team->id);
					$this->pools[$game->home_pool_team->pool->stage][$game->pool_id]->initial_seed = $team->initial_seed;
					ksort($this->pools);
				}
				if ($game->home_team_id == $team->id) {
					$this->_addResultPlaceholder($this->pools[$game->home_pool_team->pool->stage][$game->pool_id], $game->away_team_id);
					if ($game->isFinalized()) {
						$this->pools[$game->home_pool_team->pool->stage][$game->pool_id]->addResult(
							$game->away_team_id, $game->home_score, $game->away_score, $game->home_carbon_flip,
							$spirit_service->getScoreFor($game->home_team_id, $league), $sport_obj,
							$game->status == 'home_default', $game->status == 'normal'
						);
					}
				} else {
					$this->_addResultPlaceholder($this->pools[$game->away_pool_team->pool->stage][$game->pool_id], $game->home_team_id);
					if ($game->isFinalized()) {
						$this->pools[$game->away_pool_team->pool->stage][$game->pool_id]->addResult(
							$game->home_team_id, $game->away_score, $game->home_score, 2 - $game->away_carbon_flip,
							$spirit_service->getScoreFor($game->away_team_id, $league), $sport_obj,
							$game->status == 'away_default', $game->status == 'normal'
						);
					}
				}
				break;

			case BRACKET_GAME:
				if (!$this->has('brackets')) {
					$this->brackets = [];
				}

				if (!array_key_exists($game->pool_id, $this->brackets)) {
					$this->brackets[$game->pool_id] = new BracketRecord();
				}
				$record = $this->brackets[$game->pool_id];
				if ($game->home_team_id == $team->id) {
					$record->addResult($game->home_score, $game->away_score, $game->pool_id, $game->round, $game->placement);
				} else {
					$record->addResult($game->away_score, $game->home_score, $game->pool_id, $game->round, $game->placement);
				}
				break;
		}
	}

	static private function _addResultPlaceholder($container, $opp) {
		if (!$opp) {
			return;
		}

		// Make sure a record exists for the opponent in the vs arrays
		if (!array_key_exists($opp, $container->vs)) {
			$container->vs[$opp] = 0;
			$container->vspm[$opp] = 0;
		}
	}

	static private function _addRoundPlaceholder($container, $team_id, $opp, $round) {
		// Make sure a record exists for the round in the results
		// Some league types don't use rounds, but there's no real harm in calculating this
		if (!array_key_exists($round, $container->rounds)) {
			$container->rounds[$round] = new RoundRobinRecord($team_id);
		}

		if (!$opp) {
			return;
		}

		// Make sure a record exists for the opponent in the vs arrays
		if (!array_key_exists($opp, $container->rounds[$round]->vs)) {
			$container->rounds[$round]->vs[$opp] = 0;
			$container->rounds[$round]->vspm[$opp] = 0;
		}
	}

}
