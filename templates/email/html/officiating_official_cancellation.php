<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 * @var string $captains
 * @var string $reason
 */

use Cake\Routing\Router;
?>
<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('You have been replaced as the official for the game at {0} starting at {1} on {2}.',
	$this->Html->link($game->game_slot->field->long_name,
		Router::url(['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]], true)),
	$this->Html->link($this->Time->time($game->game_slot->game_start),
		Router::url(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], true)),
	$this->Time->date($game->start_time)
) ?></p>
<p><?= __('There is nothing for you to do here, this is just a notification.') ?></p>
<?= $this->element('email/html/footer');
