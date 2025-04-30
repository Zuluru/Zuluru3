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

<?= __('Dear {0},', $person->first_name) ?>


<?= __('You have been replaced as the official for the game at {0} starting at {1} on {2}.',
	$game->game_slot->field->long_name,
	$this->Time->time($game->game_slot->game_start),
	$this->Time->date($game->start_time)
) ?>


<?= __('There is nothing for you to do here, this is just a notification.') ?>


<?= $this->element('email/text/footer');
