<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\TeamEvent $team_event
 * @var \App\Model\Entity\Person $person
 * @var int $status
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

$address = "{$team_event->location_street}, {$team_event->location_city}, {$team_event->location_province}";
?>

<?= __('Dear {0},', $person->first_name) ?>


<?php
if ($status == ATTENDANCE_UNKNOWN || $status == ATTENDANCE_INVITED) {
	echo __('You have not yet indicated your attendance for the {0} event "{1}" at {2} ({3}) starting at {4} on {5}.',
		$team->name,
		$team_event->name,
		$team_event->location_name,
		$address,
		$this->Time->time($team_event->start),
		$this->Time->date($team_event->date)
	);
} else {
	echo __('You are currently listed as {0} for the {1} event "{2}" at {3} ({4}) starting at {5} on {6}.',
		Configure::read("attendance.$status"),
		$team->name,
		$team_event->name,
		$team_event->location_name,
		$address,
		$this->Time->time($team_event->start),
		$this->Time->date($team_event->date)
	);
}
?>


<?php
if (!empty($team_event->description)):
?>
<?= $team_event->description ?>


<?php
endif;
?>
<?= __('If you are able to attend:') ?>

<?= Router::url(['controller' => 'TeamEvents', 'action' => 'attendance_change', '?' => ['event' => $team_event->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ATTENDING]], true) ?>


<?= __('If you are unavailable to attend:') ?>

<?= Router::url(['controller' => 'TeamEvents', 'action' => 'attendance_change', '?' => ['event' => $team_event->id, 'person' => $person->id, 'code' => $code, 'status' => ATTENDANCE_ABSENT]], true) ?>


<?= $this->element('email/text/footer');
