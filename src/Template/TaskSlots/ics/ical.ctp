<?php
echo $this->element('Tasks/ical', ['task_slot' => $task_slot, 'task' => $task_slot->task, 'uid_prefix' => 'T']);
