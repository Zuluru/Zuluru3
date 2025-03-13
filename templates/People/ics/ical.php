<?php
/**
 * @var \App\View\AppView $this
 * @var int[] $team_id
 * @var \App\Model\Entity\Game[] $games
 * @var \App\Model\Entity\Event[] $events
 * @var \App\Model\Entity\Task[] $tasks
 * @var \App\Model\Entity\Game[] $officiated_games
 * @var string $calendar_type
 * @var string $calendar_name
 */

if (!empty($games)) {
	foreach ($games as $game) {
		echo $this->element('Games/ical', ['team_id' => $team_id, 'game' => $game, 'uid_prefix' => 'P']);
	}
}

if (!empty($tasks)) {
	foreach ($tasks as $task) {
		echo $this->element('Tasks/ical', ['task_slot' => $task, 'task' => $task->task, 'uid_prefix' => 'T']);
	}
}

if (!empty($events)) {
	foreach ($events as $event) {
		echo $this->element('TeamEvents/ical', ['event' => $event, 'uid_prefix' => 'E']);
	}
}

if (!empty($officiated_games)) {
	foreach ($officiated_games as $game) {
		echo $this->element('Games/official_ical', ['game' => $game, 'uid_prefix' => 'O']);
	}
}
