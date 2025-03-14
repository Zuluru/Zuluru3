<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game $game
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Team $opponent
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Attendance $attendance
 */

use Cake\Core\Configure;
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

	$arg = 'game';
	$val = $game->id;
} else {
	$game_text = '';
	$arg = 'date';
	$val = $date->toDateString();
}
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('{0} has indicated that you are {1} the {2} game{3} on {4}.',
	$captain,
	Configure::read("attendance_verb.{$attendance->status}"),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
	$game_text,
	$this->Time->date($date)
) ?></p>
<?php
if ($attendance->has('note')):
?>
<p><?= $attendance->note ?></p>
<?php
endif;
?>
<p><b><?= __('If this correctly reflects your current status, you do not need to take any action at this time.') ?></b> <?= __('To update your status, use one of the links below, or visit the web site at any time.') ?></p>
<?php
if ($attendance->status == ATTENDANCE_INVITED):
?>
<p><?= __('Keep in mind that when teams are short, coaches and captains will often invite a number of people to fill in, so it\'s possible that even if you confirm attendance now, you might be uninvited later if others responded first. You will receive another email from the system in this case, but you may want to double-check with your coach or captain that you are needed before the game.') ?></p>
<?php
elseif ($attendance->status == ATTENDANCE_AVAILABLE):
?>
<p><?= __('Remember that just because you are available for this game doesn\'t mean that the team will need you. The coach or captain should indicate this by changing you to "attending" or "absent" once they know for sure, at which time you will receive another email from the system. If you do not receive this email, you may want to check with your coach or captain through other channels.') ?></p>
<?php
endif;

$url_array = [
	'controller' => 'Games',
	'action' => 'attendance_change',
	'?' => ['team' => $team->id, $arg => $val, 'person' => $person->id, 'code' => $code]
];
foreach (Configure::read('attendance_verb') as $check_status => $check_verb):
	if ($attendance->status != $check_status && array_key_exists($check_status, $player_options)):
		$url_array['?']['status'] = $check_status;
?>
<p><?= __('If you are {0} this game, {1}.', $check_verb, $this->Html->link(__('click here'), Router::url($url_array, true))) ?></p>
<?php
	endif;
endforeach;
?>
<?= $this->element('email/html/footer');
