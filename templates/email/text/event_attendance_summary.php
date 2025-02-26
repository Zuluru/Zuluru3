<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TeamEvent $team_event
 * @var \App\Model\Entity\Team $team
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

$address = "{$team_event->location_street}, {$team_event->location_city}, {$team_event->location_province}";
?>

<?= __('Dear {0},', $captains) ?>


<?= __('This is your attendance summary for the {0} event "{1}" at {2} ({3}) starting at {4} on {5}.',
	$team->name,
	$team_event->name,
	$team_event->location_name,
	$address,
	$this->Time->time($team_event->start),
	$this->Time->date($team_event->date)
) ?>


<?php
foreach ($summary as $status => $genders) {
	$text = '';
	foreach ($genders as $gender => $players) {
		if (!empty($players)) {
			$text .= "\n" . count($players) . ' ' . __x('gender', $gender) . ': ' . implode(', ', $players);
		}
	}
	if (!empty($text)) {
		echo Configure::read("attendance.$status") . $text . "\n\n";
	}
}
?>
<?= __('You can update this or check up-to-the-minute details here:') ?>

<?= Router::url(['controller' => 'TeamEvents', 'action' => 'view', '?' => ['event' => $team_event->id]], true) ?>


<?= __('You need to be logged into the website to update this.') ?>


<?= $this->element('email/text/footer');
