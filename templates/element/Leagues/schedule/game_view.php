<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var bool $is_tournament
 * @var bool $multi_day
 * @var bool $same_date
 * @var bool $same_slot
 * @var bool $competition
 * @var bool $show_officials
 */

?>
<tr<?= $game->published ? '' : ' class="unpublished"' ?>>
	<td><?= ($is_tournament && !$same_slot) ? $game->display_name : '' ?></td>
<?php
if ($multi_day):
?>
	<td><?php
	if (!$same_date) {
		echo $this->Time->day($game->game_slot->game_date);
	}
	?></td>
<?php
endif;
?>
	<td><?php
	if (!$same_slot) {
		echo $this->Html->link($this->Time->timeRange($game->game_slot), ['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]]);
	}
	?></td>
	<td><?= (!$same_slot) ? $this->element('Fields/block', ['field' => $game->game_slot->field]) : '' ?></td>
	<td><?php
	if (empty($game->home_team)) {
		if ($game->has('home_dependency')) {
			echo $game->home_dependency;
		} else {
			echo __('Unassigned');
		}
	} else {
		echo $this->element('Teams/block', ['team' => $game->home_team, 'options' => ['max_length' => 16]]);
	}
	?></td>
<?php
if (!$competition):
?>
	<td><?php
	if (empty($game->away_team)) {
		if ($game->has('away_dependency')) {
			echo $game->away_dependency;
		} else {
			echo __('Unassigned');
		}
	} else {
		echo $this->element('Teams/block', ['team' => $game->away_team, 'options' => ['max_length' => 16]]);
	}
	?></td>
<?php
endif;

if ($show_officials):
?>
	<td><?= $this->element('Games/officials', ['game' => $game, 'officials' => $game->officials, 'team_officials' => $game->team_officials, 'league' => $league]) ?></td>
<?php
endif;
?>
	<td class="actions"><?php
	if (isset($division)) {
		echo $this->Game->score($game, $division);
		echo $this->Game->actions($game, $division, $division->league);
	} else {
		echo $this->Game->score($game, $game->division);
		echo $this->Game->actions($game, $game->division, $league);
	}
	?></td>
</tr>
