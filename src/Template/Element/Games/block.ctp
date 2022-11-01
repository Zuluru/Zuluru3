<?php
/**
 * @type $game \App\Model\Entity\Game
 * @type $game_slot \App\Model\Entity\GameSlot
 * @type $field string
 * @type $options mixed[]
 */

$id = "games_game_{$game->id}";

if (isset($options)) {
	$options = array_merge(['id' => $id, 'class' => 'trigger'], $options);
} else {
	$options = ['id' => $id, 'class' => 'trigger'];
}

if (isset($field)) {
	$display = $game->$field;
} else {
	$display = $this->Time->date($game_slot->game_date) . ' ' .
		$this->Time->time($game_slot->game_start);
}
echo $this->Html->link($display,
	['controller' => 'Games', 'action' => 'view', 'game' => $game->id],
	$options);
