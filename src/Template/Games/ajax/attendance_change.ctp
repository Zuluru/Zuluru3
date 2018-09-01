<?php
$args = [
	'team' => $team,
	'person_id' => $attendance->person_id,
	'role' => $attendance->person->teams[0]->_joinData->role,
	'status' => $attendance->status,
	'comment' => $attendance->comment,
];
if (!$game->isNew()) {
	$args['game_id'] = $game->id;
	$args['game_time'] = $game->game_slot->start_time;
} else {
	$args['game_time'] = $date;
}
echo $this->element('Games/attendance_change', $args);
