<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\TeamEvent $team_event
 * @var \App\Model\Entity\Person $person
 * @var int $status
 */

$address = "{$team_event->location_street}, {$team_event->location_city}, {$team_event->location_province}";
$link_address = strtr($address, ' ', '+');
?>

<p><?= __('Dear {0},', $person->first_name) ?></p>
<p><?php
if ($status == ATTENDANCE_UNKNOWN || $status == ATTENDANCE_INVITED) {
	echo __('You have not yet indicated your attendance for the {0} event "{1}" at {2} ({3}) starting at {4} on {5}.',
		$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
		$this->Html->link($team_event->name,
			Router::url(['controller' => 'TeamEvents', 'action' => 'view', '?' => ['event' => $team_event->id]], true)),
		$team_event->location_name,
		$this->Html->link($address, "https://maps.google.com/maps?q=$link_address"),
		$this->Time->time($team_event->start),
		$this->Time->date($team_event->date)
	);
} else {
	echo __('You are currently listed as {0} for the {1} event "{2}" at {3} ({4}) starting at {5} on {6}.',
		Configure::read("attendance.$status"),
		$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
		$this->Html->link($team_event->name,
			Router::url(['controller' => 'TeamEvents', 'action' => 'view', '?' => ['event' => $team_event->id]], true)),
		$team_event->location_name,
		$this->Html->link($address, "https://maps.google.com/maps?q=$link_address"),
		$this->Time->time($team_event->start),
		$this->Time->date($team_event->date)
	);
}
?></p>
<?php
if (!empty($team_event->description)):
?>
<p><?= $team_event->description ?></p>
<?php
endif;
?>
<p><?= __('If you are able to attend, {0}.',
	$this->Html->link(__('click here'),
		Router::url(['controller' => 'TeamEvents', 'action' => 'attendance_change', '?' => ['event' => $team_event->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ATTENDING]], true))
) ?></p>
<p><?= __('If you are unavailable to attend, {0}.',
	$this->Html->link(__('click here'),
		Router::url(['controller' => 'TeamEvents', 'action' => 'attendance_change', '?' => ['event' => $team_event->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ABSENT]], true))
) ?></p>
<?= $this->element('Eeail/html/footer');
