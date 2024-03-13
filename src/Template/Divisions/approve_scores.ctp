<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use App\Controller\AppController;
use App\Model\Entity\ScoreEntry;

$this->Html->addCrumb(__('Divisions'));
$this->Html->addCrumb($division->full_league_name);
$this->Html->addCrumb(__('Approve Scores'));
?>

<div class="divisions approve_scores">
<h2><?= __('Approve Scores') . ': ' . $division->full_league_name ?></h2>

<div class="table-responsive">
<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th><?= __('Game Date') ?></th>
			<th colspan="2"><?= __('Home Team Submission') ?></th>
			<th colspan="2"><?= __('Away Team Submission') ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php
foreach ($division->games as $game):
	$game->readDependencies();

	if (array_key_exists($game->home_team_id, $game->score_entries)) {
		$home = $game->score_entries[$game->home_team_id];
	} else {
		$home = new ScoreEntry([
			'score_for' => __('not entered'),
			'score_against' => __('not entered'),
		]);
	}

	if (array_key_exists($game->away_team_id, $game->score_entries)) {
		$away = $game->score_entries[$game->away_team_id];
	} else {
		$away = new ScoreEntry([
			'score_for' => __('not entered'),
			'score_against' => __('not entered'),
		]);
	}
?>
		<tr>
			<td rowspan="3"><?= $this->Time->day($game->game_slot->game_date) . ', ' .
				$this->Time->time($game->game_slot->game_start) ?></td>
			<td colspan="2"><?php
			if ($game->home_team_id === null) {
				echo $game->home_dependency;
			} else {
				echo $game->home_team->name;
			}
			?></td>
			<td colspan="2"><?php
			if ($game->away_team_id === null) {
				echo $game->away_dependency;
			} else {
				echo $game->away_team->name;
			}
			?></td>
			<td><?= $this->Html->link(__('approve score'),
					['controller' => 'Games', 'action' => 'edit', 'game' => $game->id, 'return' => AppController::_return()]) ?></td>
		</tr>
		<tr>
			<td><?= __('Home Score') ?>:</td>
			<td><?= $home->score_for ?></td>
			<td><?= __('Home Score') ?>:</td>
			<td><?= $away->score_against ?></td>
			<td><?php
			// Tournament games may not have teams filled in
			if (!$game->home_team->has('people')) {
				$game->home_team->people = [];
			}
			if (!$game->away_team->has('people')) {
				$game->away_team->people = [];
			}
			$captains = array_merge(
				AppController::_extractEmails($game->home_team->people, false, true, true),
				AppController::_extractEmails($game->away_team->people, false, true, true)
			);
			if (!empty($captains)) {
				echo $this->Html->link(__('email coaches and captains'), 'mailto:' . implode(',', $captains));
			}
			?></td>
		</tr>
		<tr>
			<td><?= __('Away Score') ?>:</td>
			<td><?= $home->score_against ?></td>
			<td><?= __('Away Score') ?>:</td>
			<td><?= $away->score_for ?></td>
			<td></td>
		</tr>
<?php
endforeach;
?>
	</tbody>
</table>
</div>

</div>
