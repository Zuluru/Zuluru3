<?php
use Cake\Core\Configure;

/**
 * @type $games \App\Model\Entity\Game[]
 */

$return = [];
foreach ($games as $game) {
	$data = [
		'gameID' => $game->id,
		'leagueID' => $game->division->league_id,
		'leagueName' => $game->division->league->full_name,
		'divisionID' => $game->division_id,
		'divisionName' => $game->division->name,
		'divisionLongName' => $game->division->full_league_name,
		'gameDate' => $this->Time->day($game->game_slot->game_date),
		'gameStartTime' => $this->Time->time($game->game_slot->game_start),
		'gameStartTimestamp' => $game->game_slot->start_time->toUnixString(),
		'gameEndTime' => $this->Time->time($game->game_slot->display_game_end),
		'gameEndTimestamp' => $game->game_slot->end_time->toUnixString(),
		'facilityID' => $game->game_slot->field->facility_id,
		'facilityName' => $game->game_slot->field->facility->name,
		'facilityCode' => $game->game_slot->field->facility->code,
		'fieldID' => $game->game_slot->field_id,
		'fieldNum' => $game->game_slot->field->num,
		'status' => $game->status,
		'finalized' => $game->isFinalized(),
	];
	if ($game->isFinalized()) {
		$homeScore = $game->home_score;
		$awayScore = $game->away_score;
	} else {
		$entry = $game->getBestScoreEntry();
		if (empty($entry)) {
			$homeScore = 0;
			$awayScore = 0;
		} else if ($entry->team_id == $game->home_team_id || $entry->team_id === null) {
			$homeScore = $entry->score_for;
			$awayScore = $entry->score_against;
			if ($entry->status == 'in_progress') {
				$data['status'] = 'in_progress';
			}
		} else {
			$homeScore = $entry->score_against;
			$awayScore = $entry->score_for;
			if ($entry->status == 'in_progress') {
				$data['status'] = 'in_progress';
			}
		}
	}
	$data = array_merge($data, compact('homeScore', 'awayScore'));
	foreach (['home', 'away'] as $key) {
		$team = "{$key}_team";
		if ($game->$team === null) {
			$dependency = "{$key}_dependency";
			if ($game->has($dependency)) {
				$data["{$key}TeamName"] = $game->$dependency;
			} else {
				$data["{$key}TeamName"] = __('Unassigned');
			}
		} else {
			$team = $game->$team;
			$data = array_merge($data, [
				"{$key}TeamID" => $team->id,
				"{$key}TeamName" => $team->name,
				"{$key}TeamColour" => $team->shirt_colour,
			]);
			if (Configure::read('feature.shirt_colour') && $team->has('shirt_colour')) {
				$data["{$key}TeamShirtIcon"] = $this->element('shirt', ['colour' => $team->shirt_colour]);
			}
		}
	}
	$return[] = $data;
}
echo json_encode($return);
