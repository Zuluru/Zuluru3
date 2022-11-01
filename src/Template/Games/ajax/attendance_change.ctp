<?php
/**
 * @type $team \App\Model\Entity\Team
 * @type $person \App\Model\Entity\Person
 * @type $attendance \App\Model\Entity\Attendance
 * @type $game \App\Model\Entity\Game
 * @type $game_date \Cake\I18n\Date
 */

$args = [
	'team' => $team,
	'person_id' => $attendance->person_id,
	'role' => $attendance->person->teams[0]->_joinData->role,
	'attendance' => $attendance,
];
if (!$game->isNew()) {
	$args['game'] = $game;
} else {
	$args['game_date'] = $game_date;
}
echo $this->element('Games/attendance_change', $args);
