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

$url = Router::url(['controller' => 'Games', 'action' => 'unassign_official', '?' => ['game' => $game->id]], true);
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('You have been assigned by your captain to officiate the game at {0} starting at {1} on {2}.',
	$game->game_slot->field->long_name,
	$this->Time->time($game->game_slot->game_start),
	$this->Time->date($game->start_time)
) ?>


<?= __('If you are unable to officiate this game, you can {0}; a short explanation of why will be required.', __('remove yourself')) ?>

<?= $url ?>


<?= $this->element('email/text/footer');
