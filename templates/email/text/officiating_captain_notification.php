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

$url = Router::url(['controller' => 'Games', 'action' => 'assign_official', '?' => ['game' => $game->id]], true);
?>

<?= __('Dear {0},', $captains) ?>


<?= __('{0} has indicated that they are unable to officiate the game at {1} starting at {2} on {3}.',
	$person->full_name,
	$game->game_slot->field->long_name,
	$this->Time->time($game->game_slot->game_start),
	$this->Time->date($game->start_time)
) ?>


<?= __('The reason given is: {0}', $reason) ?>


<?= __('Please {0} at your earliest convenience.', __('assign a replacement')) ?>

<?= $url ?>


<?= $this->element('email/text/footer');
