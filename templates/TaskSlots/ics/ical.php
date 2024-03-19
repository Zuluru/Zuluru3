<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\TaskSlot $task_slot
 */

echo $this->element('Tasks/ical', ['task_slot' => $task_slot, 'task' => $task_slot->task, 'uid_prefix' => 'T']);
