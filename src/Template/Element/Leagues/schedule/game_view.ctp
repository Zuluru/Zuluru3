<?php
/**
 * @type \App\Model\Entity\Game $game
 * @type boolean $is_tournament
 * @type boolean $multi_day
 * @type boolean $same_date
 * @type boolean $same_slot
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
		echo $this->Html->link($this->Time->timeRange($game->game_slot), ['controller' => 'Games', 'action' => 'view', 'game' => $game->id]);
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
?>
	<td class="actions"><?php
	if (isset($division)) {
		echo $this->Game->displayScore($game, $division, $division->league);
	} else {
		echo $this->Game->displayScore($game, $game->division, $league);
	}
	?></td>
</tr>
