<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task $task
 * @var \App\Model\Entity\TaskSlot $task_slot
 * @var string $uid_prefix
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use function App\Lib\ical_encode;

// Get domain URL for signing tasks
$domain = Configure::read('App.domain');

// output task
?>
BEGIN:VEVENT
UID:<?= "{$uid_prefix}{$task_slot->id}@$domain" ?>

DTSTAMP:<?= $this->Time->iCal(FrozenTime::now()) ?>

CREATED:<?= $this->Time->iCal($task_slot->modified) ?>

LAST-MODIFIED:<?= $this->Time->iCal($task_slot->modified) ?>

DTSTART:<?= $this->Time->iCal($task_slot->start_time) ?>

DTEND:<?= $this->Time->iCal($task_slot->end_time) ?>

SUMMARY:<?= ical_encode($task->translateField('name')) ?>

DESCRIPTION:<?= __('{0}, reporting to {1}, on {2}',
	ical_encode($task->translateField('name')),
	ical_encode($task->person->full_name),
	$this->Time->iCalDateTimeRange($task_slot)
);
?>

STATUS:CONFIRMED
TRANSP:OPAQUE
END:VEVENT
