<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Team $opponent
 * @var \App\Model\Entity\Person $person
 */

use Cake\Routing\Router;
?>

<p><?= __('{0} has added a note about the {1} game against {2} at {3} starting at {4} on {5}.',
	$person->full_name,
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	$this->Html->link($opponent->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $opponent->id], true)),
	$this->Html->link($game->game_slot->field->long_name, Router::url(['controller' => 'Facilities', 'action' => 'view', 'facility' => $game->game_slot->field->facility_id], true)),
	$this->Time->time($game->game_slot->game_start),
	$this->Time->date($game->game_slot->game_date)
) ?></p>
<?= $note->note ?>
<p><?= __('To see all game details and notes, or add your own comment, see the {0}.',
	$this->Html->link('game details page', Router::url(['controller' => 'Games', 'action' => 'view', 'game' => $game->id], true))
) ?></p>
<?= $this->element('Email/html/footer');
