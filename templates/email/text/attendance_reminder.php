<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Team $opponent
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Person $person
 * @var int $status
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

if ($opponent) {
	if (Configure::read('feature.shirt_colour') && !empty($opponent->shirt_colour)) {
		$shirt_text = __(' (they wear {0})', $opponent->shirt_colour);
	} else {
		$shirt_text = '';
	}
	$opponent_text = __(' against {0}', $opponent->name . $shirt_text);
} else {
	$opponent_text = '';
}
?>

<?= __('Dear {0},', $person->first_name) ?>


<?php
if ($status == ATTENDANCE_UNKNOWN || $status == ATTENDANCE_INVITED) {
	echo __('You have not yet indicated your attendance for the {0} game{1} at {2} from {3} to {4} on {5}.',
		$team->name,
		$opponent_text,
		$game->game_slot->field->long_name . __(' ({0})', Router::url(['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]], true)),
		$this->Time->time($game->game_slot->game_start),
		$this->Time->time($game->game_slot->display_game_end),
		$this->Time->date($game->game_slot->game_date)
	);
} else {
	echo __('You are currently listed as {0} for the {1} game{2} at {3} from {4} to {5} on {6}.',
		Configure::read("attendance.$status"),
		$team->name,
		$opponent_text,
		$game->game_slot->field->long_name . __(' ({0})', Router::url(['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]], true)),
		$this->Time->time($game->game_slot->game_start),
		$this->Time->time($game->game_slot->display_game_end),
		$this->Time->date($game->game_slot->game_date)
	);
}
?>


<?php
if ($status == ATTENDANCE_INVITED):
?>
<?= __('The coach or captain has invited you to play in this game. However, when teams are short, coaches and captains will often invite a number of people to fill in, so it\'s possible that even if you confirm now, you might be uninvited later if others responded first. You will receive another email from the system in this case, but you may want to double-check with your coach or captain that you are needed before the game.') ?>


<?php
endif;

if ($status == ATTENDANCE_INVITED || in_array($person->_joinData->role, Configure::read('regular_roster_roles'))):
?>
<?= __('If you are able to play:') ?>

<?= Router::url(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['team' => $team->id, 'game' => $game->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ATTENDING]], true) ?>


<?php
elseif ($status != ATTENDANCE_ATTENDING && !in_array($person->_joinData->role, Configure::read('regular_roster_roles'))):
?>
<?= __('If you are available to play:') ?>

<?= Router::url(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['team' => $team->id, 'game' => $game->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_AVAILABLE]], true) ?>


<?php
endif;
?>
<?= __('If you are unavailable to play:') ?>

<?= Router::url(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['team' => $team->id, 'game' => $game->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ABSENT]], true) ?>


<?= __('Note that you can {0}, giving your coach or captain advance notice of vacations or other planned absences.',
		__('set your attendance in advance')
	) . ' ' .
	__('You need to be logged into the website to update this.')
?>

<?= Router::url(['controller' => 'Teams', 'action' => 'attendance', '?' => ['team' => $team->id]], true) ?>


<?= $this->element('email/text/footer');
