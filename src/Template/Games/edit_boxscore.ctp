<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->Html->addCrumb(__('Games'));
$this->Html->addCrumb(__('{0} vs {1}', $game->home_team->name, $game->away_team->name));
$this->Html->addCrumb(__('Edit Box Score'));

$team_names = [
	$game->home_team->id => $game->home_team->name,
	$game->away_team->id => $game->away_team->name
];

$roster = [];
if (!empty($game->division->league->stat_types)) {
	// Build the roster options
	foreach (['home_team', 'away_team'] as $key) {
		$roster[$game->$key->id] = collection($game->$key->people)->combine('id', function ($person) {
			$option = $person->full_name;
			if (Configure::read('feature.shirt_numbers') && $person->_joinData->number !== null && $person->_joinData->number !== '') {
				$option = "{$person->_joinData->number} $option";
				if ($person->_joinData->number < 10) {
					$option = " $option";
				}
			}
			return $option;
		})->toArray();
		asort($roster[$game->$key->id]);
	}
}
?>

<div class="games form">
<h2><?= __('Edit Box Score') ?></h2>
<?= $this->Form->create($game, ['align' => 'horizontal']) ?>
<div class="table-responsive">
<table class="table table-striped table-hover table-condensed">
	<thead>
		<tr>
			<th><?= __('Team') ?></th>
			<th><?= __('Time') ?></th>
			<th><?= __('Play') ?></th>
<?php
foreach($game->division->league->stat_types as $stat):
?>
			<th><?= __(Inflector::singularize($stat->name)) ?></th>
<?php
endforeach;
?>
			<th><?= __('Score') ?></th>
			<th><?= __('Actions') ?></th>
		</tr>
	</thead>
	<tbody>
<?php
$scores = [$game->home_team->id => 0, $game->away_team->id => 0];

$created = new \Cake\I18n\FrozenTime();
foreach ($game->score_details as $detail) {
	if ($detail->points) {
		$scores[$detail->team_id] += $detail->points;
	}
	echo $this->element('Games/edit_boxscore_line', [
		'detail' => $detail,
		'scores' => $scores,
		'year' => $game->game_slot->game_date->year,
		'month' => $game->game_slot->game_date->month,
		'day' => $game->game_slot->game_date->day,
		'team_names' => $team_names,
		'roster' => $roster,
	]);

	$created = $detail->created;
}
?>
		<tr id="add_row">
			<td><?php
			echo $this->Form->input('add_detail.team_id', [
				'type' => 'select',
				'options' => $team_names,
				'empty' => '---',
				'label' => false,
			]);
			?></td>
			<td><?php
			echo $this->Form->hidden('add_detail.created.year', ['value' => $game->game_slot->game_date->year]);
			echo $this->Form->hidden('add_detail.created.month', ['value' => $game->game_slot->game_date->month]);
			echo $this->Form->hidden('add_detail.created.day', ['value' => $game->game_slot->game_date->day]);
			echo $this->Form->input('add_detail.created', [
				'type' => 'time',
				// This will use the time of the previous detail as the default
				'value' => $created,
				'label' => false,
			]);
			?></td>
			<td><?php
			echo $this->Form->input('add_detail.play', [
				'options' => array_merge(\App\Config\make_options(array_merge(array_keys(Configure::read("sports.{$game->division->league->sport}.score_options")), ['Start', 'Timeout'])), Configure::read("sports.{$game->division->league->sport}.other_options")),
				'empty' => '---',
				'label' => false,
			]);
			?></td>
<?php
foreach($game->division->league->stat_types as $stat):
?>
			<td></td>
<?php
endforeach;
?>
			<td></td>
			<td><?php
			echo $this->Jquery->ajaxButton($this->Html->iconImg('add_24.png', ['alt' => __('Add Score Detail'), 'title' => __('Add Score Detail')]), [
				'url' => ['action' => 'add_score', 'game' => $game->id],
				'selector' => '#add_row',
				'input-selector' => '#add_row :input',
				'disposition' => 'before',
			]);
			?></td>
		</tr>
	</tbody>
</table>
</div>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
