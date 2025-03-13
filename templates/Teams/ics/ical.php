<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Game[] $gameS
 * @var \App\Model\Entity\TeamEvent[] $events
 * @var int $team_id
 */

// Outlook only imports the first item from an ICS file. We'll consider games more important than events and put them first.
if (isset($games) && !empty($games)) {
	$uid_prefix = '';
	foreach ($games as $game) {
		echo $this->element('Games/ical', compact('team_id', 'game', 'uid_prefix'));
	}
}

if (isset($events) && !empty($events)) {
	$uid_prefix = 'E';
	foreach ($events as $event) {
		echo $this->element('TeamEvents/ical', compact('event', 'uid_prefix'));
	}
}
