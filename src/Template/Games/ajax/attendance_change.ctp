<?php
/**
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 * @var \App\Model\Entity\Attendance $attendance
 * @var \App\Model\Entity\Game $game
 * @var \Cake\I18n\Date $game_date
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
