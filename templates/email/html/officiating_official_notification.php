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
<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('You have been assigned by your captain to officiate the game at {0} starting at {1} on {2}.',
	$this->Html->link($game->game_slot->field->long_name,
		Router::url(['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]], true)),
	$this->Html->link($this->Time->time($game->game_slot->game_start),
		Router::url(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], true)),
	$this->Time->date($game->start_time)
) ?></p>
<p><?= __('If you are unable to officiate this game, you can {0}; a short explanation of why will be required.',
	$this->Html->link(__('remove yourself'), $url)
) ?></p>
<?= $this->element('email/html/footer');
