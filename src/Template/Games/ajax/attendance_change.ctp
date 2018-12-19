<?php
/**
 * @type \App\Model\Entity\Team $team
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Attendance $attendance
 * @type \App\Model\Entity\Game $game
 * @type \Cake\I18n\Date $game_date
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
