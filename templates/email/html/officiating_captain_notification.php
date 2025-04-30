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
<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('{0} has indicated that they are unable to officiate the game at {1} starting at {2} on {3}.',
	$person->full_name,
	$this->Html->link($game->game_slot->field->long_name,
		Router::url(['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]], true)),
	$this->Html->link($this->Time->time($game->game_slot->game_start),
		Router::url(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], true)),
	$this->Time->date($game->start_time)
) ?></p>
<p><?= __('The reason given is: {0}', $reason) ?></p>
<p><?= __('Please {0} at your earliest convenience.',
	$this->Html->link(__('assign a replacement'), $url)
) ?></p>
<?= $this->element('email/html/footer');
