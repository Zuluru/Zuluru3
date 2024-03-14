<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

$this->Breadcrumbs->add(__('Divisions'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('Scores'));
?>

<div class="divisions scores">
<h2><?= __('Division Scores') . ': ' . $division->full_league_name ?></h2>

<?php
// We need a list of all of the teams that have participated in games, as some may have moved
$all_teams = $division->teams;

// Rearrange game results into a nice array we can just dump out
$games = [];
foreach ($division->games as $game) {
	if ($game->isFinalized() && $game->status != 'rescheduled') {
		$home = $game->home_team_id;
		$away = $game->away_team_id;

		// Add the game to the home team's list
		if (!array_key_exists($home, $games)) {
			$games[$home] = [];
		}
		if (!array_key_exists($away, $games[$home])) {
			$games[$home][$away] = [];
		}
		$games[$home][$away][] = $game;

		// Add the game to the away team's list
		if (!array_key_exists($away, $games)) {
			$games[$away] = [];
		}
		if (!array_key_exists($home, $games[$away])) {
			$games[$away][$home] = [];
		}
		$games[$away][$home][] = $game;

		// Make sure both teams are in the all_teams list
		if (!array_key_exists($home, $all_teams)) {
			$all_teams[$home] = $game->home_team;
		}
		if (!array_key_exists($away, $all_teams)) {
			$all_teams[$away] = $game->away_team;
		}
	}
}
$header = [null];
foreach ($all_teams as $team_id => $team) {
	$header[] = $this->element('Teams/block', ['team' => $team, 'max_length' => 16, 'show_shirt' => false]);
}
$header[] = null;

?>

<div class="table-responsive">
<table class="table table-striped table-hover table-condensed">
<thead>
<?= $this->Html->tableHeaders($header) ?>
</thead>
<tbody>
<?php
$rows = [];
// Down the left side, we only list teams currently in the division
foreach ($division->teams as $team_id => $team) {
	$link = $this->Html->link($team->name, ['controller' => 'Teams', 'action' => 'schedule', 'team' => $team_id]);
	$row = [$link];
	// In each row, we want all teams included
	foreach ($all_teams as $opp_id => $opp) {
		if ($team_id == $opp_id) {
			$row[] = ['N/A', ['style' => 'color: gray;']];
		} else if (array_key_exists($team_id, $games) && array_key_exists($opp_id, $games[$team_id])) {
			$results = [];
			$wins = $losses = 0;
			foreach ($games[$team_id][$opp_id] as $game) {
				switch($game->status) {
					case 'home_default':
						$game_score = '(default)';
						$game_result = "{$game->home_team->name} defaulted";
						break;
					case 'away_default':
						$game_score = '(default)';
						$game_result = "{$game->away_team->name} defaulted";
						break;
					case 'forfeit':
						$game_score = '(forfeit)';
						$game_result = 'forfeit';
						break;
					default: //normal finalized game
						if($game->home_team_id == $team_id) {
							$game_score = "{$game->home_score}-{$game->away_score}";
							if ($game->home_score > $game->away_score) {
								$wins++;
							} else if ($game->home_score < $game->away_score) {
								$losses++;
							}
						} else {
							$game_score = "{$game->away_score}-{$game->home_score}";
							if ($game->away_score > $game->home_score) {
								$wins++;
							} else if ($game->away_score < $game->home_score) {
								$losses++;
							}
						}
						if ($game->home_score > $game->away_score) {
							$game_result = "{$game->home_team->name} defeated {$game->away_team->name} {$game->home_score}-{$game->away_score}";
						} else if ($game->home_score < $game->away_score) {
							$game_result = "{$game->away_team->name} defeated {$game->home_team->name} {$game->away_score}-{$game->home_score}";
						} else {
							$game_result = "{$game->home_team->name} and {$game->away_team->name} tied $game_score";
						}
						$game_result .= " ({$game->rating_points} rating points transferred)";
				}

				$popup = $this->Time->date($game->game_slot->game_date) . " at {$game->game_slot->field->long_code}: $game_result";

				$results[] = $this->Html->link($game_score, ['controller' => 'Games', 'action' => 'view', 'game' => $game->id], ['title' => $popup]);
			}
			$cell = implode('<br />', $results);
			if ($wins > $losses) {
				$row[] = [$cell, ['class'=>'winning']];
			} else if ($wins < $losses) {
				$row[] = [$cell, ['class'=>'losing']];
			} else {
				$row[] = $cell;
			}
		} else {
			$row[] = null;
		}
	}
	$row[] = $link;
	$rows[] = $row;
}

echo $this->Html->tableCells($rows);
?>

</tbody>
<tfoot>
<?= $this->Html->tableHeaders($header) ?>
</tfoot>
</table>
</div>

<p><?= __('Below the diagonal, scores are listed with the first score belonging the team whose name appears on the left. Above the diagonal, the first score belongs the teams across the top.') ?>
<br /><?= __('Green backgrounds means row team is winning season series, red means column team is winning series. Defaulted games are not counted.') ?></p>

</div>
