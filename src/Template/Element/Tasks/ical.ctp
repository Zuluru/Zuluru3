<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Task $task
 * @var \App\Model\Entity\TaskSlot $task_slot
 */

use Cake\Core\Configure;

// Get domain URL for signing tasks
$domain = Configure::read('App.domain');

// output task
?>
BEGIN:VEVENT
UID:<?= "{$uid_prefix}{$task_slot->id}@{$domain}" ?>

DTSTAMP:<?= $this->Time->iCal(\Cake\I18n\FrozenTime::now()) ?>

CREATED:<?= $this->Time->iCal($task_slot->modified) ?>

LAST-MODIFIED:<?= $this->Time->iCal($task_slot->modified) ?>

DTSTART:<?= $this->Time->iCal($task_slot->start_time) ?>

DTEND:<?= $this->Time->iCal($task_slot->end_time) ?>

SUMMARY:<?= \App\Lib\ical_encode($task->translateField('name')) ?>

DESCRIPTION:<?= __('{0}, reporting to {1}, on {2}',
	\App\Lib\ical_encode($task->translateField('name')),
	\App\Lib\ical_encode($task->person->full_name),
	$this->Time->iCalDateTimeRange($task_slot)
);
?>

STATUS:CONFIRMED
TRANSP:OPAQUE
END:VEVENT
