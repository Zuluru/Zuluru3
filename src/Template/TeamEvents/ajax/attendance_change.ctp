<?php
$args = [
	'team' => $team,
	'person_id' => $person->id,
	'role' => $person->teams[0]->_joinData->role,
	'status' => $status,
	'comment' => $comment,
	'event_id' => $event->id,
	'event_time' => $event->start_time,
];
echo $this->element('TeamEvents/attendance_change', $args);
