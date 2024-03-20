<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TeamEvent $team_event
 */

use Cake\Routing\Router;

$address = "{$team_event->location_street}, {$team_event->location_city}, {$team_event->location_province}";
$link_address = strtr($address, ' ', '+');
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?php
if (empty($attendance->comment)) {
	echo __('{0} has removed the comment from their attendance at the {1} event "{2}" at {3} ({4}) starting at {5} on {6}.',
		$person->full_name,
		$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', '?' => ['team' => $team->id]], true)),
		$this->Html->link($team_event->name,
			Router::url(['controller' => 'TeamEvents', 'action' => 'view', '?' => ['event' => $team_event->id]], true)),
		$team_event->location_name,
		$this->Html->link($address, "https://maps.google.com/maps?q=$link_address"),
		$this->Time->time($team_event->start),
		$this->Time->date($team_event->date)
	);
} else {
	echo __('{0} has added the following comment to their attendance at the {1} event "{2}" at {3} ({4}) starting at {5} on {6}.',
		$person->full_name,
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
if (!empty($attendance->comment)):
?>
<p><?= $attendance->comment ?></p>
<?php
endif;
?>
<?= $this->element('Email/html/footer');
