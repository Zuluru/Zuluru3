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
		$opponent->name,
		$game->game_slot->field->long_name . __(' ({0})', Router::url(['controller' => 'Facilities', 'action' => 'view', '?' => ['facility' => $game->game_slot->field->facility_id]], true)),
		$this->Time->time($game->game_slot->game_start)
	);

	$arg = 'game';
	$val = $game->id;
} else {
	$game_text = '';
	$arg = 'date';
	$val = $date->toDateString();
}
?>

<?= __('Dear {0},', $captains) ?>


<?= __('{0} has indicated that they will be {1} the {2} game{3} on {4}',
	$person->full_name,
	Configure::read("attendance_verb.{$attendance->status}"),
	$team->name,
	$game_text,
	$this->Time->date($date)
) ?>


<?php
if (!empty($attendance->comment)):
?>
<?= $attendance->comment ?>


<?php
endif;

if ($attendance->status == ATTENDANCE_AVAILABLE):
?>
<?= __('If you need {0} for this game:', $person->first_name) ?>

<?= Router::url(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['team' => $team->id, $arg => $val, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ATTENDING]], true) ?>


<?= __('If you know <b>for sure</b> that you don\'t need {0} for this game:', $person->first_name) ?>

<?= Router::url(['controller' => 'Games', 'action' => 'attendance_change', '?' => ['team' => $team->id, $arg => $val, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ABSENT]], true) ?>


<?= __('Either of these actions will generate an automatic email to {0} indicating your selection. If you are unsure whether you will need {0} for this game, it\'s best to leave them listed as available, and take action later when you know for sure. You can always update their status on the web site, there is no need to keep this email for that purpose.',
	$person->first_name
) ?>


<?php
endif;
?>
<?= __('You can also check up-to-the-minute details here:') ?>

<?= Router::url(['controller' => 'Games', 'action' => 'attendance', '?' => ['team' => $team->id, 'game' => $game->id]], true) ?>


<?= __('You need to be logged into the website to update this.') ?>


<?= $this->element('email/text/footer');
