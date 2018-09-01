<?php
use Cake\Core\Configure;

if (isset($division)) {
	$games = $division->games;
	$competition = ($division->schedule_type == 'competition');
	$id = $division->id;
	$id_field = 'division';
} else {
	$games = $league->games;
	$competition = collection($league->divisions)->every(function ($division) { return $division->schedule_type == 'competition'; });
	$id = $league->id;
	$id_field = 'league';
}

// Spin through the games before building headers, to eliminate edit-type actions on completed weeks.
$finalized = true;
$published = false;
$is_tournament = $has_dependent_games = false;
foreach ($games as $game) {
	if ($game->game_slot->game_date->between($week[0], $week[1])) {
		$finalized &= $game->isFinalized();
		$published |= $game->published;
		$is_tournament |= ($game->type != SEASON_GAME);
		$has_dependent_games |= (!empty($game->home_pool_team->dependency_type) || !empty($game->away_pool_team->dependency_type));
	}
}
if (!($published || Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator)) {
	return;
}

echo $this->element('Leagues/schedule/view_header', compact('division', 'league', 'week', 'competition', 'id_field', 'id', 'published', 'finalized', 'is_tournament', 'multi_day', 'has_dependent_games'));
?>

<?php
$last_date = $last_slot = null;
foreach ($games as $game):
	if (! ($game->published || Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator)) {
		continue;
	}
	if (!$game->game_slot->game_date->between($week[0], $week[1])) {
		continue;
	}
	$game->readDependencies();
	$same_date = ($game->game_slot->game_date === $last_date);
	$same_slot = ($game->game_slot->id === $last_slot);
	echo $this->element('Leagues/schedule/game_view', compact('division', 'league', 'game', 'competition', 'is_tournament', 'multi_day', 'same_date', 'same_slot'));
	$last_date = $game->game_slot->game_date;
	$last_slot = $game->game_slot->id;
endforeach;
