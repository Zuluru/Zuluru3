<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Team $opponent
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Attendance $attendance
 */

use Cake\Routing\Router;

if (!$game->isNew()) {
	$game_text = __(' against {0} at {1} starting at {2}',
		$this->Html->link($opponent->name,
			Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $opponent->id]], true)),
		$this->Html->link($game->game_slot->field->long_name,
			Router::url(['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]], true)),
		$this->Html->link($this->Time->time($game->game_slot->game_start),
			Router::url(['controller' => 'Games', 'action' => 'view', '?' => ['game' => $game->id]], true))
	);
} else {
	$game_text = '';
}
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?php
if (empty($attendance->comment)) {
	echo __('{0} has removed the comment from their attendance at the {1} game{2} on {3}.',
		$person->full_name,
		$this->Html->link($team->name,
			Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
		$game_text,
		$this->Time->date($date)
	);
} else {
	echo __('{0} has added the following comment to their attendance at the {1} game{2} on {3}.',
		$person->full_name,
		$this->Html->link($team->name,
			Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
		$game_text,
		$this->Time->date($date)
	);
}
?></p>
<?php
if (!empty($attendance->comment)):
?>
<p><?= $attendance->comment ?></p>
<?php
endif;
?>
<?= $this->element('email/html/footer');
