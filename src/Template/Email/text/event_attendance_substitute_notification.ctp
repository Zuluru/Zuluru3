<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @type $team \App\Model\Entity\Team
 * @type $team_event \App\Model\Entity\TeamEvent
 * @type $person \App\Model\Entity\Person
 * @type $attendance \App\Model\Entity\Attendance
 * @type $captain string
 * @type $code string
 * @type $player_options string[]
 */

$address = "{$team_event->location_street}, {$team_event->location_city}, {$team_event->location_province}";
?>

<?= __('Dear {0},', $person->first_name) ?>


<?= __('{0} has indicated that you are {1} the {2} event "{3}" at {4} ({5}) starting at {6} on {7}.',
	$captain,
	Configure::read("event_attendance_verb.{$attendance->status}"),
	$team->name,
	$team_event->name,
	$team_event->location_name,
	$address,
	$this->Time->time($team_event->start),
	$this->Time->date($team_event->date)
) ?>


<?php
if (!empty($team_event->description)):
?>
<?= $team_event->description ?>


<?php
endif;

if (isset($note)):
?>
<?= $note ?>


<?php
endif;
?>
<?= strtoupper(__('If this correctly reflects your current status, you do not need to take any action at this time.')) ?> <?= __('To update your status, use one of the links below, or visit the web site at any time.') ?>


<?php
$url_array = [
	'controller' => 'TeamEvents', 'action' => 'attendance_change',
	'event' => $team_event->id, 'person' => $person->id, 'code' => $code];
foreach (Configure::read('event_attendance_verb') as $check_status => $check_verb):
	if ($attendance->status != $check_status && array_key_exists($check_status, $player_options)):
		$url_array['status'] = $check_status;
?>
<?= __('If you are {0} this event:', $check_verb) ?>

<?= Router::url($url_array, true) ?>


<?php
	endif;
endforeach;
?>
<?= $this->element('Email/text/footer');
