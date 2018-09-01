<?php
// Outlook only imports the first item from an ICS file. We'll consider games more important than events and put them first.
if (isset($games) && !empty($games)) {
	$uid_prefix = '';
	foreach ($games as $game) {
		$game_id = $game->id;
		echo $this->element('Games/ical', compact('game_id', 'team_id', 'game', 'uid_prefix'));
	}
}

if (isset($events) && !empty($events)) {
	$uid_prefix = 'E';
	foreach ($events as $event) {
		$event_id = $event->id;
		echo $this->element('TeamEvents/ical', compact('event_id', 'event', 'uid_prefix'));
	}
}
