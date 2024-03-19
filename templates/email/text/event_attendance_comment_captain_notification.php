<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\TeamEvent $team_event
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Attendance $attendance
 */

?>
<?= __('Dear {0},', $captains) ?>


<?php
if (empty($attendance->comment)) {
	echo __('{0} has removed the comment from their attendance at the {1} event "{2}" at {3} ({4}) starting at {5} on {6}.',
		$person->full_name,
		$team->name,
		$team_event->name,
		$team_event->location_name,
		$address,
		$this->Time->time($team_event->start),
		$this->Time->date($team_event->date)
	);
} else {
	echo __('{0} has added the following comment to their attendance at the {1} event "{2}" at {3} ({4}) starting at {5} on {6}.',
		$person->full_name,
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
if (!empty($attendance->comment)):
?>
<?= $attendance->comment ?>


<?php
endif;
?>
<?= $this->element('Email/text/footer');
