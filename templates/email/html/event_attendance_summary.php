<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TeamEvent $team_event
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

$address = "{$team_event->location_street}, {$team_event->location_city}, {$team_event->location_province}";
$link_address = strtr($address, ' ', '+');
?>

<p><?= __('Dear {0},', $captains) ?></p>
<p><?= __('This is your attendance summary for the {0} event "{1}" at {2} ({3}) starting at {4} on {5}.',
	$this->Html->link($team->name, Router::url(['controller' => 'Teams', 'action' => 'view', 'team' => $team->id], true)),
	$this->Html->link($team_event->name,
		Router::url(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $team_event->id], true)),
	$team_event->location_name,
	$this->Html->link($address, "https://maps.google.com/maps?q=$link_address"),
	$this->Time->time($team_event->start),
	$this->Time->date($team_event->date)
) ?></p>
<?php
foreach ($summary as $status => $genders) {
	$text = '';
	foreach ($genders as $gender => $players) {
		if (!empty($players)) {
			$text .= '<br />' . count($players) . ' ' . __x('gender', $gender) . ': ' . implode(', ', $players);
		}
	}
	if (!empty($text)) {
		echo $this->Html->para(null, Configure::read("attendance.$status") . $text);
	}
}
?>
<p><?= __('You can {0}.',
		$this->Html->link(__('update this or check up-to-the-minute details'),
			Router::url(['controller' => 'TeamEvents', 'action' => 'view', 'event' => $team_event->id], true)
		)
	) . ' ' .
	__('You need to be logged into the website to update this.')
?></p>
<?= $this->element('Email/html/footer');
