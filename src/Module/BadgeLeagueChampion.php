<?php

/**
 * Implementation of the game callback for the "league champion" badge.
 */
namespace App\Module;

class BadgeLeagueChampion extends Badge {

	public function applicable($game, $team_id) {
		if ($game->isFinalized() && $game->type == BRACKET_GAME && $game->placement == 1) {
			if ($game->home_team_id == $team_id && $game->home_score > $game->away_score) {
				return true;
			}
			if ($game->away_team_id == $team_id && $game->away_score > $game->home_score) {
				return true;
			}
		}
		return false;
	}

}
