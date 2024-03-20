<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Attendance $attendance
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

$address = "{$team_event->location_street}, {$team_event->location_city}, {$team_event->location_province}";
$link_address = strtr($address, ' ', '+');
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('{0} has indicated that they will be {1} the {2} event "{3}" at {4} ({5}) starting at {6} on {7}.',
	$person->full_name,
	Configure::read("event_attendance_verb.{$attendance->status}"),
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
	$this->Html->link($team_event->name,
		Router::url(['controller' => 'TeamEvents', 'action' => 'view', '?' => ['event' => $team_event->id]], true)),
	$team_event->location_name,
	$this->Html->link($address, "https://maps.google.com/maps?q=$link_address"),
	$this->Time->time($team_event->start),
	$this->Time->date($team_event->date)
) ?></p>
<?php
if (!empty($attendance->comment)):
?>
<p><?= $attendance->comment ?></p>
<?php
endif;

if ($attendance->status == ATTENDANCE_AVAILABLE):
?>
<p><?= __('If you would like {0} to attend this event:', $person->first_name) ?>

<?php
$url = Router::url(['controller' => 'TeamEvents', 'action' => 'attendance_change', '?' => ['event' => $team_event->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ATTENDING]], true);
echo $this->Html->link($url, $url);
?></p>
<p><?= __('If you know <b>for sure</b> that you don\'t want {0} to attend this event:', $person->first_name) ?>

<?php
$url = Router::url(['controller' => 'TeamEvents', 'action' => 'attendance_change', '?' => ['event' => $team_event->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ABSENT]], true);
echo $this->Html->link($url, $url);
?></p>
<p><?= __('Either of these actions will generate an automatic email to {0} indicating your selection. If you are unsure whether you will want {0} to attend this event, it\'s best to leave them listed as available, and take action later when you know for sure. You can always update their status on the web site, there is no need to keep this email for that purpose.',
	$person->first_name
) ?></p>
<?php
endif;
?>
<?= $this->element('Email/html/footer');
