<?php
if (isset($games)) {
	foreach ($games as $game) {
		echo $this->element('Games/ical', ['game_id' => $game->id, 'team_id' => $team_id, 'game' => $game, 'uid_prefix' => 'P']);
	}
}

if (isset($tasks)) {
	foreach ($tasks as $task) {
		echo $this->element('Tasks/ical', ['task_slot' => $task->task_slot, 'task' => $task, 'uid_prefix' => 'T']);
	}
}

if (isset($events) && !empty($events)) {
	foreach ($events as $event) {
		echo $this->element('TeamEvents/ical', ['event_id' => $event->id, 'event' => $event, 'uid_prefix' => 'E']);
	}
}
