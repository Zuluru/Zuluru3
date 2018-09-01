<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

$address = "{$team_event->location_street}, {$team_event->location_city}, {$team_event->location_province}";
?>

<?= __('Dear {0},', $captains) ?>


<?= __('{0} has indicated that they will be {1} the {2} event "{3}" at {4} ({5}) starting at {6} on {7}.',
	$person->full_name,
	Configure::read("event_attendance_verb.{$attendance->status}"),
	$team->name,
	$team_event->name,
	$team_event->location_name,
	$address,
	$this->Time->time($team_event->start),
	$this->Time->date($team_event->date)
) ?>


<?php
if (!empty($attendance->comment)):
?>
<?= $attendance->comment ?>


<?php
endif;

if ($attendance->status == ATTENDANCE_AVAILABLE):
?>
<?= __('If you want {0} to attend this event:', $person->first_name) ?>

<?= Router::url(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $team_event->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ATTENDING], true) ?>


<?= __('If you know <b>for sure</b> that you don\'t want {0} to attend this event:', $person->first_name) ?>

<?= Router::url(['controller' => 'TeamEvents', 'action' => 'attendance_change', 'event' => $team_event->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ABSENT], true) ?>


<?= __('Either of these actions will generate an automatic email to {0} indicating your selection. If you are unsure whether you will want {0} to attend this event, it\'s best to leave them listed as available, and take action later when you know for sure. You can always update their status on the web site, there is no need to keep this email for that purpose.',
	$person->first_name
) ?>


<?php
endif;
?>
<?= $this->element('Email/text/footer');
