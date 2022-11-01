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
$link_address = strtr($address, ' ', '+');
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?= __('{0} has indicated that you are {1} the {2} event "{3}" at {4} ({5}) starting at {6} on {7}.',
	$captain,
	Configure::read("event_attendance_verb.{$attendance->status}"),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	$this->Html->link($team_event->name,
		Router::url(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $team_event->id], true)),
	$team_event->location_name,
	$this->Html->link($address, "https://maps.google.com/maps?q=$link_address"),
	$this->Time->time($team_event->start),
	$this->Time->date($team_event->date)
) ?></p>
<?php
if (!empty($team_event->description)):
?>
<p><?= $team_event->description ?></p>
<?php
endif;

if (isset($note)):
?>
<p><?= $note ?></p>
<?php
endif;
?>
<p><b><?= __('If this correctly reflects your current status, you do not need to take any action at this time.') ?></b> <?= __('To update your status, use one of the links below, or visit the web site at any time.') ?></p>
<?php
$url_array = [
	'controller' => 'TeamEvents', 'action' => 'attendance_change',
	'event' => $team_event->id, 'person' => $person->id, 'code' => $code];
foreach (Configure::read('event_attendance_verb') as $check_status => $check_verb):
	if ($attendance->status != $check_status && array_key_exists($check_status, $player_options)):
		$url_array['status'] = $check_status;
?>
<p><?= __('If you are {0} this event, {1}.', $check_verb, $this->Html->link(__('click here'), Router::url($url_array, true))) ?></p>
<?php
	endif;
endforeach;
?>
<?= $this->element('Email/html/footer');
