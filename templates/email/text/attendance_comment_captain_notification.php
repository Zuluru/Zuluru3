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
		$opponent->name,
		$game->game_slot->field->long_name . __(' ({0})', Router::url(['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]], true)),
		$this->Time->time($game->game_slot->game_start)
	);
} else {
	$game_text = '';
}
?>

<?= __('Dear {0},', $captains) ?>


<?php
if (empty($attendance->comment)) {
	echo __('{0} has removed the comment from their attendance at the {1} game{2} on {3}.',
		$person->full_name,
		$team->name,
		$game_text,
		$this->Time->date($date)
	);
} else {
	echo __('{0} has added the following comment to their attendance at the {1} game{2} on {3}.',
		$person->full_name,
		$team->name,
		$game_text,
		$this->Time->date($date)
	);
}
?>


<?php
if (!empty($attendance->comment)):
?>
<?= $attendance->comment ?>


<?php
endif;
?>
<?= $this->element('email/text/footer');
